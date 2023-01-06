<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use DB;
use Auth;
use App\Mail\ReceiptEmail;
use Illuminate\Support\Facades\Mail;

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
        $counted_checks = $request->checks;
        $counted_cash = $request->cash;

        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = $startOfDay->copy()->endOfDay();

        $tickets = Ticket::where('date', '>=', $startOfDay)
                        ->where([['payment_type', '!=', 'VOID'], ['payment_type', '!=', 'discount']])
                        ->whereNotNull('payment_type')
                        ->with(['customers','job'])
                        ->get();

        $total_checks = 0;
        $total_cash_payments = 0;
        $total_cash = 0;
        $total_sales = 0;

        foreach($tickets as $ticket)
        {
            $ticketDate = $ticket->date->format('n/d/Y g:i a');

            $ticket->refund ? $refundDeterminator = -1 : $refund_Determinator = 1;

            $pparts = explode("_", $ticket->payment_type); // check for payment_

            // if a payment type
            if(count($pparts) > 1 && $pparts[1] != 'REFUND')
            {
                $payment_type = 'PYMT ' . strtoupper($pparts[1]);
            
                // refunds on account: check or cash (e.g. acct_cash)
                if($pparts[0] == 'acct')
                {
                    $payment_type = 'RFND ' . strtoupper($pparts[1]);

                    // this is a refund on the acct, not sure this should negate cash or not, but 
                    // think so, since money must go back to them, and their acct is negated that sum
                    if($pparts[1] == 'cash') // checks don't affect the totals
                        $total_cash -= $ticket->total;

                } else if($pparts[1] == 'cash')
                    $total_cash += $ticket->total;
                else if($pparts[1] == 'check')
                    $total_checks += $ticket->total;
                
                    
            }
            else
            {
            
                switch($ticket->payment_type)
                {
                    case 'cash':
                        $payment_type = 'CASH';
                        $total_cash += $ticket->total * $refund_determinator;
                        break;
                    case 'acct':
                        $payment_type = 'CHRG';
                        break;
                    case 'cc':
                        $payment_type = 'CC';
                        break;
                    case 'check':
                        $payment_type = 'CHCK';
                        $total_checks += $ticket->total; // no R.D. here because checks are only received
                        break;
                    case 'svc_charge':
                        $payment_type = 'SVC CHG';
                        break;
                    case 'discount':
                        $payment_type = 'DISCOUNT';
                        break;
                    case 'VOID':
                        $payment_type = 'VOID'; // the sql query is exluding this type
                        break;
                    case 'payment_cash':
                        $payment_type = 'CASH REFUND';
                        break;
                }
                

                
                if(!$ticket->refund)
                    $total_sales += $ticket->total;
            }
            
            if($ticket->refund)
                $payment_type = $payment_type . ' RFND';


            $line .= ' ' . $payment_type;
            


            $llen = strlen($line);
            $price_len = strlen($ticket->total);
            $total_len = $llen + $price_len;

            for($j = $total_len; $j < 24; $j++)
                $line .= ' ';

            $output .= $line . "$ticket->total\n";
        }

        // COMPARE to opening balance

        $result = $DB::select("SELECT * FROM log WHERE date >= $startOfDay AND date <= $endOfDay");

        if(!$result || count($result) == 0)
            $over_short = 'ERR';
        else {
            $log = $result[0];

            $calc_balance = (float) ($log->drawer_balance + $total_cash);
    
            (float) $over_short = (float) $counted_cash - (float) $calc_balance;
            
            if($over_short > 0)
                $over_shortcash_prefix = '+';
            
            $opening_drawer = $log->drawer_balance;

            $over_short = number_format($over_short, 2);
        }

        $over_short_checks = number_format($request->counted_checks - $request->total_checks, 2);	

        if($over_short_checks > 0)
            $over_shortchecks_prefix = '+';



        $total_sales = number_format($total_sales, 2);
        $total_cash = number_format($total_cash, 2);

        $totals = (object) ['total_cash' => $total_cash, 
                 'total_checks' => $total_checks, 
                 'opening_drawer' => $opening_drawer, 
                'total_sales' => $total_sales, 
                'os_cash' => $over_shortcash_prefix . $over_short,
                 'os_checks' => $over_shortchecks_prefix . $over_short_checks
            ];

dd(Auth::user());
        $report = Blade::render("@include('layouts.closing_journal')", ['tickets' => $tickets, 'totals' => $totals]);
        Mail::to(Auth::user()->email)->send(new ReceiptEmail($report));

        return response()->json(['status' => true]);

    }
}
