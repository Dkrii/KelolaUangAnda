<?php

namespace App\Enums;

enum SavingGoalStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Berjalan',
            self::Completed => 'Tercapai',
            self::Overdue => 'Terlewat',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Active => 'emerald',
            self::Completed => 'indigo',
            self::Overdue => 'rose',
        };
    }
}
