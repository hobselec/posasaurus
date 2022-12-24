<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\Ticket;

use App\Helpers\StatementHelper;

use Carbon\Carbon;
use DB;
use Config;
use Dompdf\Dompdf;
use App\Jobs\PrintStatements;


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
     * @param Request $request ['id' => integer (customer.id), 'startDate' => date, 'endDate' => date]
     */
    public function statement(Request $request)
    {
        $customerId = $request->id;

        $startDate = Carbon::parse($request->startDate);
        $endDate = Carbon::parse($request->endDate)->endOfDay();

        $statementData = StatementHelper::getStatement($customerId, $startDate, $endDate, false);

        return response()->json(['html' => $statementData->statement]);
    }

    /**
     * print single statement
     * 
     * returns a pdf
     * 
     * @param Request $request ['id' => customer id, 'startDate' => date, 'endDate' => date, 'printTickets' => boolean]
     * @return \Illuminate\Http\Response stream
     */
    public function printStatement(Request $request)
    {
        $customerId = $request->id;

        $startDate = Carbon::parse($request->startDate);
        $endDate = Carbon::parse($request->endDate)->endOfDay();

        $statementData = StatementHelper::getStatement($customerId, $startDate, $endDate, $request->printTickets);

        $statementHtml = $statementData->statement;
        foreach($statementData->invoices as $invoice)
            $statementHtml .= $invoice;

        $dompdf = new Dompdf();
        $dompdf->loadHtml($statementHtml);

        $dompdf->render();

        $pdf = $dompdf->output();

        
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
        ,   'Content-type'        => 'application/pdf'
        ,   'Content-Disposition' => 'attachment; filename=statement.pdf'
        ,   'Expires'             => '0'
        ,   'Pragma'              => 'public'
        ];


        $callback = function() use($pdf) {
            $fp = fopen('php://output', 'w');
            fwrite($fp, $pdf);
            fclose($fp);

        };

       return response()->stream($callback, 200, $headers);
        
    }

    /**
     * print invoice
     * 
     * returns a pdf
     * 
     * @param Request $request ['id' => ticket id]
     * @return \Illuminate\Http\Response stream
     */
    public function printInvoice(Request $request)
    {
        $ticket = Ticket::where('id', $request->id)->with('customer')->first();

        $invoices = StatementHelper::getInvoice($ticket->customer->id, [$request->id]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($invoices[0]);

        $dompdf->render();

        $pdf = $dompdf->output();

        
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
        ,   'Content-type'        => 'application/pdf'
        ,   'Content-Disposition' => 'attachment; filename=statement.pdf'
        ,   'Expires'             => '0'
        ,   'Pragma'              => 'public'
        ];


        $callback = function() use($pdf) {
            $fp = fopen('php://output', 'w');
            fwrite($fp, $pdf);
            fclose($fp);

        };

       return response()->stream($callback, 200, $headers);

    }

    /**
     * email invoice
     *
     * 
     * @param Request $request ['id' => ticket id]
     * @return \Illuminate\Http\JsonResponse
     */
    public function emailInvoice(Request $request)
    {

        $ticket = Ticket::where('id', $request->id)->with('customer')->first();

        $invoices = StatementHelper::getInvoice($ticket->customer->id, [$request->id]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($invoices[0]);

        $dompdf->render();

        $pdf = $dompdf->output();

        // todo:
        // send e-mail to $ticket->customer->email
        
    }

     /**
     * print multiple statement
     * 
     * returns a pdf
     * 
     * @param Request $request ['id' => array of customer ids, 'endDate' => date]
     * @return \Illuminate\Http\Response stream
     */
    public function printStatements(Request $request)
    {
        $customers = $request->customers;

        $endDate = Carbon::parse($request->endDate)->endOfDay();

        // todo: multiple accounts need a page break before next customer

        PrintStatements::dispatch(['customers' => $customers, 'endDate' => $endDate]);

        return response()->json(['status' => true]);
    }

    public function agingReport(Request $request)
    {
        $customer = Customer::where('active', true)->get();

        $endDate = Carbon::parse($request->endDate)->endOfDay();
        $showOnlylBalance = $request->showOnlyBalances;

        $days_ago_30 = $endDate->copy()->subDays(30);
        $days_ago_60 = $endDate->copy()->subDays(60);
        $days_ago_90 = $endDate->copy()->subDays(90);
        $days_ago_120 = $endDate->copy()->subDays(120);
        $days_ago_150 = $endDate->copy()->subDays(150);



        return response()->json(['report' => 'test']);
    }
}
