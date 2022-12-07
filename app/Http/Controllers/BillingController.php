<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;

class BillingController extends Controller
{
    public function list(Request $request)
    {
        $endDate = $request->endDate;

        $customer = Customer::
            with(['debts'=>function($q) use($endDate) {
            $q->where('date', '<=', $endDate);
            }])
            ->with(['payments'=>function($q) use($endDate) {
                $q->where('date', '<=', $endDate);
            }])
            ->with(['returns'=>function($q) use($endDate) {
                $q->where('date', '<=', $endDate);
            }])
            ->get();

        $results = $customer->map(function($c) {

            $debts = $c->debts->count() > 0 ? $c->debts[0]->sum_total : 0;
            $payments = $c->payments->count() > 0 ? $c->payments[0]->sum_total : 0;
            $returns = $c->returns->count() > 0 ? $c->returns[0]->sum_total : 0;

            $obj = ['name' => '', 'id' => $c->id,
                'balance' => number_format($debts - $payments - $returns, 2),
                'print_statement' => $c->print_statement];

            $c->use_company ? $obj['name'] = $c->company : $obj['name'] = $c->last_name . ', ' . $c->first_name;

            return $obj;
        });

        if($request->type == 'balances')
        {
            $results = $results->filter(function($item) {
                if($item['balance'] != 0)
                    return $item;
            });
        }

        $results = $results->sortBy('name')->values();

        return response()->json($results);
    }
}
