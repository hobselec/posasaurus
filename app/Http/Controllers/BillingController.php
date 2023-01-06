<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;

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
            with(['jobs', 'debts'=>function($q) use($endDate) {
            $q->where('date', '<=', $endDate);
            }])
            ->with(['payments'=>function($q) use($endDate) {
                $q->where('date', '<=', $endDate);
            }])
            ->with(['returns'=>function($q) use($endDate) {
                $q->where('date', '<=', $endDate);
            }])
            ->where('active', true)
            ->get();

        $results = $customer->map(function($c) {

            $debts = $c->debts->count() > 0 ? $c->debts[0]->sum_total : 0;
            $payments = $c->payments->count() > 0 ? $c->payments[0]->sum_total : 0;
            $returns = $c->returns->count() > 0 ? $c->returns[0]->sum_total : 0;

            $obj = ['name' => $c->display_name, 'id' => $c->id, 'rawBalance' => ($debts - $payments - $returns),
                'balance' => number_format(abs($debts - $payments - $returns), 2),
                'print_statement' => $c->print_statement, 'jobs' => $c->jobs];

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
            // remove null jobless tickets and get unique
            $jobs = $jobs->unique('name')->filter(fn($item) => $item)->values();
        }

        return response()->json(['tickets' => $tickets, 'customer' => $customer, 'jobs' => $jobs ?? []]);
    }

    /**
     * search single ticket by display_id
     * 
     * 
     */
    public function getTicket(Request $request)
    {
        $where = [['display_id', $request->displayId]];
        if($request->limit_customer_id != '')
            $where[] = ['customer_id', $request->limit_customer_id];

        $ticket = Ticket::where($where)->with(['customer', 'items'])->first();

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
        $report = Cache::get('agingReport');
        $customerTotals = $report['customers'];
        $totals = $report['totals'];

        $results = array_filter($customerTotals, fn($item) => $item->periods[5]->balance > 0);

        $report = Blade::render("@include('layouts.aging')", ['results' => $results, 'totals' => $totals]);

        return response()->json(['report' => $report]);
    }

    /**
     * save payment from payment dialog
     * 
     * @param Request $request ['customer_id' : integer, 'date' => date, 'pay_type' : string , 'amount' : float, 'extra_info' :  string, 'job_id' : integer ]
     * @return \Illuminate\Http\JsonResponse
     */
    public function savePayment(Request $request)
    {

        $amount = $request->amount;
        $extra_info = $request->extra_info;

        $ticket = new Ticket();

        $ticket->save() or abort(500);

        $ticket->display_id = $ticket->id + Config::get('pos.display_id_offset');
        $ticket->customer_id = $request->customerId;
        $ticket->payment_type = "payment_" . $request->payType;
        $ticket->total = $request->amount;

        if($request->payType == 'cc')
            $ticket->cc_trans_no = $request->extraInfo;
        else if($request->payTYpe == 'check')
            $icket->check_no = $request->extraInfo;

        $ticket->date = Carbon::parse($request->date);
        $ticket->job_id = $request->jobId;


        $ticket->save() or abort(500);


        return response()->json(['status' => true]);
    }

     /**
     * add adjustment
     * 
     * cash refund, discount, or service charge
     * 
     * @param Request $request ['type' => string refund|discount|svc_charge, 'customerId' => int, 'format' => cash|check (only for refund), 'jobId' => int (discount only), 'amount' => float ]
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAdjustment(Request $request)
    {
        $validated = $request->validate([
            'type' => Rule::in(['discount','svc_charge','refund']),
            'format' => [Rule::requiredIf($request->type == 'refund'), Rule::in(['cash', 'check'])],
            'amount' => 'required|numeric',
            'customerId' => 'required'
        ]);

        $ticket = new Ticket();
      
        if($request->jobId != '')
            $ticket->job_id = $request->jobId;

        if($request->type == 'discount' || $request->type == 'svc_charge')
            $ticket->payment_type == $request->type;
        else if($request->type == 'refund')
            $ticket->payment_type = 'acct_' . $request->format;
        
        $ticket->date = Carbon::now();
        $ticket->subtotal = $request->amount;
        $ticket->total = $request->amount;
        $ticket->customer_id = $request->customerId;

        $ticket->save();

        $ticket->display_id = $ticket->id + Config::get('pos.display_id_offset');

        $ticket->save();

        return response()->json($ticket);
    }

}
