<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_code',
        'total_amount',
        'discount_id',
        'discount_amount',
        'status',
        'payment_method',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    /**
     * Relationship: Order belongs to a User.
     * If the user is deleted, fallback to a default "N/A" name.
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'N/A',
        ]);
    }

    /**
     * Relationship: Order has many Order Items.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship: Order may have one Discount.
     */
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}
