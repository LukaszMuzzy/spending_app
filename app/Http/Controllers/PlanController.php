<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Income;
use App\Models\Receipt;
use App\Models\ShoppingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $groupIds = $user->shoppingGroupIds();
        $shoppingGroups = $user->shopping_groups()->orderBy('name')->get();
        $shoppingTypes = ShoppingType::orderBy('description')->get();

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $spentByType = Receipt::query()
            ->selectRaw('shopping_group_id, shopping_type_id, SUM(price) as total_price')
            ->whereIn('shopping_group_id', $groupIds ?: [0])
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->groupBy('shopping_group_id', 'shopping_type_id')
            ->get()
            ->keyBy(fn ($row) => $row->shopping_group_id.'-'.$row->shopping_type_id);

        $budgets = Budget::query()
            ->with(['shoppingGroup', 'shoppingType'])
            ->whereIn('shopping_group_id', $groupIds ?: [0])
            ->orderBy('shopping_group_id')
            ->get()
            ->map(function (Budget $budget) use ($spentByType) {
                $key = $budget->shopping_group_id.'-'.$budget->shopping_type_id;

                return [
                    'id' => $budget->id,
                    'shopping_group_id' => $budget->shopping_group_id,
                    'shopping_type_id' => $budget->shopping_type_id,
                    'amount' => (float) $budget->amount,
                    'spent' => (float) ($spentByType[$key]->total_price ?? 0),
                    'shopping_group' => $budget->shoppingGroup,
                    'shopping_type' => $budget->shoppingType,
                ];
            });

        $incomes = Income::query()
            ->with('shoppingGroup')
            ->whereIn('shopping_group_id', $groupIds ?: [0])
            ->orderBy('name')
            ->get();

        return Inertia::render('Plan/Index', [
            'budgets' => $budgets,
            'incomes' => $incomes,
            'shopping_groups' => $shoppingGroups,
            'shopping_types' => $shoppingTypes,
            'frequencies' => ['daily', 'weekly', 'fortnightly', 'monthly', 'quarterly', 'half-yearly', 'yearly'],
        ]);
    }

    public function storeBudget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shopping_group_id' => 'required|exists:shopping_groups,id',
            'shopping_type_id' => 'required|exists:shopping_types,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $this->assertGroupAccess((int) $validated['shopping_group_id']);

        Budget::updateOrCreate(
            [
                'shopping_group_id' => $validated['shopping_group_id'],
                'shopping_type_id' => $validated['shopping_type_id'],
            ],
            ['amount' => $validated['amount']]
        );

        return redirect()->route('plan.index');
    }

    public function destroyBudget(Budget $budget): RedirectResponse
    {
        $this->assertGroupAccess((int) $budget->shopping_group_id);
        $budget->delete();

        return redirect()->route('plan.index');
    }

    public function storeIncome(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,fortnightly,monthly,quarterly,half-yearly,yearly',
            'next_date' => 'required|date',
            'is_active' => 'required|boolean',
            'shopping_group_id' => 'required|exists:shopping_groups,id',
        ]);

        $this->assertGroupAccess((int) $validated['shopping_group_id']);

        $request->user()->incomes()->create($validated);

        return redirect()->route('plan.index');
    }

    public function updateIncome(Request $request, Income $income): RedirectResponse
    {
        $this->assertGroupAccess((int) $income->shopping_group_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,fortnightly,monthly,quarterly,half-yearly,yearly',
            'next_date' => 'required|date',
            'is_active' => 'required|boolean',
            'shopping_group_id' => 'required|exists:shopping_groups,id',
        ]);

        $this->assertGroupAccess((int) $validated['shopping_group_id']);
        $income->update($validated);

        return redirect()->route('plan.index');
    }

    public function destroyIncome(Income $income): RedirectResponse
    {
        $this->assertGroupAccess((int) $income->shopping_group_id);
        $income->delete();

        return redirect()->route('plan.index');
    }

    private function assertGroupAccess(int $groupId): void
    {
        $allowed = Auth::user()->shoppingGroupIds();

        abort_unless(in_array($groupId, $allowed, true), 403);
    }
}
