<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Cache;

use Carbon\Carbon;
use App\Models\Customer;

class AgingReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:aging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates aging report and caches it';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       //$customer = Customer::where('active', true)->get();
       ini_set('max_execution_time', 60);

       $endDate = Carbon::now()->startOfDay();
      //$showOnlylBalance = $request->showOnlyBalances;

       $days_ago_30 = $endDate->copy()->subDays(30);
       $days_ago_60 = $endDate->copy()->subDays(60);
       $days_ago_90 = $endDate->copy()->subDays(90);
       $days_ago_120 = $endDate->copy()->subDays(120);
       $days_ago_150 = $endDate->copy()->subDays(150);
       $days_ago_current = $endDate;

       //$periods = [$days_ago_30, $days_ago_60, $days_ago_90, $days_ago_120, $days_ago_150, $days_ago_current];

        $periods = [[$days_ago_30],
                [$days_ago_60, $days_ago_30],
                [$days_ago_90, $days_ago_60],
                [$days_ago_120, $days_ago_90],
                [$days_ago_150, $days_ago_120],
                []
         ];

       $customers = Customer::where('active', true)
                           ->with(['tickets' =>  function($q) {
                               $q->where([['payment_type', '!=', 'cash'],['payment_type', '!=', 'VOID'],['payment_type', '!=', 'cc']]);
                            }])
                           ->cursor();
       
       $customerTotals = [];
       
        $periodTotals = [0,0,0,0,0,0];
                           
       foreach($customers as $customer)
       {
           $data = (object) ['name' => $customer->display_name, 'periods' => []];

           foreach($periods as $index=>$period)
           {
               $periodData = (object) ['debts' => 0, 'credits' => 0, 'balance' => 0];
              

               $customer->load(['tickets' => function($q) use($period) {

                    if(count($period) > 1)
                        $q->whereBetween('date', $period);
                    else if(count($period) > 0)
                        $q->where('date', '>=', $period);
                    
               }]);

               foreach($customer->tickets as $ticket)
               {
                /*
                   if(!($ticket->date >= $period))
                       continue;
                    if($index > 0 && $index < 5)
                    {
                        if(!($ticket->date < $period[$index -1]))
                            continue;
                    }
                    */

                   if(substr($ticket->payment_type, 0, 8) == 'payment_' || $ticket->refund || $ticket->payment_type == 'discount')
                       $periodData->credits += $ticket->total;
                   else if(($ticket->payment_type == 'acct' && !$ticket->refund) || $ticket->payment_type == 'svc_charge' || $ticket->payment_type == 'acct_cash' || $ticket->payment_type == 'acct_check')
                       $periodData->debts += $ticket->total;

               }

               $periodData->balance = round($periodData->debts - $periodData->credits, 2);
               $data->periods[] = $periodData;

               $periodTotals[$index] += $periodData->balance;
           }
           
           $customerTotals[] = $data;
       }

       Cache::put('agingReport', ['customers' => $customerTotals, 'totals' => $periodTotals]);

        return Command::SUCCESS;
    }
}
