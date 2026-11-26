<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_transaction_pages(): void
    {
        $this->get(route('transactions.index'))->assertRedirect('/login');
        $this->get(route('transactions.create'))->assertRedirect('/login');
    }

    public function test_create_page_renders(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->for($user)->create(['name' => 'Tunai']);
        Category::factory()->for($user)->expense()->create(['name' => 'Makanan']);

        $this->actingAs($user)->get(route('transactions.create'))
            ->assertStatus(200)
            ->assertSee('Jenis Transaksi')
            ->assertSee('Makanan');
    }

    public function test_income_and_expense_are_recorded_and_change_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create(['initial_balance' => 100000]);
        $incomeCategory = Category::factory()->for($user)->income()->create();
        $expenseCategory = Category::factory()->for($user)->expense()->create();

        $this->actingAs($user)->post(route('transactions.store'), [
            'type' => TransactionType::Income->value,
            'wallet_id' => $wallet->id,
            'category_id' => $incomeCategory->id,
            'amount' => 250000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Gaji bulanan',
        ])->assertRedirect(route('transactions.index'));

        $this->actingAs($user)->post(route('transactions.store'), [
            'type' => TransactionType::Expense->value,
            'wallet_id' => $wallet->id,
            'category_id' => $expenseCategory->id,
            'amount' => 75000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Belanja',
        ])->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => TransactionType::Income->value,
            'amount' => '250000.00',
            'description' => 'Gaji bulanan',
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => TransactionType::Expense->value,
            'amount' => '75000.00',
            'description' => 'Belanja',
        ]);
        $this->assertSame('275000.00', app(WalletService::class)->balanceFor($wallet));
    }

    public function test_category_type_must_match_transaction_type(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $expenseCategory = Category::factory()->for($user)->expense()->create();

        $this->actingAs($user)->post(route('transactions.store'), [
            'type' => TransactionType::Income->value,
            'wallet_id' => $wallet->id,
            'category_id' => $expenseCategory->id,
            'amount' => 10000,
            'transaction_date' => now()->toDateString(),
        ])->assertSessionHasErrors(['category_id']);
    }

    public function test_transfer_via_transaction_endpoint_requires_destination_and_forbids_category(): void
    {
        $user = User::factory()->create();
        $source = Wallet::factory()->for($user)->create();
        $destination = Wallet::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();

        $base = [
            'type' => TransactionType::Transfer->value,
            'wallet_id' => $source->id,
            'amount' => 10000,
            'transaction_date' => now()->toDateString(),
        ];

        $this->actingAs($user)->post(route('transactions.store'), $base)
            ->assertSessionHasErrors(['destination_wallet_id']);

        $this->actingAs($user)->post(route('transactions.store'), [
            ...$base,
            'destination_wallet_id' => $source->id,
        ])->assertSessionHasErrors(['destination_wallet_id']);

        $this->actingAs($user)->post(route('transactions.store'), [
            ...$base,
            'destination_wallet_id' => $destination->id,
            'category_id' => $category->id,
        ])->assertSessionHasErrors(['category_id']);
    }

    public function test_amount_and_date_are_validated(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();

        $base = [
            'type' => TransactionType::Expense->value,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
        ];

        $this->actingAs($user)->post(route('transactions.store'), [
            ...$base,
            'amount' => 0,
        ])->assertSessionHasErrors(['amount']);

        $this->actingAs($user)->post(route('transactions.store'), [
            ...$base,
            'amount' => 10000,
            'transaction_date' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors(['transaction_date']);
    }

    public function test_updating_transaction_recalculates_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create(['initial_balance' => 100000]);
        $category = Category::factory()->for($user)->expense()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => 20000,
        ]);

        $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'type' => TransactionType::Expense->value,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => 35000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Diubah',
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame('65000.00', app(WalletService::class)->balanceFor($wallet));
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'amount' => '35000.00']);
    }

    public function test_deleting_transaction_restores_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create(['initial_balance' => 100000]);
        $category = Category::factory()->for($user)->expense()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => 40000,
        ]);

        $this->actingAs($user)->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.index'));

        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
        $this->assertSame('100000.00', app(WalletService::class)->balanceFor($wallet));
    }

    public function test_index_filters_by_type_search_and_date(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $category = Category::factory()->for($user)->expense()->create();
        $today = now()->toDateString();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'description' => 'Kopi pagi',
            'transaction_date' => $today,
        ]);
        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'description' => 'Lainnya',
            'transaction_date' => now()->subMonth()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('transactions.index', [
            'type' => TransactionType::Expense->value,
            'search' => 'Kopi',
            'from' => $today,
            'to' => $today,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kopi pagi');
        $response->assertDontSee('Lainnya');
    }

    public function test_user_cannot_view_or_modify_another_users_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownWallet = Wallet::factory()->for($user)->create();
        $ownCategory = Category::factory()->for($user)->expense()->create();
        $otherWallet = Wallet::factory()->for($otherUser)->create();
        $otherCategory = Category::factory()->for($otherUser)->expense()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'wallet_id' => $otherWallet->id,
            'category_id' => $otherCategory->id,
        ]);

        $this->actingAs($user)->get(route('transactions.show', $transaction))->assertForbidden();
        $this->actingAs($user)->get(route('transactions.edit', $transaction))->assertForbidden();
        $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'type' => TransactionType::Expense->value,
            'wallet_id' => $ownWallet->id,
            'category_id' => $ownCategory->id,
            'amount' => 1000,
            'transaction_date' => now()->toDateString(),
        ])->assertForbidden();
        $this->actingAs($user)->delete(route('transactions.destroy', $transaction))->assertForbidden();

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'deleted_at' => null]);
    }

    public function test_show_page_displays_transaction_detail(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create(['name' => 'Tunai']);
        $category = Category::factory()->for($user)->expense()->create(['name' => 'Makanan']);
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'description' => 'Makan siang',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.show', $transaction));

        $response->assertStatus(200);
        $response->assertSee('Makan siang');
        $response->assertSee('Tunai');
        $response->assertSee('Makanan');
    }
}
