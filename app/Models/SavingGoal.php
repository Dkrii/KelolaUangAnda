<?php

namespace App\Models;

use App\Enums\SavingGoalStatus;
use App\Support\Money;
use Database\Factories\SavingGoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'target_amount', 'saved_amount', 'deadline', 'description', 'completed_at'])]
class SavingGoal extends Model
{
    /** @use HasFactory<SavingGoalFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'saved_amount' => 'decimal:2',
            'deadline' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SavingGoalContribution::class);
    }

    public function status(): SavingGoalStatus
    {
        if ($this->completed_at !== null || bccomp((string) $this->saved_amount, (string) $this->target_amount, 2) >= 0) {
            return SavingGoalStatus::Completed;
        }

        if ($this->deadline !== null && $this->deadline->isBefore(today())) {
            return SavingGoalStatus::Overdue;
        }

        return SavingGoalStatus::Active;
    }

    public function progressPercentage(): float
    {
        if (bccomp((string) $this->target_amount, '0', 2) <= 0) {
            return 0.0;
        }

        $progress = (float) bcmul(bcdiv((string) $this->saved_amount, (string) $this->target_amount, 4), '100', 2);

        return min(100.0, $progress);
    }

    public function remainingAmount(): string
    {
        return Money::sub($this->target_amount, $this->saved_amount);
    }
}
