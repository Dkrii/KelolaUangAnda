<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavingGoalContributionRequest;
use App\Http\Requests\StoreSavingGoalRequest;
use App\Http\Requests\UpdateSavingGoalRequest;
use App\Models\SavingGoal;
use App\Services\SavingGoalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingGoalController extends Controller
{
    public function index(Request $request, SavingGoalService $service): View
    {
        return view('saving-goals.index', $service->summaryFor($request->user()));
    }

    public function create(): View
    {
        return view('saving-goals.create');
    }

    public function store(StoreSavingGoalRequest $request, SavingGoalService $service): RedirectResponse
    {
        $goal = $service->create($request->user(), $request->validated());

        return redirect()
            ->route('saving-goals.show', $goal)
            ->with('status', 'Target tabungan berhasil dibuat.');
    }

    public function show(SavingGoal $savingGoal): View
    {
        $this->authorize('view', $savingGoal);

        $savingGoal->load(['contributions' => fn ($query) => $query->latest('contribution_date')->latest('id')]);

        return view('saving-goals.show', [
            'goal' => $savingGoal,
        ]);
    }

    public function edit(SavingGoal $savingGoal): View
    {
        $this->authorize('update', $savingGoal);

        return view('saving-goals.edit', [
            'goal' => $savingGoal,
        ]);
    }

    public function update(UpdateSavingGoalRequest $request, SavingGoal $savingGoal, SavingGoalService $service): RedirectResponse
    {
        $this->authorize('update', $savingGoal);

        $service->update($savingGoal, $request->validated());

        return redirect()
            ->route('saving-goals.show', $savingGoal)
            ->with('status', 'Target tabungan berhasil diperbarui.');
    }

    public function destroy(SavingGoal $savingGoal, SavingGoalService $service): RedirectResponse
    {
        $this->authorize('delete', $savingGoal);

        $service->delete($savingGoal);

        return redirect()
            ->route('saving-goals.index')
            ->with('status', 'Target tabungan berhasil dihapus.');
    }

    public function storeContribution(
        StoreSavingGoalContributionRequest $request,
        SavingGoal $savingGoal,
        SavingGoalService $service
    ): RedirectResponse {
        $this->authorize('contribute', $savingGoal);

        $service->addContribution($savingGoal, $request->validated());

        return redirect()
            ->route('saving-goals.show', $savingGoal)
            ->with('status', 'Setoran berhasil dicatat.');
    }
}
