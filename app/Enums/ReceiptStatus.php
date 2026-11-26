<?php

namespace App\Enums;

enum ReceiptStatus: string
{
    case Pending = 'pending';
    case Review = 'review';
    case Confirmed = 'confirmed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Diproses',
            self::Review => 'Perlu Dikonfirmasi',
            self::Confirmed => 'Terkonfirmasi',
            self::Failed => 'Gagal',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Pending => 'neutral',
            self::Review => 'amber',
            self::Confirmed => 'emerald',
            self::Failed => 'rose',
        };
    }
}
