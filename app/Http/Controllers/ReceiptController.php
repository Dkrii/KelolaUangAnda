<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Enums\ReceiptStatus;
use App\Http\Requests\ConfirmReceiptRequest;
use App\Http\Requests\StoreReceiptRequest;
use App\Jobs\ProcessReceipt;
use App\Models\Receipt;
use App\Services\ReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    public function index(Request $request): View
    {
        return view('receipts.index', [
            'receipts' => $request->user()->receipts()
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('receipts.create');
    }

    public function store(StoreReceiptRequest $request, ReceiptService $service): RedirectResponse
    {
        $receipt = $service->createFromUpload($request->user(), $request->file('image'));

        return redirect()
            ->route('receipts.show', $receipt)
            ->with('status', 'Struk berhasil diunggah. Hasil OCR sedang diproses.');
    }

    public function show(Request $request, Receipt $receipt): View
    {
        $this->authorize('view', $receipt);

        $receipt->load(['receiptItems', 'transaction']);

        return view('receipts.show', [
            'receipt' => $receipt,
            'wallets' => $request->user()->wallets()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'categories' => $request->user()->categories()
                ->where('type', CategoryType::Expense->value)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function image(Receipt $receipt): StreamedResponse
    {
        $this->authorize('view', $receipt);

        return Storage::disk('local')->response($receipt->path, $receipt->original_name);
    }

    public function retry(Receipt $receipt): RedirectResponse
    {
        $this->authorize('retry', $receipt);

        abort_unless($receipt->status === ReceiptStatus::Failed, 422, 'Hanya struk gagal yang bisa diproses ulang.');

        ProcessReceipt::dispatch($receipt);

        return redirect()
            ->route('receipts.show', $receipt)
            ->with('status', 'Struk sedang diproses ulang.');
    }

    public function confirm(ConfirmReceiptRequest $request, Receipt $receipt, ReceiptService $service): RedirectResponse
    {
        $this->authorize('confirm', $receipt);

        $transaction = $service->confirm($receipt, $request->validated());

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('status', 'Struk dikonfirmasi. Transaksi pengeluaran berhasil dibuat.');
    }

    public function destroy(Receipt $receipt, ReceiptService $service): RedirectResponse
    {
        $this->authorize('delete', $receipt);

        $service->delete($receipt);

        return redirect()
            ->route('receipts.index')
            ->with('status', 'Struk berhasil dihapus.');
    }
}
