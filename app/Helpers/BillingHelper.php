<?php

namespace app\Helpers;

use App\Models\Customer;

use Carbon\Carbon;

use Config;

class BillingHelper {

    /**
     * Calculate customer's balance
     * 
     * @param int $customerId
     * @return float
     */
    public static function getCustomerBalance(int $customerId)
    {

        $c = Customer::with(['debts','payments','returns'])->where('id', $customerId)->first();

        $debts = $c->debts->count() > 0 ? $c->debts[0]->sum_total : 0;
        $payments = $c->payments->count() > 0 ? $c->payments[0]->sum_total : 0;
        $returns = $c->returns->count() > 0 ? $c->returns[0]->sum_total : 0;

        return round($debts - $payments - $returns, 2);
    }

    public static function getCustomerBalanceData(int $customerId) : array
    {
        $c = Customer::with(['debts','payments','returns','jobs'])->where('id', $customerId)->first();


        $debts = $c->debts->count() > 0 ? $c->debts[0]->sum_total : 0;
            $payments = $c->payments->count() > 0 ? $c->payments[0]->sum_total : 0;
            $returns = $c->returns->count() > 0 ? $c->returns[0]->sum_total : 0;

        $obj = ['name' => $c->display_name, 'id' => $c->id, 'rawBalance' => ($debts - $payments - $returns),
                'balance' => number_format(abs($debts - $payments - $returns), 2),
                'print_statement' => $c->print_statement, 'jobs' => $c->jobs];

        return $obj;
    }
}