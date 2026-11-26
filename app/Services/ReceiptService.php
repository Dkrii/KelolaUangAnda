<?php

namespace App\Services;

use App\Enums\ReceiptStatus;
use App\Enums\TransactionType;
use App\Jobs\ProcessReceipt;
use App\Models\Receipt;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ReceiptService
{
    public function __construct(private readonly TransactionService $transactionService) {}

    public function createFromUpload(User $user, UploadedFile $file): Receipt
    {
        $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'jpg');
        $path = $file->storeAs(
            'receipts/'.$user->id.'/'.now()->format('Y/m'),
            Str::uuid()->toString().'.'.$extension,
            'local'
        );

        $receipt = $user->receipts()->create([
            'path' => $path,
            'original_name' => basename($file->getClientOriginalName()),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size_in_bytes' => $file->getSize(),
            'status' => ReceiptStatus::Pending->value,
        ]);

        ProcessReceipt::dispatch($receipt);

        return $receipt->refresh();
    }

    public function process(Receipt $receipt): void
    {
        $token = config('services.ocr.token');

        if (! is_string($token) || $token === '') {
            $this->markFailed($receipt, 'OCR_SERVICE_TOKEN belum dikonfigurasi.');

            return;
        }

        try {
            $response = Http::timeout((int) config('services.ocr.timeout', 60))
                ->withHeaders(['X-OCR-Token' => $token])
                ->attach(
                    'file',
                    (string) Storage::disk('local')->get($receipt->path),
                    $receipt->original_name,
                    ['Content-Type' => $receipt->mime_type]
                )
                ->post(rtrim((string) config('services.ocr.url'), '/').'/ocr');

            $response->throw();
            $data = $response->json();
        } catch (Throwable $exception) {
            $this->markFailed($receipt, 'OCR gagal: '.$exception->getMessage());

            return;
        }

        if (! is_array($data)) {
            $this->markFailed($receipt, 'Respons OCR tidak valid.');

            return;
        }

        $this->storeExtraction($receipt, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function retry(Receipt $receipt): void
    {
        $receipt->update([
            'status' => ReceiptStatus::Pending->value,
            'ocr_data' => null,
        ]);

        ProcessReceipt::dispatch($receipt);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function confirm(Receipt $receipt, array $data): Transaction
    {
        abort_unless($receipt->status === ReceiptStatus::Review, 422, 'Struk belum siap dikonfirmasi.');
        abort_if($receipt->transaction_id !== null, 422, 'Struk sudah dikonfirmasi.');

        return DB::transaction(function () use ($receipt, $data) {
            $description = is_string($data['description'] ?? null) ? trim($data['description']) : '';

            $transaction = $this->transactionService->create($receipt->user, [
                'type' => TransactionType::Expense->value,
                'wallet_id' => $data['wallet_id'],
                'category_id' => $data['category_id'],
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'description' => $description !== '' ? $description : ($receipt->merchant ?: 'Transaksi dari struk'),
            ]);

            $ocrData = is_array($receipt->ocr_data) ? $receipt->ocr_data : [];
            $ocrData['confirmed'] = [
                'wallet_id' => $data['wallet_id'],
                'category_id' => $data['category_id'],
                'amount' => (string) $transaction->amount,
                'transaction_date' => $data['transaction_date'],
                'confirmed_at' => now()->toIso8601String(),
            ];

            $receipt->update([
                'transaction_id' => $transaction->id,
                'status' => ReceiptStatus::Confirmed->value,
                'transaction_date' => $data['transaction_date'],
                'total' => $data['amount'],
                'ocr_data' => $ocrData,
            ]);

            return $transaction;
        });
    }

    public function delete(Receipt $receipt): void
    {
        DB::transaction(function () use ($receipt) {
            $receipt->receiptItems()->delete();
            $receipt->delete();
        });

        Storage::disk('local')->delete($receipt->path);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeExtraction(Receipt $receipt, array $data): void
    {
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        DB::transaction(function () use ($receipt, $data, $items) {
            $receipt->update([
                'status' => ReceiptStatus::Review->value,
                'merchant' => $this->nullableString($data['merchant'] ?? null),
                'transaction_date' => $this->parseDate($data['transaction_date'] ?? null)?->toDateString(),
                'total' => $this->parseAmount($data['total'] ?? null),
                'ocr_data' => $data,
            ]);

            $receipt->receiptItems()->delete();

            foreach ($items as $item) {
                if (! is_array($item) || ! is_string($item['name'] ?? null) || trim($item['name']) === '') {
                    continue;
                }

                $receipt->receiptItems()->create([
                    'name' => Str::limit(trim($item['name']), 255),
                    'quantity' => $this->parseAmount($item['quantity'] ?? 1) ?? '1.00',
                    'unit_price' => $this->parseAmount($item['unit_price'] ?? 0) ?? '0.00',
                    'subtotal' => $this->parseAmount($item['subtotal'] ?? 0) ?? '0.00',
                ]);
            }
        });
    }

    private function markFailed(Receipt $receipt, string $message): void
    {
        $receipt->update([
            'status' => ReceiptStatus::Failed->value,
            'ocr_data' => ['error' => $message],
        ]);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        $date = Carbon::createFromFormat('!Y-m-d', $value);

        return $date->toDateString() === $value ? $date : null;
    }

    private function parseAmount(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Str::limit(trim($value), 255);
    }
}
