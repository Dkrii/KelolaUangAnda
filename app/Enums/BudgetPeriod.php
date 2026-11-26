<?php

namespace App\Enums;

enum BudgetPeriod: string
{
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Bulanan',
        };
    }
}
