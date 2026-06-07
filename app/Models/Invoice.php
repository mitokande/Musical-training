<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'user_id', 'subscription_id', 'invoice_number', 'amount',
        'tax_amount', 'total_amount', 'currency', 'status',
        'paid_at', 'payment_method', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public static function generateNumber(): string
    {
        $prefix = 'INV-' . date('Ym');
        $lastInvoice = static::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        $sequence = $lastInvoice
            ? ((int) substr($lastInvoice->invoice_number, -4)) + 1
            : 1;

        return $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
