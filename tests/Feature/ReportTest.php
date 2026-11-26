<?php

namespace Tests\Feature;

use App\Enums\ReportPeriod;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('reports.index'))->assertRedirect('/login');
        $this->get(route('reports.pdf'))->assertRedirect('/login');
        $this->get(route('reports.excel'))->assertRedirect('/login');
    }

    public function test_monthly_report_aggregates_own_transactions_and_excludes_transfers(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $otherWallet = Wallet::factory()->create(['user_id' => $user->id]);
        $incomeCategory = Category::factory()->income()->create(['user_id' => $user->id, 'name' => 'Gaji']);
        $expenseCategory = Category::factory()->expense()->create(['user_id' => $user->id, 'name' => 'Makanan']);
        $otherCategory = Category::factory()->expense()->create(['user_id' => $otherUser->id, 'name' => 'Rahasia']);
        $date = now()->startOfMonth()->addDays(3)->toDateString();

        $this->makeTransaction($user, $wallet, $incomeCategory, TransactionType::Income, '500000.00', $date);
        $this->makeTransaction($user, $wallet, $expenseCategory, TransactionType::Expense, '200000.00', $date);
        $this->makeTransaction($user, $wallet, $expenseCategory, TransactionType::Expense, '50000.00', $date);
        Transaction::factory()->transfer($otherWallet)->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => '100000.00',
            'transaction_date' => $date,
        ]);
        $this->makeTransaction($otherUser, $otherWallet, $otherCategory, TransactionType::Expense, '999000.00', $date);

        $response = $this->actingAs($user)->get(route('reports.index', [
            'period' => ReportPeriod::Monthly->value,
            'date' => $date,
        ]));

        $response->assertOk();
        $response->assertSee('Rp 500.000');
        $response->assertSee('Rp 250.000');
        $response->assertSee('Gaji');
        $response->assertSee('Makanan');
        $response->assertDontSee('Rahasia');
        $response->assertDontSee('Rp 999.000');
        $response->assertDontSee('Rp 350.000');
    }

    public function test_daily_report_excludes_transactions_outside_the_selected_day(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $this->makeTransaction($user, $wallet, $category, TransactionType::Expense, '100000.00', $today);
        $this->makeTransaction($user, $wallet, $category, TransactionType::Expense, '200000.00', $yesterday);

        $response = $this->actingAs($user)->get(route('reports.index', [
            'period' => ReportPeriod::Daily->value,
            'date' => $today,
        ]));

        $response->assertOk();
        $response->assertSee('Rp 100.000');
        $response->assertDontSee('Rp 200.000');
    }

    public function test_yearly_trend_buckets_transactions_by_month(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->income()->create(['user_id' => $user->id]);
        $year = now()->year;

        $this->makeTransaction($user, $wallet, $category, TransactionType::Income, '100000.00', $year.'-01-15');
        $this->makeTransaction($user, $wallet, $category, TransactionType::Income, '250000.00', $year.'-12-10');

        $report = app(ReportService::class)->build(
            $user,
            ReportPeriod::Yearly,
            Carbon::create($year, 6, 1),
        );

        $this->assertSame('Jan', $report['trend']['labels'][0]);
        $this->assertSame('Des', $report['trend']['labels'][11]);
        $this->assertSame('100000.00', $report['trend']['income'][0]);
        $this->assertSame('250000.00', $report['trend']['income'][11]);
    }

    public function test_pdf_export_returns_a_pdf_download(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.pdf', [
            'period' => ReportPeriod::Monthly->value,
            'date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertDownload();
    }

    public function test_excel_export_returns_a_spreadsheet_download(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.excel', [
            'period' => ReportPeriod::Monthly->value,
            'date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_invalid_period_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index', ['period' => 'invalid']));

        $response->assertSessionHasErrors('period');
    }

    private function makeTransaction(
        User $user,
        Wallet $wallet,
        Category $category,
        TransactionType $type,
        string $amount,
        string $date,
    ): Transaction {
        return Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => $type,
            'amount' => $amount,
            'transaction_date' => $date,
        ]);
    }
}
