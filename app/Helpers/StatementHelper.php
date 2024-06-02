<?php

namespace app\Helpers;

use App\Models\Customer;
use App\Models\Ticket;

use Carbon\Carbon;
use DB;
use Config;

use Illuminate\Support\Facades\Blade;

class StatementHelper {

    /**
     * get statement
     * 
     * @return object ['statement' => string $statement of html, 'invoices' => array $invoices of html];
     * 
     */
    public static function getStatement(int $customerId, Carbon $startDate, Carbon $endDate, bool $getInvoices) : object
    {

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
    
            $customer->curTickets[] = (object) ['id' => $row->id, 
                            'date' => Carbon::parse($row->date)->format('m/d/Y'), 
                            'type' => $transaction_type . ' ' . $job_name, 
                            'total' => number_format($total, 2),
                            'curBalance' => number_format( $customer->debts - $customer->credits, 2)];

        }

        $posConfig = Config::get('pos');

        $statement = Blade::render("@include('layouts.statement')", 
                                    ['statement' => $customer, 
                                    'config' => $posConfig,
                                    'customer' => $customerData]);

        if($getInvoices)
            $invoices = static::getInvoice($customerData->id, array_column($customer->curTickets, 'id'), true);
        else
            $invoices = [];


        return (object) ['statement' => $statement, 'invoices' => $invoices];
    }

    /**
     * get invoice
     * 
     * @param integer $customerId
     * @param array $invoices (id of ticket.id)
     * @param bool $breakPage used when adding to statement to change html formatting
     * @return array of html invoices
     */
    public static function getInvoice(int $customerId, array $invoices, bool $breakPage = false) : array
    {
        //generate_invoice($ticket_id, $show_heading = 1, $mode = 'simple', $basic_html = 1)
        $posConfig = Config::get('pos');

        $customer = Customer::with(['tickets' => function($q) use($invoices) {
                $q->whereIn('id', $invoices);
            }, 'tickets.job', 'tickets.items'])
            ->where('id', $customerId)
            ->first();

        $customerData = $customer;

        $invoices = [];

        foreach($customer->tickets as $ticket)
        {
            
            $invoices[] = Blade::render("@include('layouts.statementInvoice')", 
            ['ticket' => $ticket,
            'breakPage' => $breakPage,
            'config' => $posConfig,
            'customer' => $customer]);
        }

        return $invoices;

    }


}