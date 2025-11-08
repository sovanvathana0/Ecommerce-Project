<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        // FORCE LOAD cart_info + product
        $this->order = $order->load('cart_info.product');

        // DEBUG: Log what's loaded
        \Log::info('OrderConfirmationMail: cart_info count = ' . $this->order->cart_info->count());
        foreach ($this->order->cart_info as $item) {
            \Log::info('Cart item: ' . ($item->product?->title ?? 'NO PRODUCT'));
        }
    }

    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Order Confirmed – #' . $this->order->order_number)
            ->view('emails.order_confirmation');
    }
}