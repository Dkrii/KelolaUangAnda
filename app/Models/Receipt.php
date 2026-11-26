<?php

namespace App\Models;

use App\Enums\ReceiptStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'transaction_id',
    'path',
    'original_name',
    'mime_type',
    'size_in_bytes',
    'status',
    'merchant',
    'transaction_date',
    'total',
    'ocr_data',
])]
class Receipt extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ReceiptStatus::class,
            'transaction_date' => 'date:Y-m-d',
            'total' => 'decimal:2',
            'ocr_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }
}
