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
        $this->botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
        $this->chatId = config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID'));
        
        // Log credentials for debugging
        Log::info('TelegramService initialized', [
            'bot_token_set' => !empty($this->botToken),
            'chat_id_set' => !empty($this->chatId),
            'bot_token_length' => strlen($this->botToken ?? ''),
            'chat_id' => $this->chatId
        ]);
    }

    /**
     * Send order notification to Telegram
     */
    public function sendOrderNotification($orderData)
    {
        try {
            if (empty($this->botToken) || empty($this->chatId)) {
                Log::error('Telegram credentials missing', [
                    'bot_token_empty' => empty($this->botToken),
                    'chat_id_empty' => empty($this->chatId)
                ]);
                return false;
            }
            
            $message = $this->formatOrderMessage($orderData);
            
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
            
            Log::info('Sending Telegram notification', [
                'url_start' => substr($url, 0, 50),
                'chat_id' => $this->chatId
            ]);
            
            $response = Http::post($url, [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            if ($response->successful()) {
                Log::info('Telegram notification sent successfully');
                return true;
            } else {
                Log::error('Telegram notification failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
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
        $message .= "Subtotal: \${$orderData['subtotal']}\n";
        $message .= "Shipping: \${$orderData['shipping_cost']}\n";
        if (isset($orderData['discount']) && $orderData['discount'] > 0) {
            $message .= "Discount: -\${$orderData['discount']}\n";
        }
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "<b>Total: \${$orderData['total']}</b>\n\n";
        
        $message .= "💳 <b>Payment Method:</b>\n";
        $message .= $this->getPaymentMethodLabel($orderData['payment_method']) . "\n\n";
        
        $message .= "⏰ <b>Order Time:</b>\n";
        $message .= date('Y-m-d H:i:s') . "\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";

        return $message;
    }

    /**
     * Send order status update notification to Telegram
     */
    public function sendOrderStatusUpdate($order, $newStatus)
    {
        try {
            $message = $this->formatStatusUpdateMessage($order, $newStatus);
            
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            if ($response->successful()) {
                Log::info('Telegram status update sent successfully');
                return true;
            } else {
                Log::error('Telegram status update failed: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram status update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format status update message
     */
    private function formatStatusUpdateMessage($order, $newStatus)
    {
        $statusEmoji = [
            'new' => '🆕',
            'process' => '⚙️',
            'delivered' => '✅',
            'cancel' => '❌'
        ];

        $statusLabel = [
            'new' => 'New Order',
            'process' => 'Processing',
            'delivered' => 'Delivered',
            'cancel' => 'Cancelled'
        ];

        $emoji = $statusEmoji[$newStatus] ?? '📦';
        $label = $statusLabel[$newStatus] ?? ucfirst($newStatus);

        $message = "{$emoji} <b>ORDER STATUS UPDATED</b>\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 Order #: <code>{$order->order_number}</code>\n\n";
        
        $message .= "👤 <b>Customer:</b>\n";
        $message .= "{$order->first_name} {$order->last_name}\n";
        $message .= "📧 {$order->email}\n";
        $message .= "📞 {$order->phone}\n\n";
        
        $message .= "📍 <b>Shipping Address:</b>\n";
        $message .= "{$order->address1}\n";
        if (!empty($order->address2)) {
            $message .= "{$order->address2}\n";
        }
        $message .= "{$order->country}";
        if (!empty($order->post_code)) {
            $message .= " - {$order->post_code}";
        }
        $message .= "\n\n";
        
        $message .= "💰 <b>Order Total:</b> \${$order->total_amount}\n\n";
        
        $message .= "📊 <b>New Status:</b> {$emoji} <b>{$label}</b>\n\n";
        
        $message .= "⏰ <b>Updated:</b>\n";
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