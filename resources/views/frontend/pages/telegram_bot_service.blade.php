<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $chatId;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    /**
     * Send order notification to Telegram
     */
    public function sendOrderNotification($orderData)
    {
        try {
            $message = $this->formatOrderMessage($orderData);
            
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            if ($response->successful()) {
                Log::info('Telegram notification sent successfully');
                return true;
            } else {
                Log::error('Telegram notification failed: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format order data into a readable message
     */
    private function formatOrderMessage($orderData)
    {
        $message = "🛒 <b>NEW ORDER RECEIVED!</b>\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 <b>Order Details:</b>\n";
        $message .= "Order #: <code>{$orderData['order_number']}</code>\n\n";
        
        $message .= "👤 <b>Customer Information:</b>\n";
        $message .= "Name: {$orderData['first_name']} {$orderData['last_name']}\n";
        $message .= "Email: {$orderData['email']}\n";
        $message .= "Phone: {$orderData['phone']}\n\n";
        
        $message .= "📍 <b>Shipping Address:</b>\n";
        $message .= "{$orderData['address1']}\n";
        if (!empty($orderData['address2'])) {
            $message .= "{$orderData['address2']}\n";
        }
        $message .= "{$orderData['country']}";
        if (!empty($orderData['post_code'])) {
            $message .= " - {$orderData['post_code']}";
        }
        $message .= "\n\n";
        
        $message .= "💰 <b>Order Summary:</b>\n";
        $message .= "Subtotal: ${$orderData['subtotal']}\n";
        $message .= "Shipping: ${$orderData['shipping_cost']}\n";
        if (isset($orderData['discount']) && $orderData['discount'] > 0) {
            $message .= "Discount: -${$orderData['discount']}\n";
        }
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "<b>Total: ${$orderData['total']}</b>\n\n";
        
        $message .= "💳 <b>Payment Method:</b>\n";
        $message .= $this->getPaymentMethodLabel($orderData['payment_method']) . "\n\n";
        
        $message .= "⏰ <b>Order Time:</b>\n";
        $message .= date('Y-m-d H:i:s') . "\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";

        return $message;
    }

    /**
     * Get payment method label
     */
    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'cod' => '💵 Cash On Delivery',
            'cardpay' => '💳 Card Payment',
            'paypal' => '🅿️ PayPal'
        ];

        return $labels[$method] ?? $method;
    }
}