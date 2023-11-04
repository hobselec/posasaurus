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

        // test value
        if($counted_cash == '000')
            $startOfDay = Carbon::parse('2022-11-26');

        $endOfDay = $startOfDay->copy()->endOfDay();

        $tickets = Ticket::where('date', '>=', $startOfDay->format('Y-m-d'))
                        ->where([['payment_type', '!=', 'VOID'], ['payment_type', '!=', 'discount']])
                        ->whereNotNull('payment_type')
                        ->with(['customer','job'])
                        ->get();

        $total_checks = 0;
        $total_cash_payments = 0;
        $total_cash = 0;
        $total_sales = 0;

        $output = '';

        foreach($tickets as $ticket)
        {
            $ticketDate = $ticket->date->format('g:i a');
            $line = $ticket->display_id;

            $ticket->refund ? $refund_determinator = -1 : $refund_determinator = 1;

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
        $overShortCashPrefix = '';
        $overShortChecksPrefix = '';

        $result = DB::select("SELECT * FROM log WHERE date >= '$startOfDay' AND date <= '$endOfDay'");

        if(!$result || count($result) == 0)
        {
            $over_short = 'ERR';
            $drawerBalance = 0;
            
        }
        else
        {
            $log = $result[0];
            $drawerBalance = $log->drawer_balance;
        }

        $calc_balance = (float) ($drawerBalance + $total_cash);

        (float) $over_short = (float) $counted_cash - (float) $calc_balance;
        
        if($over_short > 0)
            $overShortCashPrefix = '+';

        $openingDrawer = number_format($drawerBalance, 2);

        $overShort = number_format($over_short, 2);
        

        $overShortChecks = number_format($request->counted_checks - $request->total_checks, 2);	

        if($overShortChecks > 0)
            $overShortChecksPrefix = '+';


        $totals = (object) ['total_cash' => number_format($total_cash, 2),
                 'total_checks' => number_format($total_checks, 2), 
                 'opening_drawer' => $openingDrawer, 
                'total_sales' => number_format($total_sales, 2), 
                'os_cash' => $overShortCashPrefix . $overShort,
                 'os_checks' => $overShortChecksPrefix . $overShortChecks
            ];

        $report = Blade::render("@include('layouts.closing_journal')", ['date' => $startOfDay->format('m/d/Y g:i a'), 'tickets' => $tickets, 'totals' => $totals]);
        
        $obj = (object) ['message' => $report, 'subject' => 'Closing Journal'];

        Mail::to(Auth::user()->email)->send(new ReceiptEmail($obj));

        return response()->json(['status' => true]);

    }
}
