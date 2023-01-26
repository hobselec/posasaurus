<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Cache;

use App\Helpers\BillingHelper;
use App\Models\Customer;

class CacheCustomerBalancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:cache-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update customer balances in cache';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $customer = Customer::with(['jobs', 'debts', 'payments', 'returns'])->where('active', true)->get();

        $results = $customer->map(function($c) {

            $debts = $c->debts->count() > 0 ? $c->debts[0]->sum_total : 0;
            $payments = $c->payments->count() > 0 ? $c->payments[0]->sum_total : 0;
            $returns = $c->returns->count() > 0 ? $c->returns[0]->sum_total : 0;

            $obj = ['name' => $c->display_name, 'id' => $c->id, 'rawBalance' => ($debts - $payments - $returns),
                'balance' => number_format(abs($debts - $payments - $returns), 2),
                'print_statement' => $c->print_statement, 'jobs' => $c->jobs];

            return $obj;
        });

        $results = $results->sortBy('name')->values();

        Cache::put('balances', $results->toArray());

        return Command::SUCCESS;
    }
}
