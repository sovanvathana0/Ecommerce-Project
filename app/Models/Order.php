<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'sub_total', 'quantity', 'delivery_charge',
        'status', 'total_amount', 'first_name', 'last_name', 'country',
        'post_code', 'address1', 'address2', 'phone', 'email',
        'payment_method', 'payment_status', 'shipping_id', 'coupon'
    ];

    // === MAIN CART RELATIONSHIP (USED IN EMAIL) ===
    public function cart()
    {
        return $this->hasMany(Cart::class, 'order_id', 'id')->with('product');
    }

    // === LEGACY: Keep for backward compatibility (admin panel, etc.) ===
    public function cart_info()
    {
        return $this->hasMany(Cart::class, 'order_id', 'id')->with('product');
    }

    // === STATIC: Get full order with cart + product ===
    public static function getAllOrder($id)
    {
        return Order::with('cart.product')->find($id);
    }

    // === COUNT ORDERS ===
    public static function countActiveOrder()
    {
        return Order::count() ?: 0;
    }

    public static function countNewReceivedOrder()
    {
        return Order::where('status', 'new')->count();
    }

    public static function countProcessingOrder()
    {
        return Order::where('status', 'process')->count();
    }

    public static function countDeliveredOrder()
    {
        return Order::where('status', 'delivered')->count();
    }

    public static function countCancelledOrder()
    {
        return Order::where('status', 'cancel')->count();
    }

    // === RELATIONSHIPS ===
    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}