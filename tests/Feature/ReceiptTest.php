<?php

namespace Tests\Feature;

use App\Enums\ReceiptStatus;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ocr.url', 'http://ocr.test');
        config()->set('services.ocr.token', 'test-token');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('receipts.index'))->assertRedirect('/login');
        $this->get(route('receipts.create'))->assertRedirect('/login');
    }

    public function test_upload_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('receipts.create'))
            ->assertOk()
            ->assertSee('Unggah Struk')
            ->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_upload_sends_image_to_ocr_and_stores_extraction(): void
    {
        Storage::fake('local');
        Http::fake(['*' => Http::response($this->ocrPayload())]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('receipts.store'), [
            'image' => UploadedFile::fake()->image('struk.png', 640, 480)->size(128),
        ]);

        $receipt = Receipt::query()->firstOrFail();

        $response->assertRedirect(route('receipts.show', $receipt))
            ->assertSessionHas('status');

        $this->assertSame(ReceiptStatus::Review, $receipt->status);
        $this->assertSame('Toko Contoh', $receipt->merchant);
        $this->assertSame('25000.00', (string) $receipt->total);
        $this->assertSame(now()->toDateString(), $receipt->transaction_date->toDateString());
        $this->assertCount(1, $receipt->receiptItems);
        $this->assertSame('Roti', $receipt->receiptItems->first()->name);
        $this->assertSame('10000.00', (string) $receipt->receiptItems->first()->subtotal);
        $this->assertContains('Jumlah item tidak cocok dengan total struk.', $receipt->ocr_data['warnings']);
        Storage::disk('local')->assertExists($receipt->path);

        Http::assertSent(function (ClientRequest $request) {
            return str_contains($request->url(), '/ocr')
                && $request->hasHeader('X-OCR-Token', 'test-token')
                && $request->hasFile('file');
        });
    }

    public function test_upload_rejects_invalid_image_and_oversized_file(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('receipts.store'), [
                'image' => UploadedFile::fake()->create('script.txt', 16, 'text/plain'),
            ])
            ->assertSessionHasErrors('image');

        $this->actingAs($user)
            ->post(route('receipts.store'), [
                'image' => UploadedFile::fake()->image('besar.jpg')->size(10241),
            ])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, Receipt::query()->count());
    }

    public function test_failed_ocr_is_recorded_and_can_be_retried(): void
    {
        Storage::fake('local');
        Http::fakeSequence()
            ->push('Gagal', 500)
            ->push($this->ocrPayload());

        $user = User::factory()->create();
        $receipt = $this->reviewReceipt($user, ReceiptStatus::Failed);

        $this->actingAs($user)
            ->post(route('receipts.retry', $receipt))
            ->assertRedirect(route('receipts.show', $receipt));

        $this->assertSame(ReceiptStatus::Failed, $receipt->fresh()->status);
        $this->assertStringContainsString('OCR gagal', $receipt->fresh()->ocr_data['error']);

        $this->actingAs($user)
            ->post(route('receipts.retry', $receipt))
            ->assertRedirect(route('receipts.show', $receipt));

        $this->assertSame(
            ReceiptStatus::Review,
            $receipt->fresh()->status,
            json_encode($receipt->fresh()->ocr_data) ?: ''
        );
    }

    public function test_confirm_creates_expense_transaction_and_updates_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'initial_balance' => '100000.00',
        ]);
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $receipt = $this->reviewReceipt($user);

        $this->actingAs($user)
            ->post(route('receipts.confirm', $receipt), [
                'wallet_id' => $wallet->id,
                'category_id' => $category->id,
                'amount' => '25000.00',
                'transaction_date' => now()->toDateString(),
                'description' => 'Beli roti',
            ])
            ->assertRedirect(route('transactions.show', $receipt->fresh()->transaction));

        $this->assertSame(ReceiptStatus::Confirmed, $receipt->fresh()->status);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense->value,
            'amount' => '25000.00',
            'description' => 'Beli roti',
        ]);
        $this->assertSame('75000.00', app(WalletService::class)->balanceFor($wallet));
        $this->assertArrayHasKey('confirmed', $receipt->fresh()->ocr_data);
    }

    public function test_confirm_uses_merchant_as_default_description(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $receipt = $this->reviewReceipt($user);

        $this->actingAs($user)->post(route('receipts.confirm', $receipt), [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => '25000.00',
            'transaction_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame('Toko Contoh', $receipt->fresh()->transaction->description);
    }

    public function test_confirm_validates_ownership_and_amount(): void
    {
        $user = User::factory()->create();
        $otherWallet = Wallet::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $receipt = $this->reviewReceipt($user);

        $this->actingAs($user)
            ->post(route('receipts.confirm', $receipt), [
                'wallet_id' => $otherWallet->id,
                'category_id' => $category->id,
                'amount' => '0',
                'transaction_date' => now()->toDateString(),
            ])
            ->assertSessionHasErrors(['wallet_id', 'amount']);

        $this->assertSame(ReceiptStatus::Review, $receipt->fresh()->status);
    }

    public function test_confirmed_receipt_cannot_be_confirmed_twice(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $receipt = $this->reviewReceipt($user);

        $payload = [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => '25000.00',
            'transaction_date' => now()->toDateString(),
        ];

        $this->actingAs($user)->post(route('receipts.confirm', $receipt), $payload)->assertRedirect();
        $this->actingAs($user)->post(route('receipts.confirm', $receipt), $payload)->assertStatus(422);

        $this->assertSame(1, $user->transactions()->count());
    }

    public function test_user_cannot_view_or_modify_another_users_receipt(): void
    {
        $user = User::factory()->create();
        $otherReceipt = $this->reviewReceipt(User::factory()->create());

        $this->actingAs($user)->get(route('receipts.show', $otherReceipt))->assertForbidden();
        $this->actingAs($user)->get(route('receipts.image', $otherReceipt))->assertForbidden();
        $this->actingAs($user)->delete(route('receipts.destroy', $otherReceipt))->assertForbidden();
    }

    public function test_delete_removes_receipt_but_keeps_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->expense()->create(['user_id' => $user->id]);
        $receipt = $this->reviewReceipt($user);

        $this->actingAs($user)->post(route('receipts.confirm', $receipt), [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'amount' => '25000.00',
            'transaction_date' => now()->toDateString(),
        ])->assertRedirect();

        $path = $receipt->path;
        $transactionId = $receipt->fresh()->transaction_id;

        $this->actingAs($user)
            ->delete(route('receipts.destroy', $receipt))
            ->assertRedirect(route('receipts.index'));

        $this->assertSoftDeleted('receipts', ['id' => $receipt->id]);
        $this->assertDatabaseHas('transactions', ['id' => $transactionId]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_index_shows_only_own_receipts(): void
    {
        $user = User::factory()->create();
        $own = $this->reviewReceipt($user);
        $other = $this->reviewReceipt(User::factory()->create());

        $this->actingAs($user)
            ->get(route('receipts.index'))
            ->assertOk()
            ->assertSee('Toko Contoh')
            ->assertSee(route('receipts.show', $own))
            ->assertDontSee(route('receipts.show', $other));
    }

    /**
     * @return array<string, mixed>
     */
    private function ocrPayload(): array
    {
        return [
            'merchant' => 'Toko Contoh',
            'transaction_date' => now()->toDateString(),
            'total' => '25000.00',
            'items' => [
                [
                    'name' => 'Roti',
                    'quantity' => '2',
                    'unit_price' => '5000.00',
                    'subtotal' => '10000.00',
                ],
            ],
            'raw_text' => "TOKO CONTOH\nRoti 2 x 5000 10000\nTOTAL 25000",
            'warnings' => ['Jumlah item tidak cocok dengan total struk.'],
            'suggested_category' => 'Makanan & Minuman',
            'metadata' => ['parser' => 'receipt-rules-v1', 'line_count' => 3],
            'ocr_confidence' => 0.975,
        ];
    }

    private function reviewReceipt(User $user, ReceiptStatus $status = ReceiptStatus::Review): Receipt
    {
        $path = 'receipts/'.$user->id.'/struk.png';
        Storage::disk('local')->put($path, 'fake-image');

        return $user->receipts()->create([
            'path' => $path,
            'original_name' => 'struk.png',
            'mime_type' => 'image/png',
            'size_in_bytes' => 1024,
            'status' => $status->value,
            'merchant' => 'Toko Contoh',
            'transaction_date' => now()->toDateString(),
            'total' => '25000.00',
            'ocr_data' => ['items' => [], 'raw_text' => 'TOKO CONTOH'],
        ]);
    }
}
