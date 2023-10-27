<?php

namespace App\Http\Controllers;

use App\Models\ShoppingGroup;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShoppingGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $shopping_group = User::with(['shopping_groups'])->find(Auth::id())->shopping_groups;

        return Inertia::render('Shopping/ShoppingGroups/Index', [
            
            'shopping_groups' => $shopping_group
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string'
           
        ]);

        $user_id = Auth::id();

 
        $shopping_group = ShoppingGroup::create([
            'name' => $request->name,
            'user_id' => $user_id
        ]);

        $user = User::find([$user_id]);
        $shopping_group->users()->attach($user);


 
        return redirect(route('shopping_groups.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ShoppingGroupController $shoppingGroupController)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShoppingGroupController $shoppingGroupController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShoppingGroupController $shoppingGroupController)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShoppingGroupController $shoppingGroupController)
    {
        //
    }
}
