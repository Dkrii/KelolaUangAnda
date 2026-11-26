<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DashboardService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_transfer_form(): void
    {
        $this->get(route('transfers.create'))->assertRedirect('/login');
    }

    public function test_transfer_form_renders_with_explanation(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->for($user)->create(['name' => 'Tunai']);
        Wallet::factory()->for($user)->create(['name' => 'BCA']);

        $this->actingAs($user)->get(route('transfers.create'))
            ->assertStatus(200)
            ->assertSee('Transfer Antar Dompet')
            ->assertSee('Tunai')
            ->assertSee('BCA')
            ->assertSee('tidak dihitung sebagai pemasukan maupun pengeluaran');
    }

    public function test_transfer_moves_balance_without_changing_total(): void
    {
        $user = User::factory()->create();
        $source = Wallet::factory()->for($user)->create(['initial_balance' => 500000]);
        $destination = Wallet::factory()->for($user)->create(['initial_balance' => 100000]);

        $response = $this->actingAs($user)->post(route('transfers.store'), [
            'from_wallet_id' => $source->id,
            'to_wallet_id' => $destination->id,
            'amount' => 150000,
            'transaction_date' => now()->toDateString(),
            'description' => 'Pindah ke tabungan',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => TransactionType::Transfer->value,
            'wallet_id' => $source->id,
            'destination_wallet_id' => $destination->id,
            'category_id' => null,
            'amount' => '150000.00',
        ]);

        $service = app(WalletService::class);
        $this->assertSame('350000.00', $service->balanceFor($source));
        $this->assertSame('250000.00', $service->balanceFor($destination));
        $this->assertSame('600000.00', $service->totalBalance($user));
    }

    public function test_transfer_is_excluded_from_monthly_income_and_expense(): void
    {
        $user = User::factory()->create();
        $source = Wallet::factory()->for($user)->create();
        $destination = Wallet::factory()->for($user)->create();

        $this->actingAs($user)->post(route('transfers.store'), [
            'from_wallet_id' => $source->id,
            'to_wallet_id' => $destination->id,
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
        ]);

        $summary = app(DashboardService::class)->getSummary($user);

        $this->assertSame('0.00', $summary['income']);
        $this->assertSame('0.00', $summary['expense']);
        $this->assertSame('0.00', $summary['balance']);
        $this->assertCount(1, $summary['recentTransactions']);
    }

    public function test_transfer_validation_rejects_same_wallet_and_foreign_wallet(): void
    {
        $user = User::factory()->create();
        $source = Wallet::factory()->for($user)->create();
        $foreignWallet = Wallet::factory()->for(User::factory())->create();

        $base = [
            'from_wallet_id' => $source->id,
            'amount' => 10000,
            'transaction_date' => now()->toDateString(),
        ];

        $this->actingAs($user)->post(route('transfers.store'), [
            ...$base,
            'to_wallet_id' => $source->id,
        ])->assertSessionHasErrors(['to_wallet_id']);

        $this->actingAs($user)->post(route('transfers.store'), [
            ...$base,
            'to_wallet_id' => $foreignWallet->id,
        ])->assertSessionHasErrors(['to_wallet_id']);

        $this->actingAs($user)->post(route('transfers.store'), [
            ...$base,
            'to_wallet_id' => $foreignWallet->id,
            'amount' => -1,
        ])->assertSessionHasErrors(['amount']);
    }

    public function test_deleting_transfer_restores_both_wallet_balances(): void
    {
        $user = User::factory()->create();
        $source = Wallet::factory()->for($user)->create(['initial_balance' => 300000]);
        $destination = Wallet::factory()->for($user)->create(['initial_balance' => 100000]);
        $transfer = Transaction::factory()->transfer($destination)->create([
            'user_id' => $user->id,
            'wallet_id' => $source->id,
            'amount' => 75000,
        ]);

        $this->actingAs($user)->delete(route('transactions.destroy', $transfer))
            ->assertRedirect(route('transactions.index'));

        $service = app(WalletService::class);
        $this->assertSame('300000.00', $service->balanceFor($source));
        $this->assertSame('100000.00', $service->balanceFor($destination));
    }

    public function test_wallet_filter_finds_transfers_by_destination(): void
    {
        $user = User::factory()->create();
        $source = Wallet::factory()->for($user)->create(['name' => 'Sumber']);
        $destination = Wallet::factory()->for($user)->create(['name' => 'Tujuan']);
        Transaction::factory()->transfer($destination)->create([
            'user_id' => $user->id,
            'wallet_id' => $source->id,
            'description' => 'Transfer terfilter',
        ]);

        $this->actingAs($user)->get(route('transactions.index', ['wallet_id' => $destination->id]))
            ->assertStatus(200)
            ->assertSee('Transfer terfilter');
    }
}
