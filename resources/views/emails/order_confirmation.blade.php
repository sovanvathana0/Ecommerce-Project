<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Invoice</title>
    <style>
        body {font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;}
        .container {max-width:700px;margin:20px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 0 10px rgba(0,0,0,.1);}
        .header {background:#2c3e50;color:#fff;padding:20px;text-align:center;}
        .content {padding:30px;}
        .table {width:100%;border-collapse:collapse;margin:20px 0;}
        .table th,.table td {border:1px solid #ddd;padding:12px;text-align:left;}
        .table th {background:#f8f9fa;}
        .total {font-weight:bold;font-size:18px;}
        .footer {background:#f8f9fa;padding:20px;text-align:center;font-size:14px;color:#777;}
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Order Confirmed!</h1>
        <p>Thank you, {{ $order->first_name }}!</p>
    </div>

    <div class="content">
        <h2>Order #{{ $order->order_number }}</h2>
        <p><strong>Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
        <p><strong>Email:</strong> {{ $order->email }}</p>

        <h3>Order Items</h3>

        @if($order->cart_info && $order->cart_info->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->cart_info as $item)
                        <tr>
                            <td>
                                @if($item->product)
                                    {{ $item->product->title }}
                                @else
                                    [Product Not Found]
                                @endif
                            </td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color:red;">No cart items found in order.</p>
        @endif

        <table class="table" style="width:50%;float:right;">
            <tr><td>Subtotal</td><td>${{ number_format($order->sub_total, 2) }}</td></tr>
            @if($order->coupon > 0)
            <tr><td>Discount</td><td>-${{ number_format($order->coupon, 2) }}</td></tr>
            @endif
            <tr><td>Shipping</td><td>${{ number_format($order->shipping?->price ?? 0, 2) }}</td></tr>
            <tr class="total"><td><strong>Total</strong></td><td><strong>${{ number_format($order->total_amount, 2) }}</strong></td></tr>
        </table>
        <div style="clear:both;"></div>

        <p><strong>Payment:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
    </div>

    <div class="footer">
        <p>Track your order in your account.</p>
        <p>&copy; {{ date('Y') }} E-SHOP. All rights reserved.</p>
    </div>
</div>
</body>
</html>