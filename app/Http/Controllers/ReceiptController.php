<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\ShoppingType;
use App\Models\PaymentMethod;
use App\Models\Shop;
use App\Models\ShoppingGroup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {

        return Inertia::render('Shopping/Receipts/Index', [
            
            'receipts' => Receipt::with(['user','shopping_type', 'payment_method', 'shop'])->latest()->get(),
            'shopping_types' => ShoppingType::all(),
            'payment_methods' => PaymentMethod::all(),
            'shops' => Shop::all(),
            'shopping_groups' => ShoppingGroup::all()

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shopping_type_id' => 'required|numeric',
            'payment_method_id' => 'required|numeric',
            'shop_id' => 'required|numeric',
            'price' => 'required|numeric',
            'note' => 'nullable|string',
            'date' => 'date',
            'shopping_group_id' => 'required|numeric'
        ]);
 
        $request->user()->receipts()->create($validated);
 
        return redirect(route('receipts.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receipt $receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receipt $receipt)
    {
        //
    }
}
