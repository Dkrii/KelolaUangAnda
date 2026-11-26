<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

enum ReportPeriod: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Harian',
            self::Weekly => 'Mingguan',
            self::Monthly => 'Bulanan',
            self::Yearly => 'Tahunan',
        };
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function range(Carbon $date): array
    {
        return match ($this) {
            self::Daily => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            self::Weekly => [$date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(), $date->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay()],
            self::Monthly => [$date->copy()->startOfMonth()->startOfDay(), $date->copy()->endOfMonth()->endOfDay()],
            self::Yearly => [$date->copy()->startOfYear()->startOfDay(), $date->copy()->endOfYear()->endOfDay()],
        };
    }
}
