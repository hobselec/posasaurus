<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class JournalController extends Controller
{
    public function open(Request $request)
    {
        DB::insert("INSERT INTO log(action, drawer_balance) VALUES('open', $request->amount)");

        return response()->json(['status' => true]);

    }
}
