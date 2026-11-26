<?php

namespace App\Http\Controllers;

use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request, BudgetService $service): View
    {
        $month = $this->parseMonth($request->string('month')->toString());

        return view('budgets.index', [
            'month' => $month,
            'summary' => $service->summaryFor($request->user(), $month),
        ]);
    }

    public function create(Request $request): View
    {
        return view('budgets.create', [
            'categories' => $request->user()->categories()
                ->where('type', CategoryType::Expense->value)
                ->orderBy('name')
                ->get(),
            'month' => now()->startOfMonth(),
        ]);
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $month = Carbon::createFromFormat('!Y-m', $data['month'])->startOfMonth();

        $request->user()->budgets()->create([
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'month' => $month->toDateString(),
            'period' => BudgetPeriod::Monthly->value,
        ]);

        return redirect()
            ->route('budgets.index', ['month' => $month->format('Y-m')])
            ->with('status', 'Anggaran berhasil ditambahkan.');
    }

    public function edit(Budget $budget): View
    {
        $this->authorize('update', $budget);

        return view('budgets.edit', [
            'budget' => $budget,
        ]);
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);

        $budget->update($request->validated());

        return redirect()
            ->route('budgets.index', ['month' => $budget->month->format('Y-m')])
            ->with('status', 'Anggaran berhasil diperbarui.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);

        $month = $budget->month->format('Y-m');
        $budget->delete();

        return redirect()
            ->route('budgets.index', ['month' => $month])
            ->with('status', 'Anggaran berhasil dihapus.');
    }

    private function parseMonth(string $value): Carbon
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
            return Carbon::createFromFormat('!Y-m', $value)->startOfMonth();
        }

        return now()->startOfMonth();
    }
}
