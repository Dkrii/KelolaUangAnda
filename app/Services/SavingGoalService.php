<?php

namespace App\Services;

use App\Models\SavingGoal;
use App\Models\SavingGoalContribution;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SavingGoalService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): SavingGoal
    {
        return $user->savingGoals()->create([
            'name' => $data['name'],
            'target_amount' => $data['target_amount'],
            'deadline' => $data['deadline'] ?? null,
            'description' => $data['description'] ?? null,
            'saved_amount' => '0.00',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(SavingGoal $goal, array $data): SavingGoal
    {
        return DB::transaction(function () use ($goal, $data) {
            $goal = SavingGoal::query()->lockForUpdate()->findOrFail($goal->id);

            $goal->fill([
                'name' => $data['name'],
                'target_amount' => $data['target_amount'],
                'deadline' => $data['deadline'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            $this->syncCompletion($goal);
            $goal->save();

            return $goal->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addContribution(SavingGoal $goal, array $data): SavingGoalContribution
    {
        return DB::transaction(function () use ($goal, $data) {
            $goal = SavingGoal::query()->lockForUpdate()->findOrFail($goal->id);

            $contribution = $goal->contributions()->create([
                'amount' => $data['amount'],
                'contribution_date' => $data['contribution_date'],
                'note' => $data['note'] ?? null,
            ]);

            $goal->saved_amount = Money::add($goal->saved_amount, $contribution->amount);
            $this->syncCompletion($goal);
            $goal->save();

            return $contribution;
        });
    }

    public function delete(SavingGoal $goal): void
    {
        DB::transaction(fn () => $goal->delete());
    }

    /**
     * @return array{
     *     goals: Collection<int, SavingGoal>,
     *     totalTarget: string,
     *     totalSaved: string,
     *     remaining: string
     * }
     */
    public function summaryFor(User $user): array
    {
        $goals = $user->savingGoals()
            ->withCount('contributions')
            ->orderByDesc('created_at')
            ->get();

        $totalTarget = '0.00';
        $totalSaved = '0.00';

        foreach ($goals as $goal) {
            $totalTarget = Money::add($totalTarget, $goal->target_amount);
            $totalSaved = Money::add($totalSaved, $goal->saved_amount);
        }

        return [
            'goals' => $goals,
            'totalTarget' => $totalTarget,
            'totalSaved' => $totalSaved,
            'remaining' => Money::sub($totalTarget, $totalSaved),
        ];
    }

    private function syncCompletion(SavingGoal $goal): void
    {
        if (bccomp((string) $goal->saved_amount, (string) $goal->target_amount, 2) >= 0) {
            $goal->completed_at ??= now();

            return;
        }

        $goal->completed_at = null;
    }
}
