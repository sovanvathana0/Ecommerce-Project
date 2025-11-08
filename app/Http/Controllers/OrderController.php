<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use App\User;
use PDF;
use Notification;
use Helper;
use Illuminate\Support\Str;
use App\Notifications\StatusNotification;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;
use Exception;
use Carbon\Carbon;                             

class OrderController extends Controller
{
    /* -------------------------------------------------
       INDEX – keep as-is
       ------------------------------------------------- */
    public function index()
    {
        $orders = Order::orderBy('id', 'DESC')->paginate(10);
        return view('backend.order.index')->with('orders', $orders);
    }

    /* -------------------------------------------------
       STORE – THE ONLY METHOD THAT NEEDED FIXING
       ------------------------------------------------- */
    public function store(Request $request)
    {
        // ---- 1. VALIDATE ----
        $this->validate($request, [
            'first_name' => 'string|required',
            'last_name'  => 'string|required',
            'address1'   => 'string|required',
            'address2'   => 'string|nullable',
            'coupon'     => 'nullable|numeric',
            'phone'      => 'numeric|required',
            'post_code'  => 'string|nullable',
            'email'      => 'string|required|email'
        ]);

        // ---- 2. CART CHECK ----
        if (empty(Cart::where('user_id', auth()->user()->id)->whereNull('order_id')->first())) {
            request()->session()->flash('error', 'Cart is Empty!');
            return back();
        }

        // ---- 3. BUILD ORDER ----
        $order      = new Order();
        $order_data = $request->all();

        $order_data['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $order_data['user_id']      = $request->user()->id;
        $order_data['shipping_id']  = $request->shipping;

        $shipping   = Shipping::where('id', $order_data['shipping_id'])->pluck('price');
        $shippingCost = $shipping[0] ?? 0;

        $order_data['sub_total'] = \App\Helpers\Helper::totalCartPrice();
        $order_data['quantity'] = \App\Helpers\Helper::cartCount();

        if (session('coupon')) {
            $order_data['coupon'] = session('coupon')['value'];
        }

        $total = \App\Helpers\Helper::totalCartPrice() + $shippingCost;
        if (session('coupon')) {
            $total -= session('coupon')['value'];
        }
        $order_data['total_amount'] = $total;

        // ---- payment method ----
        if ($request->payment_method === 'paypal') {
            $order_data['payment_method'] = 'paypal';
            $order_data['payment_status'] = 'paid';
        } elseif ($request->payment_method === 'cardpay') {
            $order_data['payment_method'] = 'cardpay';
            $order_data['payment_status'] = 'paid';
        } else {
            $order_data['payment_method'] = 'cod';
            $order_data['payment_status'] = 'Unpaid';
        }

        $order->fill($order_data);
        $status = $order->save();

        if ($status) {
            // ---- UPDATE CART (must be BEFORE e-mail) ----
            Cart::where('user_id', auth()->user()->id)
                ->whereNull('order_id')
                ->update(['order_id' => $order->id]);

            // ---- RE-LOAD ORDER WITH PRODUCTS ----
            $order = $order->fresh('cart_info.product');

            // ---- 4. SEND CONFIRMATION E-MAIL ----
            if (auth()->check()) {
                try {
                    Mail::to($order->email)->send(new OrderConfirmationMail($order));
                    \Log::info('Order confirmation sent to: ' . $order->email);
                } catch (Exception $e) {
                    \Log::error('Order mail failed: ' . $e->getMessage());
                }
            } else {
                \Log::info('Guest checkout – no e-mail sent.');
            }

            // ---- 5. TELEGRAM NOTIFICATION ----
            try {
                $telegram = new TelegramService();

                $telegramData = [
                    'order_number'   => $order->order_number,
                    'first_name'     => $request->first_name,
                    'last_name'      => $request->last_name,
                    'email'          => $request->email,
                    'phone'          => $request->phone,
                    'address1'       => $request->address1,
                    'address2'       => $request->address2 ?? '',
                    'country'        => $request->country ?? 'N/A',
                    'post_code'      => $request->post_code ?? '',
                    'subtotal'       => number_format($order_data['sub_total'], 2),
                    'shipping_cost'  => number_format($shippingCost, 2),
                    'discount'       => number_format($order_data['coupon'] ?? 0, 2),
                    'total'          => number_format($order_data['total_amount'], 2),
                    'payment_method' => $order_data['payment_method']
                ];

                $telegram->sendOrderNotification($telegramData);
            } catch (Exception $e) {
                \Log::error('Telegram failed: ' . $e->getMessage());
            }

            // ---- 6. ADMIN NOTIFICATION ----
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $details = [
                    'title'     => 'New Order Received',
                    'actionURL' => route('order.show', $order->id),
                    'fas'       => 'fa-file-alt'
                ];
                Notification::send($admin, new StatusNotification($details));
            }

            // ---- 7. PAYPAL REDIRECT ----
            if ($request->payment_method === 'paypal') {
                return redirect()->route('payment')->with(['id' => $order->id]);
            }

            // ---- 8. CLEAR SESSION ----
            session()->forget(['cart', 'coupon']);

            request()->session()->flash('success', 'Your product order has been placed. Thank you for shopping with us.');
            return redirect()->route('home');
        }

        request()->session()->flash('error', 'Something went wrong. Please try again.');
        return back();
    }

    /* -------------------------------------------------
       SHOW / EDIT / UPDATE / DESTROY – unchanged
       ------------------------------------------------- */
    public function show($id)
    {
        $order = Order::findOrFail($id);
        return view('backend.order.show')->with('order', $order);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        return view('backend.order.edit')->with('order', $order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $this->validate($request, ['status' => 'required|in:new,process,delivered,cancel']);
        $data = $request->all();

        if ($request->status === 'delivered') {
            foreach ($order->cart as $cart) {
                $product = $cart->product;
                $product->stock -= $cart->quantity;
                $product->save();
            }
        }

        $status = $order->fill($data)->save();

        if ($status) {
            try {
                $telegram = new TelegramService();
                $telegram->sendOrderStatusUpdate($order, $request->status);
            } catch (Exception $e) {
                \Log::error('Telegram status update failed', ['error' => $e->getMessage()]);
            }
            request()->session()->flash('success', 'Successfully updated order');
        } else {
            request()->session()->flash('error', 'Error while updating order');
        }

        return redirect()->route('order.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        request()->session()->flash('success', 'Order Successfully deleted');
        return redirect()->route('order.index');
    }

    public function orderTrack()
    {
        return view('frontend.pages.order-track');
    }

    public function productTrackOrder(Request $request)
    {
        $order = Order::where('user_id', auth()->user()->id)
                      ->where('order_number', $request->order_number)
                      ->first();

        if ($order) {
            $msg = match ($order->status) {
                'new'       => 'Your order has been placed.',
                'process'   => 'Your order is currently processing.',
                'delivered' => 'Your order has been delivered. Thank you for shopping with us.',
                default     => 'Sorry, your order has been canceled.',
            };
            request()->session()->flash('success', $msg);
        } else {
            request()->session()->flash('error', 'Invalid order number. Please try again!');
        }
        return redirect()->route('home');
    }

    // PDF generate
    public function pdf(Request $request)
    {
        $order     = Order::getAllOrder($request->id);
        $file_name = $order->order_number . '-' . $order->first_name . '.pdf';
        $pdf       = PDF::loadView('backend.order.pdf', compact('order'));
        return $pdf->download($file_name);
    }

    // Income chart
    public function incomeChart(Request $request)
    {
        $year  = Carbon::now()->year;
        $items = Order::with(['cart_info'])
            ->whereYear('created_at', $year)
            ->where('status', 'delivered')
            ->get()
            ->groupBy(fn($d) => Carbon::parse($d->created_at)->format('m'));

        $result = [];
        foreach ($items as $month => $collections) {
            foreach ($collections as $item) {
                $amount = $item->cart_info->sum('amount');
                $m      = (int) $month;
                $result[$m] = ($result[$m] ?? 0) + $amount;
            }
        }

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $data[$monthName] = number_format($result[$i] ?? 0, 2, '.', '');
        }

        return $data;
    }
}