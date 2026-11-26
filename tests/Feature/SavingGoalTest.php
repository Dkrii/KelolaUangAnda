<?php

namespace Tests\Feature;

use App\Enums\SavingGoalStatus;
use App\Models\SavingGoal;
use App\Models\SavingGoalContribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingGoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('saving-goals.index'))->assertRedirect('/login');
        $this->get(route('saving-goals.create'))->assertRedirect('/login');
    }

    public function test_index_shows_only_own_goals(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        SavingGoal::factory()->create([
            'user_id' => $user->id,
            'name' => 'Liburan Bali',
            'target_amount' => '5000000.00',
            'saved_amount' => '1000000.00',
        ]);
        SavingGoal::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Mobil Baru',
            'target_amount' => '9999999.00',
        ]);

        $response = $this->actingAs($user)->get(route('saving-goals.index'));

        $response->assertOk();
        $response->assertSee('Liburan Bali');
        $response->assertSee('Rp 5.000.000');
        $response->assertSee('Rp 1.000.000');
        $response->assertDontSee('Mobil Baru');
        $response->assertDontSee('Rp 9.999.999');
    }

    public function test_user_can_create_a_saving_goal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('saving-goals.store'), [
            'name' => 'Dana Darurat',
            'target_amount' => '10000000.00',
            'deadline' => now()->addMonths(6)->toDateString(),
            'description' => 'Simpanan enam bulan.',
        ]);

        $goal = SavingGoal::query()->firstOrFail();

        $response->assertRedirect(route('saving-goals.show', $goal));
        $response->assertSessionHas('status', 'Target tabungan berhasil dibuat.');
        $this->assertSame('Dana Darurat', $goal->name);
        $this->assertSame('10000000.00', (string) $goal->target_amount);
        $this->assertSame('0.00', (string) $goal->saved_amount);
    }

    public function test_goal_validation_rejects_zero_target_and_past_deadline(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('saving-goals.store'), [
            'name' => 'Tidak Valid',
            'target_amount' => '0',
            'deadline' => now()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['target_amount', 'deadline']);
    }

    public function test_user_can_update_a_goal_target(): void
    {
        $user = User::factory()->create();
        $goal = SavingGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => '1000000.00',
            'deadline' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->actingAs($user)->put(route('saving-goals.update', $goal), [
            'name' => 'Target Baru',
            'target_amount' => '2000000.00',
            'deadline' => now()->addMonths(2)->toDateString(),
            'description' => 'Diperbarui.',
        ]);

        $response->assertRedirect(route('saving-goals.show', $goal));
        $this->assertSame('Target Baru', $goal->fresh()->name);
        $this->assertSame('2000000.00', (string) $goal->fresh()->target_amount);
    }

    public function test_contribution_updates_saved_amount_and_completes_goal(): void
    {
        $user = User::factory()->create();
        $goal = SavingGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => '100000.00',
            'saved_amount' => '0.00',
            'deadline' => now()->addMonth()->toDateString(),
        ]);

        $first = $this->actingAs($user)->post(route('saving-goals.contributions.store', $goal), [
            'amount' => '40000.00',
            'contribution_date' => now()->toDateString(),
            'note' => 'Setoran pertama.',
        ]);

        $first->assertRedirect(route('saving-goals.show', $goal));
        $this->assertSame('40000.00', (string) $goal->fresh()->saved_amount);
        $this->assertNull($goal->fresh()->completed_at);

        $second = $this->actingAs($user)->post(route('saving-goals.contributions.store', $goal), [
            'amount' => '60000.00',
            'contribution_date' => now()->toDateString(),
        ]);

        $second->assertRedirect(route('saving-goals.show', $goal));
        $this->assertSame('100000.00', (string) $goal->fresh()->saved_amount);
        $this->assertNotNull($goal->fresh()->completed_at);
        $this->assertSame(SavingGoalStatus::Completed, $goal->fresh()->status());
        $this->assertSame(2, SavingGoalContribution::query()->count());
    }

    public function test_over_contribution_clamps_progress_and_shows_remaining_negative(): void
    {
        $user = User::factory()->create();
        $goal = SavingGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => '100000.00',
            'saved_amount' => '0.00',
        ]);

        $this->actingAs($user)->post(route('saving-goals.contributions.store', $goal), [
            'amount' => '150000.00',
            'contribution_date' => now()->toDateString(),
        ]);

        $goal->refresh();

        $this->assertSame(100.0, $goal->progressPercentage());
        $this->assertSame(SavingGoalStatus::Completed, $goal->status());
        $this->assertSame('-50000.00', $goal->remainingAmount());
    }

    public function test_user_cannot_view_or_modify_another_users_goal(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $goal = SavingGoal::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user)->get(route('saving-goals.show', $goal))->assertStatus(403);
        $this->actingAs($user)->get(route('saving-goals.edit', $goal))->assertStatus(403);
        $this->actingAs($user)->put(route('saving-goals.update', $goal), [
            'name' => 'Diambil Alih',
            'target_amount' => '1000.00',
        ])->assertStatus(403);
        $this->actingAs($user)->delete(route('saving-goals.destroy', $goal))->assertStatus(403);
        $this->actingAs($user)->post(route('saving-goals.contributions.store', $goal), [
            'amount' => '1000.00',
            'contribution_date' => now()->toDateString(),
        ])->assertStatus(403);
    }

    public function test_deleting_goal_removes_contributions(): void
    {
        $user = User::factory()->create();
        $goal = SavingGoal::factory()->create(['user_id' => $user->id]);
        SavingGoalContribution::factory()->create(['saving_goal_id' => $goal->id]);

        $this->actingAs($user)->delete(route('saving-goals.destroy', $goal))
            ->assertRedirect(route('saving-goals.index'));

        $this->assertDatabaseMissing('saving_goals', ['id' => $goal->id]);
        $this->assertDatabaseMissing('saving_goal_contributions', ['saving_goal_id' => $goal->id]);
    }

    public function test_overdue_goal_status_is_visible(): void
    {
        $user = User::factory()->create();
        $goal = SavingGoal::factory()->create([
            'user_id' => $user->id,
            'name' => 'Target Terlewat',
            'deadline' => now()->subDay()->toDateString(),
            'saved_amount' => '10000.00',
        ]);

        $this->assertSame(SavingGoalStatus::Overdue, $goal->status());

        $response = $this->actingAs($user)->get(route('saving-goals.index'));

        $response->assertOk();
        $response->assertSee('Target Terlewat');
        $response->assertSee('Terlewat');
    }

    public function test_contribution_validation_rejects_zero_and_future_date(): void
    {
        $user = User::factory()->create();
        $goal = SavingGoal::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('saving-goals.contributions.store', $goal), [
            'amount' => '0',
            'contribution_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['amount', 'contribution_date']);
    }
}
