<?php

namespace App\Http\Controllers;

use App\Models\DdPayment;
use App\Models\PaymentMethod;
use App\Models\Shop;
use App\Models\ShoppingGroup;
use App\Models\ShoppingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DdPaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $shoppingGroupIds = $user->shoppingGroupIds();

        return Inertia::render('Shopping/DdPayments/Index', [
            'dd_payments' => DdPayment::query()
                ->with(['shoppingGroup', 'paymentMethod', 'shop', 'shoppingType'])
                ->whereIn('shopping_group_id', $shoppingGroupIds ?: [0])
                ->orderBy('name')
                ->get(),
            'shopping_groups' => ShoppingGroup::query()
                ->whereIn('id', $shoppingGroupIds ?: [0])
                ->orderBy('name')
                ->get(),
            'shops' => Shop::orderBy('name')->get(),
            'payment_methods' => PaymentMethod::orderBy('name')->get(),
            'shopping_types' => ShoppingType::orderBy('description')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $this->assertGroupAccess((int) $validated['shopping_group_id']);

        DdPayment::create($validated);

        return redirect()->route('dd_payments.index');
    }

    public function update(Request $request, DdPayment $dd_payment): RedirectResponse
    {
        $this->assertGroupAccess((int) $dd_payment->shopping_group_id);

        $validated = $this->validated($request);
        $this->assertGroupAccess((int) $validated['shopping_group_id']);

        $dd_payment->update($validated);

        return redirect()->route('dd_payments.index');
    }

    public function destroy(DdPayment $dd_payment): RedirectResponse
    {
        $this->assertGroupAccess((int) $dd_payment->shopping_group_id);
        $dd_payment->delete();

        return redirect()->route('dd_payments.index');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'end_date' => 'nullable|date',
            'frequency' => 'required|in:daily,weekly,fortnightly,monthly,quarterly,half-yearly,yearly',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'shopping_group_id' => 'required|exists:shopping_groups,id',
            'shop_id' => 'required|exists:shops,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'shopping_type_id' => 'required|exists:shopping_types,id',
        ]);
    }

    private function assertGroupAccess(int $groupId): void
    {
        abort_unless(in_array($groupId, Auth::user()->shoppingGroupIds(), true), 403);
    }
}
