<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_visiting_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create(['name' => 'Siti Rahma']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Keuangan');
        $response->assertSee('Total Saldo');
        $response->assertSee('Pemasukan Bulan Ini');
        $response->assertSee('Pengeluaran Bulan Ini');
        $response->assertSee('Sisa Anggaran');
        $response->assertSee('Rp 0');
        $response->assertSee('Belum ada transaksi');
    }

    public function test_dashboard_renders_charts_from_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->expense()->create([
            'user_id' => $user->id,
            'name' => 'Belanja Harian',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'amount' => '150000.00',
            'transaction_date' => now()->toDateString(),
        ]);

        Transaction::factory()->income()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => '300000.00',
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Arus Kas Masuk vs Keluar');
        $response->assertSee('cashFlowChart');
        $response->assertSee('cashFlowChartData');
        $response->assertSee('expenseCategoryChart');
        $response->assertSee('expenseCategoryChartData');
        $response->assertSee('Belanja Harian');
        $response->assertSee('Rp 300.000');
        $response->assertSee('Rp 150.000');
    }

    public function test_dashboard_budget_remaining_reflects_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        Budget::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => '50000.00',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'amount' => '70000.00',
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Rp -20.000');
    }

    public function test_dashboard_charts_exclude_transfers(): void
    {
        $user = User::factory()->create();
        $source = Wallet::factory()->create(['user_id' => $user->id]);
        $destination = Wallet::factory()->create(['user_id' => $user->id]);

        Transaction::factory()->transfer($destination)->create([
            'user_id' => $user->id,
            'wallet_id' => $source->id,
            'amount' => '900000.00',
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Belum ada arus kas');
        $response->assertDontSee('id="cashFlowChart"');
    }

    public function test_landing_page_can_be_viewed_by_guest(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Kelola Uang Anda');
        $response->assertSee('Daftar Gratis');
        $response->assertSee('Masuk');
    }
}
