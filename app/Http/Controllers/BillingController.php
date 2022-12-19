<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

use App\Models\Customer;
use App\Models\Ticket;

use Carbon\Carbon;
use DB;
use Config;


class BillingController extends Controller
{
    public function list(Request $request)
    {
        $endDate = Carbon::parse($request->endDate)->endOfDay();

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

            $obj = ['name' => $c->display_name, 'id' => $c->id,
                'balance' => number_format($debts - $payments - $returns, 2),
                'print_statement' => $c->print_statement];

            //$c->use_company ? $obj['name'] = $c->company : $obj['name'] = $c->last_name . ', ' . $c->first_name;

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

    /**
     * get tickets for a customer or all
     * 
     * Request $request ['transaction_type' => string of "all|payments|returns|paid_transactions|charges|voids", 'id' => customer id (optional), 'start' => date, 'end' => date]
     */
    public function customer(Request $request) {

//     ?transaction_type=${transactionType}&start_date=${startDate}&end_date=${endDate}&sort_type=${sort_type}`
        $start = $request->start_date;
        $end = Carbon::parse($request->end_date)->endOfDay();

        $type = $request->transaction_type;


         //['payment_type', 'not like', 'payment_%'],
        $where = [['date', '>=', $start], ['date', '<=', $end]];

        if($request->id != '')
            $where[] = ['customer_id', $request->id];

        switch($request->transaction_type) {
            case 'all':
                $where[] = ['payment_type', '!=', 'VOID'];
                break;
            case 'payments':
               $where[] = ['payment_type', 'like', 'payment_%'];
               $where[] = ['payment_type', '!=', 'VOID'];
               break;
            case 'returns':
                $where[] = ['refund', true];
                $where[] = ['payment_type', '!=', 'VOID']; 
                break;
            case 'paid_transactions':
               $where[] = ['payment_type', 'not like', 'acct'];
               $where[] = ['payment_type', '!=', 'account'];
               $where[] = ['payment_type', '!=', 'VOID'];
                break;
            case 'charges':
                $where[] = ['payment_type', 'account'];
                $where[] = ['payment_type', '!=', 'VOID']; 
                break; 
            case 'voids':
                $where[] = ['payment_type', 'VOID'];
                break;
                    
        }
        //dd($where);

        $tickets = Ticket::where($where)
                            ->whereNotNull('payment_type')
                            ->with(['customer','job','items'])
                            ->get();

        if($tickets->count() == 0)
        $customer = Customer::where('id', $request->id)->first();
        else
        {
            $customer = $tickets[0]->customer;
            $jobs = $tickets->map(function($item) {
                    return $item->job;
            });
            $jobs = $jobs->filter(fn($item) => $item);
        }

        return response()->json(['tickets' => $tickets, 'customer' => $customer, 'jobs' => $jobs ?? []]);
    }

    public function getTicket(Request $request)
    {
        $where = [['display_id', $request->displayId]];
        if($request->limit_customer_id != '')
            $where[] = ['customer_id', $request->limit_customer_id];

        $ticket = Ticket::where($where)->with('items')->first();

        if(!$ticket)
            abort(404);

        return response()->json(['ticket' => $ticket]);
    }

    /** 
     * view statement
     * 
     * @param Request $request ['id' => integer, 'startDate' => date, 'endDate' => date]
     */
    public function statement(Request $request)
    {
        $customerId = $request->id;

        $startDate = Carbon::parse($request->startDate);
        $endDate = Carbon::parse($request->endDate)->endOfDay();


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
            ->where('id', $customerId)
            ->first();
        $customerData = $customer;

        $debts = $customer->debts->count() > 0 ? $customer->debts[0]->sum_total : 0;
        $payments = $customer->payments->count() > 0 ? $customer->payments[0]->sum_total : 0;
        $returns = $customer->returns->count() > 0 ? $customer->returns[0]->sum_total : 0;

        $curBalance = number_format($debts - $payments - $returns, 2);
              
   

        // get balance forward
        $startDateYmd = $startDate->format('Y-m-d 00:00:00'); 
        $result = DB::select("SELECT * FROM ticket WHERE customer_id=$customerId AND date < '$startDateYmd' AND payment_type != 'VOID' ORDER BY DATE ASC");

        //$item_lines = '';

        // || $row->payment_type='acct_cash' || $row->payment_type='acct_check'

        $customer = (object) ['debts'=>0, 'credits'=>0, 
                            'date' => $startDate->format('m/d/Y'), 
                            'forwardBalance' => 0,
                            'curBalance' => $curBalance, 
                            'curTickets' => [], 
                            'balanceForwardDate' => $startDate->format('m/d/Y'),
                            'curTickets' => []
                            ];

        for($i = 0; $i < count($result); $i++)
        {
            $row = $result[$i];
 

            if($row->payment_type == 'check' && $row->refund) // returned checks are not a credit
                continue;
            
            if(($row->payment_type == 'acct' && !$row->refund) || $row->payment_type == 'svc_charge' || $row->payment_type == 'acct_cash' || $row->payment_type == 'acct_check')
            {

                $customer->debts += $row->total;
            }
            else if(substr($row->payment_type, 0, 8) == 'payment_' || ($row->refund && $row->payment_type != 'cash' && $row->payment_type != 'cc') || $row->payment_type == 'discount')
            {
                $customer->credits += $row->total;
            }
		
	    }

        $customer->forwardBalance = number_format($customer->debts - $customer->credits, 2);

        // now look at current period

        $date_limits = "AND ticket.date < '$endDate' AND ticket.date >= '$startDateYmd'";
	    $q2 = "SELECT ticket.*, UNIX_TIMESTAMP(ticket.date) AS ts, customer_jobs.name AS job_name FROM ticket LEFT JOIN customer_jobs ON ticket.job_id=customer_jobs.id WHERE ticket.customer_id=$customerId $date_limits AND payment_type != 'VOID' AND (payment_type = 'ACCT' OR payment_type LIKE 'PAYMENT_%' OR payment_type LIKE 'svc_charge' OR payment_type LIKE 'discount'  OR payment_type='acct_cash' OR payment_type='acct_check') ORDER BY ticket.date ASC";

	    $result = DB::select($q2);


        for($i = 0; $i < count($result); $i++)
        {
            $row = $result[$i];
    
    
            if(substr($row->payment_type, 0, 8) == 'payment_' || ($row->refund && $row->payment_type != 'cash') || $row->payment_type == 'discount' )
                $customer->credits += $row->total;
            else if(($row->payment_type == 'acct' && !$row->refund) || $row->payment_type == 'svc_charge' || $row->payment_type == 'acct_cash' || $row->payment_type == 'acct_check')
            {
                // only returns and payments can be applied to the running balance if occuring after the end date
                //if($row->ts > $timestamp)
                //	continue;
        
                $customer->debts += $row->total;
            
            }
    
            if(substr($row->payment_type, 0, 8)  == 'payment_')
            {
                $transaction_type = 'PMT #' . $row->display_id;
                $total = -1 * $row->total;
            } else if($row->payment_type == 'svc_charge')
            {
                $transaction_type = "SVC CHG #" . $row->display_id;
                $total = $row->total;
            } else if($row->payment_type == 'discount')
            {
                $transaction_type = "DISCOUNT #" . $row->display_id;
                $total = -1 * $row->total;
            }
            else if($row->payment_type == 'acct_cash' || $row->payment_type == 'acct_check')
            {
                $parts = explode("_", $row->payment_type);
            
                $transaction_type = strtoupper($parts[1]) . " REF #" . $row->display_id;
                $total = -1 * $row->total;
            }
            else
            {
                $transaction_type = "INV #" . $row->display_id;
                $total = $row->total;
            }
    
            if($row->refund)
                $total = -1 * $row->total; // mark negative for return/refund
    
            $row->job_name != '' ? $job_name = "- " . $row->job_name : $job_name = '';
    
            $customer->curTickets[] = (object) ['date' => Carbon::parse($row->date)->format('m/d/Y'), 
                            'type' => $transaction_type . ' ' . $job_name, 
                            'total' => number_format($total, 2),
                            'curBalance' => number_format( $customer->debts - $customer->credits, 2)];

        }

        $posConfig = Config::get('pos');

        $statement = Blade::render("@include('layouts.statement')", 
                                    ['statement' => $customer, 
                                    'config' => $posConfig,
                                    'customer' => $customerData]);

        return response()->json(['html' => $statement]);
    }
}
