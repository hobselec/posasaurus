<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\AdminController;

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

Route::middleware('auth')->get('/', function () {
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
Route::middleware(['auth','actionLog'])->group(function () {
    Route::get('/ticket', [TicketController::class, 'getOpenTickets']);
    Route::get('/ticket/{id}', [TicketController::class, 'loadTicket']);
    Route::put('/ticket/add-item', [TicketController::class, 'addItemToTicket']);
    Route::put('/ticket/set-customer', [TicketController::class, 'setTicketCustomer']);
    Route::put('/ticket/set-options', [TicketController::class, 'setTicketOptions']);
    Route::put('/ticket/item-description', [TicketController::class, 'addItemDescription']);
    
    Route::post('/ticket/submit', [TicketController::class, 'submitTicket']);
    Route::delete('/ticket/void/{id}', [TicketController::class, 'voidTicket']);
    Route::put('/ticket/item', [TicketController::class, 'modifyItem']);
    Route::delete('/ticket/item', [TicketController::class, 'deleteItem']);

    Route::post('/journal/open', [JournalController::class, 'open']);
    Route::post('/journal/close', [JournalController::class, 'close']);

    Route::get('/customer/list', [CustomerController::class, 'getCustomers']);
    Route::get('/customer/search', [CustomerController::class, 'searchCustomer']);
    Route::get('/customer/{id}', [CustomerController::class, 'getCustomer']);
    Route::get('/customer/jobs/{id}', [CustomerController::class, 'getCustomerJobs']);
    Route::post('/customer/job', [CustomerController::class, 'saveJob']);

    Route::post('/customer', [CustomerController::class, 'save']);

    Route::get('/test/mail', [TestController::class, 'mail']);

    Route::get('/billing/list/{type}', [BillingController::class, 'list']);
    Route::get('/billing/customer/{id?}', [BillingController::class, 'customer']);
    Route::get('/billing/ticket/{displayId}', [BillingController::class, 'getTicket']);
    Route::get('/billing/statement/{id?}', [BillingController::class, 'statement']);
    Route::get('/billing/print-statement/{id}', [BillingController::class, 'printStatement']);
    Route::get('/billing/print-invoice/{id}', [BillingController::class, 'printInvoice']);
    Route::get('/billing/email-invoice/{id}', [BillingController::class, 'emailInvoice']);
    Route::post('/billing/print-statements', [BillingController::class, 'printStatements']);
    Route::get('/billing/aging-report', [BillingController::class, 'agingReport']);
    Route::post('/payment', [BillingController::class, 'savePayment']);
    Route::post('/billing/adjustment', [BillingController::class, 'addAdjustment']);

    Route::get('/catalog/search/{term}', [CatalogController::class, 'search']);
    Route::put('/catalog/item', [CatalogController::class, 'editItem']);
    Route::post('/catalog/item', [CatalogController::class, 'addItem']);

    Route::get('/admin/sales-tax', [AdminController::class, 'getSalesTax']);
    Route::get('/admin/', [AdminController::class, 'home']);
    

});

Route::middleware('auth')->get('home', function() {
    return view('home')->with(['users' => App\Models\User::all()]);
});

// these are for breeze I think, which was conflicting with Fortify's 2FA
//require __DIR__.'/auth.php';
