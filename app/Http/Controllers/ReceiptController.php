<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\User;
use App\Models\ShoppingType;
use App\Models\PaymentMethod;
use App\Models\Shop;
use App\Models\ShoppingGroup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {

        $users = User::with(['shopping_groups', 'payment_methods'])->where('id',Auth::user()->id)->get();
        $userShopingGroupsIds = [];
        $userPaymentMethodsIds= [];

        foreach($users as $user){
            $shoppingGroups = $user->shopping_groups;
            foreach($shoppingGroups as $shoppingGroup){
                $userShopingGroupsIds[] = $shoppingGroup->id;
            }

            $paymentMethods = $user->payment_methods;
            foreach($paymentMethods as $paymentMethod){
                $userPaymentMethodsIds[] = $paymentMethod->id;
            }

        }
        return Inertia::render('Shopping/Receipts/Index', [
            
            'receipts' => Receipt::with(['user','shopping_type', 'payment_method', 'shop', 'shopping_group'])->whereIn('shopping_group_id', $userShopingGroupsIds)->orderBy('date', 'desc')->get(),
            'shopping_types' => ShoppingType::all(),
            'payment_methods' => $paymentMethods ?? [],
            'shops' => Shop::all(),
            'shopping_groups' => $shoppingGroups ?? []

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

        $validated = $request->validate([
            'shopping_type_id' => 'required|numeric',
            'payment_method_id' => 'required|numeric',
            'shop_id' => 'required|numeric',
            'price' => 'required|numeric',
            'note' => 'nullable|string',
            'date' => 'date',
            'shopping_group_id' => 'required|numeric'
        ]);

        $receipt->update($validated);

        return redirect(route('receipts.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receipt $receipt)
    {
        //
    }
}
