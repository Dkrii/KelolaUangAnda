<?php

namespace Tests\Feature;

use App\Enums\WalletType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_wallet_pages(): void
    {
        $this->get(route('wallets.index'))->assertRedirect('/login');
        $this->get(route('wallets.create'))->assertRedirect('/login');
    }

    public function test_user_can_list_only_own_wallets(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $own = Wallet::factory()->for($user)->create(['name' => 'Dompet Saya']);
        Wallet::factory()->for($other)->create(['name' => 'Dompet Orang Lain']);

        $response = $this->actingAs($user)->get(route('wallets.index'));

        $response->assertStatus(200);
        $response->assertSee('Dompet Saya');
        $response->assertDontSee('Dompet Orang Lain');
        $this->assertTrue($own->is($user->wallets()->first()));
    }

    public function test_user_can_create_wallet_with_defaults(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallets.store'), [
            'name' => 'Rekening BCA',
            'type' => WalletType::Bank->value,
            'institution' => 'BCA',
            'account_number' => '1234567890',
        ]);

        $response->assertRedirect(route('wallets.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'name' => 'Rekening BCA',
            'type' => WalletType::Bank->value,
            'initial_balance' => '0.00',
            'color' => '#10b981',
            'is_active' => true,
        ]);
    }

    public function test_wallet_name_must_be_unique_per_user(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->for($user)->create(['name' => 'Tunai']);

        $response = $this->actingAs($user)->post(route('wallets.store'), [
            'name' => 'Tunai',
            'type' => WalletType::Cash->value,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_user_cannot_edit_or_delete_another_users_wallet(): void
    {
        $user = User::factory()->create();
        $otherWallet = Wallet::factory()->for(User::factory())->create();

        $this->actingAs($user)->get(route('wallets.edit', $otherWallet))->assertForbidden();
        $this->actingAs($user)->put(route('wallets.update', $otherWallet), [
            'name' => 'Dicuri',
            'type' => WalletType::Cash->value,
        ])->assertForbidden();
        $this->actingAs($user)->delete(route('wallets.destroy', $otherWallet))->assertForbidden();

        $this->assertDatabaseHas('wallets', ['id' => $otherWallet->id, 'name' => $otherWallet->name]);
    }

    public function test_wallet_with_transactions_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
        ]);

        $response = $this->actingAs($user)->delete(route('wallets.destroy', $wallet));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('wallets', ['id' => $wallet->id, 'deleted_at' => null]);
    }

    public function test_empty_wallet_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('wallets.destroy', $wallet));

        $response->assertRedirect(route('wallets.index'));
        $this->assertSoftDeleted('wallets', ['id' => $wallet->id]);
    }

    public function test_balance_is_initial_plus_income_minus_expense(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create(['initial_balance' => 100000]);
        $incomeCategory = Category::factory()->for($user)->income()->create();
        $expenseCategory = Category::factory()->for($user)->expense()->create();

        Transaction::factory()->for($user)->income()->create([
            'wallet_id' => $wallet->id,
            'category_id' => $incomeCategory->id,
            'amount' => 50000,
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'wallet_id' => $wallet->id,
            'category_id' => $expenseCategory->id,
            'amount' => 30000,
        ]);

        $service = app(WalletService::class);

        $this->assertSame('120000.00', $service->balanceFor($wallet));
        $this->assertSame('120000.00', $service->totalBalance($user));
    }
}
