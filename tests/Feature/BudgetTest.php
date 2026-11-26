<?php

namespace Tests\Feature;

use App\Enums\BudgetPeriod;
use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('budgets.index'))->assertRedirect('/login');
        $this->get(route('budgets.create'))->assertRedirect('/login');
    }

    public function test_index_shows_only_own_budgets(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownCategory = Category::factory()->expense()->create(['user_id' => $user->id]);
        $otherCategory = Category::factory()->expense()->create(['user_id' => $otherUser->id]);

        Budget::factory()->create([
            'user_id' => $user->id,
            'category_id' => $ownCategory->id,
            'amount' => '500000.00',
        ]);
        Budget::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
            'amount' => '999999.00',
        ]);

        $response = $this->actingAs($user)->get(route('budgets.index'));

        $response->assertStatus(200);
        $response->assertSee($ownCategory->name);
        $response->assertSee('Rp 500.000');
        $response->assertDontSee($otherCategory->name);
        $response->assertDontSee('Rp 999.999');
    }

    public function test_create_page_lists_only_expense_categories(): void
    {
        $user = User::factory()->create();
        $expense = Category::factory()->expense()->create(['user_id' => $user->id, 'name' => 'Makanan']);
        $income = Category::factory()->income()->create(['user_id' => $user->id, 'name' => 'Gaji']);

        $response = $this->actingAs($user)->get(route('budgets.create'));

        $response->assertStatus(200);
        $response->assertSee('Makanan');
        $response->assertDontSee('Gaji');
    }

    public function test_user_can_create_budget_for_current_month(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $month = now()->startOfMonth()->format('Y-m');

        $response = $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => '1500000.50',
            'month' => $month,
        ]);

        $response->assertRedirect(route('budgets.index', ['month' => $month]));
        $response->assertSessionHas('status', 'Anggaran berhasil ditambahkan.');

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'period' => BudgetPeriod::Monthly->value,
            'month' => now()->startOfMonth()->toDateString(),
        ]);
        $this->assertSame('1500000.50', (string) Budget::query()->first()->amount);
    }

    public function test_duplicate_category_and_month_is_rejected(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $month = now()->startOfMonth()->format('Y-m');

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => now()->startOfMonth()->toDateString(),
        ]);

        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => '100000',
            'month' => $month,
        ])->assertSessionHasErrors('category_id');

        $this->assertSame(1, Budget::query()->count());

        $otherMonth = now()->startOfMonth()->addMonth()->format('Y-m');
        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => '100000',
            'month' => $otherMonth,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'month' => now()->startOfMonth()->toDateString(),
        ]);
        $this->assertSame(2, Budget::query()->count());
    }

    public function test_income_or_foreign_category_cannot_be_used(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $income = Category::factory()->income()->create(['user_id' => $user->id]);
        $foreign = Category::factory()->expense()->create(['user_id' => $otherUser->id]);
        $month = now()->startOfMonth()->format('Y-m');

        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $income->id,
            'amount' => '100000',
            'month' => $month,
        ])->assertSessionHasErrors('category_id');

        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $foreign->id,
            'amount' => '100000',
            'month' => $month,
        ])->assertSessionHasErrors('category_id');

        $this->assertSame(0, Budget::query()->count());
    }

    public function test_amount_validation_rejects_invalid_values(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $month = now()->startOfMonth()->format('Y-m');

        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => '0',
            'month' => $month,
        ])->assertSessionHasErrors('amount');

        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => '100.001',
            'month' => $month,
        ])->assertSessionHasErrors('amount');

        $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => '100000',
            'month' => now()->format('Y-m-d'),
        ])->assertSessionHasErrors('month');

        $this->assertSame(0, Budget::query()->count());
    }

    public function test_updating_amount_recalculates_remaining_budget(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => '100000.00',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'amount' => '40000.00',
            'transaction_date' => now()->startOfMonth()->toDateString(),
        ]);

        $service = $this->app->make(BudgetService::class);
        $before = $service->summaryFor($user, now());
        $this->assertSame('60000.00', $before['remaining']);

        $response = $this->actingAs($user)->put(route('budgets.update', $budget), [
            'amount' => '200000.00',
        ]);

        $response->assertRedirect(route('budgets.index', ['month' => now()->startOfMonth()->format('Y-m')]));
        $this->assertSame('200000.00', (string) $budget->fresh()->amount);

        $after = $service->summaryFor($user, now());
        $this->assertSame('160000.00', $after['remaining']);
    }

    public function test_usage_status_thresholds_are_computed(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $month = now()->startOfMonth()->toDateString();

        $categories = collect([
            'normal' => Category::factory()->expense()->create(['user_id' => $user->id]),
            'warning' => Category::factory()->expense()->create(['user_id' => $user->id]),
            'exceeded' => Category::factory()->expense()->create(['user_id' => $user->id]),
        ]);

        foreach (['normal' => '79999.99', 'warning' => '80000.00', 'exceeded' => '100000.00'] as $status => $spent) {
            Budget::factory()->create([
                'user_id' => $user->id,
                'category_id' => $categories->get($status)->id,
                'amount' => '100000.00',
            ]);

            Transaction::factory()->create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'category_id' => $categories->get($status)->id,
                'type' => TransactionType::Expense,
                'amount' => $spent,
                'transaction_date' => $month,
            ]);
        }

        $summary = $this->app->make(BudgetService::class)->summaryFor($user, now());

        $this->assertSame('300000.00', $summary['totalBudget']);
        $this->assertSame('259999.99', $summary['totalSpent']);
        $this->assertSame('40000.01', $summary['remaining']);

        $statuses = $summary['budgets']->mapWithKeys(fn (Budget $budget) => [
            $budget->category_id => $budget->status,
        ]);

        $this->assertSame('normal', $statuses->get($categories->get('normal')->id));
        $this->assertSame('warning', $statuses->get($categories->get('warning')->id));
        $this->assertSame('exceeded', $statuses->get($categories->get('exceeded')->id));
    }

    public function test_user_cannot_modify_or_delete_another_users_budget(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $otherUser->id]);
        $budget = Budget::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($user)->get(route('budgets.edit', $budget))->assertForbidden();
        $this->actingAs($user)->put(route('budgets.update', $budget), ['amount' => '500000'])->assertForbidden();
        $this->actingAs($user)->delete(route('budgets.destroy', $budget))->assertForbidden();

        $this->assertDatabaseHas('budgets', ['id' => $budget->id]);
    }

    public function test_user_can_delete_budget(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('budgets.destroy', $budget));

        $response->assertRedirect(route('budgets.index', ['month' => now()->startOfMonth()->format('Y-m')]));
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);

        $this->actingAs($user)->get(route('budgets.index'))
            ->assertSee('Belum ada anggaran');
    }
}
