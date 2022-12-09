<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CatalogController;

use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

//Route::get('/dashboard', function () {
  //  return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

/*
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
*/
Route::middleware('auth')->group(function () {
    Route::get('/ticket', [TicketController::class, 'getOpenTickets']);
    Route::get('/ticket/{id}', [TicketController::class, 'loadTicket']);
    Route::put('/ticket/add-item', [TicketController::class, 'addItemToTicket']);
    Route::post('/ticket/submit', [TicketController::class, 'submitTicket']);

    Route::post('/journal/open', [JournalController::class, 'open']);

    Route::get('/customer/list', [CustomerController::class, 'getCustomers']);
    Route::get('/customer/search', [CustomerController::class, 'searchCustomer']);
    Route::get('/customer/{id}', [CustomerController::class, 'getCustomer']);
    Route::get('/customer/jobs/{id}', [CustomerController::class, 'getCustomerJobs']);
    Route::post('/customer/job', [CustomerController::class, 'saveJob']);

    Route::post('/customer', [CustomerController::class, 'save']);

    Route::get('/test/mail', [TestController::class, 'mail']);

    Route::get('/billing/list/{type}', [BillingController::class, 'list']);
    Route::get('/catalog/search/{term}', [CatalogController::class, 'search']);
});

Route::middleware('auth')->get('home', function() {
    return view('home');
});


require __DIR__.'/auth.php';
