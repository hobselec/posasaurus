<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Ticket;

use Carbon\Carbon;

class JournalController extends Controller
{
    public function open(Request $request)
    {
        DB::insert("INSERT INTO log(action, drawer_balance) VALUES('open', $request->amount)");

        return response()->json(['status' => true]);

    }

    public function close(Request $request)
    {
        $startOfDay = Carbon::now()->startOfDay();

        $tickets = Ticket::where('date', '>=', $startOfDay)
                        ->where([['payment_type', '!=', 'VOID'], ['payment_type', '!=', 'discount']])
                        ->whereNotNull('payment_type')
                        ->with(['customers','job'])
                        ->get();



        return response()->json(['status' => true]);

    }
}
