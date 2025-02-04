<?php

use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShoppingTypeController;
use App\Http\Controllers\ShoppingGroupController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Receipt;
use Inertia\Inertia;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        // 'canRegister' => Route::has('register'),
        // 'laravelVersion' => Application::VERSION,
        // 'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {

    $currentMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
    $currentMonthEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

    $users = User::with(['shopping_groups'])->where('id',Auth::user()->id)->get();
    $userShopingGroupsIds = [];

    foreach($users as $user){
        $shoppingGroups = $user->shopping_groups;
        foreach($shoppingGroups as $shoppingGroup){
            $userShopingGroupsIds[] = $shoppingGroup->id;
        }

    }

    $total = Receipt::whereIn('shopping_group_id', $userShopingGroupsIds)->sum('price');
    return Inertia::render('Dashboard',
[
    'totalByGroup' => Receipt::select('shopping_type_id', \DB::raw('SUM(price) as total_price'))
                    ->with(['shopping_type'])
                    ->groupBy('shopping_type_id')
                    ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
                    ->whereIn('shopping_group_id', $userShopingGroupsIds)
                    ->orderBy('total_price', 'desc')
                    ->get(),
    'total' => $total,
    'start_date' => $currentMonthStart,
    'end_date' => $currentMonthEnd
]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/test', function () {
    return Inertia::render('Public/Home', [
        'laravel' => \Illuminate\Foundation\Application::VERSION,
        'php' => PHP_VERSION,
        'test' => 'Tester'
    ]);
});

Route::resource('receipts', ReceiptController::class)
    ->only(['index', 'store', 'update', 'edit'])
    ->middleware(['auth', 'verified']);

Route::resource('shops', ShopController::class)
    ->only(['index', 'store'])
    ->middleware(['auth', 'verified']);

Route::resource('shopping_types', ShoppingTypeController::class)
    ->only(['index', 'store'])
    ->middleware(['auth', 'verified']);

Route::resource('shopping_groups', ShoppingGroupController::class)
    ->only(['index', 'store'])
    ->middleware(['auth', 'verified']);


Route::resource('payment_methods', PaymentMethodController::class)
    ->only(['index', 'store'])
    ->middleware(['auth', 'verified']);

require __DIR__.'/auth.php';
