<?php

namespace App\Http\Controllers;

use App\Models\DdPayment;
use App\Models\ShoppingGroup;
use App\Models\Shop;
use App\Models\PaymentMethod;
use App\Models\ShoppingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DdPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $shoppingGroupIds = $user->shopping_groups()->pluck('shopping_groups.id');
        $ddPayments = DdPayment::with(['shoppingGroup', 'paymentMethod', 'shop', 'shoppingType'])->whereIn('shopping_group_id', $shoppingGroupIds)->get();
        $shoppingGroups = ShoppingGroup::whereIn('id', $shoppingGroupIds)->get();
        $shops = Shop::all();
        $paymentMethods = PaymentMethod::all();
        $shoppingTypes = ShoppingType::all();

        return inertia('Shopping/DdPayments/Index', [
            'dd_payments' => $ddPayments,
            'shopping_groups' => $shoppingGroups,
            'shops' => $shops,
            'payment_methods' => $paymentMethods,
            'shopping_types' => $shoppingTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'end_date' => 'nullable|date',
            'frequency' => 'nullable|in:daily,weekly,fortnightly,monthly,quarterly,half-yearly,yearly',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'shopping_group_id' => 'required|exists:shopping_groups,id',
            'shop_id' => 'required|exists:shops,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'shopping_type_id' => 'required|exists:shopping_types,id',
        ]);

        $ddPayment = DdPayment::create($validatedData);
        return redirect()->route('dd_payments.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ddPayment = DdPayment::findOrFail($id);
        return response()->json($ddPayment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'payment_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date',
            'frequency' => 'nullable|in:daily,weekly,fortnightly,monthly,quarterly,half-yearly,yearly',
            'price' => 'sometimes|required|numeric|min:0',
            'is_active' => 'sometimes|required|boolean',
            'shopping_group_id' => 'sometimes|required|exists:shopping_groups,id',
            'shop_id' => 'sometimes|required|exists:shops,id',
            'payment_method_id' => 'sometimes|required|exists:payment_methods,id',
            'shopping_type_id' => 'sometimes|required|exists:shopping_types,id',
        ]);

        $ddPayment = DdPayment::findOrFail($id);
        $ddPayment->update($validatedData);
        return response()->json($ddPayment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ddPayment = DdPayment::findOrFail($id);
        $ddPayment->delete(); // Soft delete the record
        return response()->json(null, 204);
    }
}
