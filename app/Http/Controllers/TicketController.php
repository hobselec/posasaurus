<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;
use Carbon\Carbon;

use App\Models\Ticket;
use App\Models\CatalogItem;
use App\Models\TransactionItem;

use App\Helpers\TicketHelper;
use App\Helpers\StatementHelper;
use App\Helpers\BillingHelper;

use App\Mail\ReceiptEmail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\UpdateAccount;

class TicketController extends Controller
{
    
    public function getOpenTickets()
    {
        //$tickets = DB::select("SELECT ticket.*, CONCAT(customers.last_name, ', ', customers.first_name, ' ', customers.mi) AS customer, customers.first_name, company, use_company FROM ticket LEFT JOIN customers ON customers.id=ticket.customer_id WHERE payment_type IS NULL");
        $tickets = Ticket::whereNull('payment_type')->with('customer')->get();

        return response()->json(['tickets' => $tickets]);
    }

    public function loadTicket(Request $request)
    {

        $ticket = Ticket::where('id',$request->id)->with(['items','customer','job'])->first();

      //  $ticket->subtotal = $ticket->items->sum('amount');
       // $ticket->tax = $ticket->subtotal * Config::get('pos.sales_tax');
        
      //  $ticket->total = number_format($ticket->subtotal + $ticket->tax,2);
      //  $ticket->subtotal = number_format($ticket->subtotal, 2);
     //   $ticket->tax = number_format($ticket->tax, 2);
        TicketHelper::computeTotals($ticket);

        return response()->json($ticket);
    }

    public function addItemToTicket(Request $request)
    {
        $catalogItem = CatalogItem::where('id', $request->itemId)->first();

        if($request->ticketId == '')
        {
            $ticket = new Ticket();
            
            $ticket->user_id = auth()->user()->id;
            
            $ticket->save();
            $request->ticketId = $ticket->id;

            $ticket->display_id = $ticket->id + Config::get('pos.display_id_offset');
            $ticket->customer_id = Config::get('pos.default_customer_id');
            $ticket->save();
        } else
            $ticket = Ticket::where('id', $request->ticketId)->first();

        // new item or increment qty
        $item = TransactionItem::firstOrNew(['ticket_id' => $request->ticketId, 'catalog_id' => $catalogItem->id]);

        $item->fill(['qty' => $item->qty + 1, 
                    'price' => $catalogItem->price, 'amount' => $catalogItem->price * ($item->qty + 1),
                    'name' => $catalogItem->name, 'product_id' => $catalogItem->product_id, 
                    'catalog_id' => $catalogItem->id]);
        $item->save();

        TicketHelper::computeTotals($ticket);
        $ticket->save();

        $ticket->load('items');

        return response()->json(['status' => true, 'ticket'=>$ticket]);
    }

    public function setTicketCustomer(Request $request)
    {
        $ticket = Ticket::where('id',$request->id)->first();

        if($request->has('customer_id'))
            $ticket->customer_id = $request->customer_id;
        if($request->has('job_id'))
        {
            $request->job_id == '' ? $jobId = null : $jobId = $request->job_id;

            $ticket->job_id = $jobId;
        } else
            $ticket->job_id = null;
        
        // recompute tax if tax exempt
        TicketHelper::computeTotals($ticket);
        
        $ticket->save();
        $ticket->load('customer');

        return response()->json(['ticket' => $ticket]);

    }

    public function setTicketOptions(Request $request)
    {
        $ticket = Ticket::where('id',$request->id)->with('customer')->first();

        $discount = $request->discount ?? 0;
        $resale = $request->resale ?? 0;
        $freight = $request->freight ?? 0;
        $labor = $request->labor ?? 0;

        $ticket->discount = $discount;
        $ticket->resale = $resale;
        $ticket->freight = $freight;
        $ticket->labor = $labor;
        $ticket->refund = $request->refund;
        $ticket->recv_by = $request->recv_by;

        TicketHelper::computeTotals($ticket);
        $ticket->save();

        return response()->json(['ticket' => $ticket]);

    }

    public function submitTicket(Request $request)
    {

        $ticket = Ticket::where('id',$request->id)->with(['items'])->first();



        // validate submitted price equals computed price
        $customerTotal = $request->total;
        $customerSubtotal = $request->subtotal;
        $customerTax = $request->tax;

        // not sure this is needed
        //TicketHelper::computeTotals($ticket);

        $validated = $request->validate([
            'total' => 'required|in:' . $ticket->total,
            'subtotal' => 'required|in:' . $ticket->subtotal,
            'tax' => 'required|in:' . $ticket->tax,
            'payment_type' => 'required'
        ]);

        $ticket->payment_type = $request->payment_type;
        $ticket->date = Carbon::now();

        if($ticket->payment_type == 'cc')
            $ticket->cc_trans_no = $request->cc_trans_no;
        if($ticket->payment_type == 'check')
            $ticket->check_no = $request->check_no;

        $ticket->save();

        // should make this a transaction
        $ticket->items->each(function($item) {

            $item->catalog->decrement('qty', $item->qty);
        
        });

        if($ticket->customer->email != '')
        {
            $invoices = StatementHelper::getInvoice($ticket->customer->id, [$ticket->id]);
            $obj = (object) ['message' => $invoices[0], 'subject' => 'Invoice'];
            Mail::to($ticket->customer->email)->send(new ReceiptEmail($obj));
        }

        if($ticket->payment_type == 'acct')
            UpdateAccount::dispatch(['customerId' => $ticket->customer_id]);

        return response()->json(['status'=>true]);
    }

    public function voidTicket(Request $request)
    {
        $ticket = Ticket::where('id',$request->id)->with(['items'])->first();

        if($ticket->payment_type == 'VOID')
            return response()->json(['status' => false]);

        $ticket->payment_type='VOID';
        $ticket->user_id = auth()->user()->id;

        $ticket->save() or abort(500);

        foreach($ticket->items as $item)
            $item->catalog->decrement('qty', $item->qty);

        $curBalance = BillingHelper::getCustomerBalance($ticket->customer_id);

        UpdateAccount::dispatch(['customerId' => $ticket->customer_id]);

        return response()->json(['status' => true, 'balance' => $curBalance]);
    }

    /** 
     * changes of quantity in cart
     * 
     * either qty or price should be given
     * 
     * @param Request $request ['itemId' => integer, 'ticketId' => integer, 'qty' => integer, 'price' => float]
     */
    public function modifyItem(Request $request) 
    {
        $validated = $request->validate([
            'qty' => 'sometimes|required_without:price|integer|min:1',
            'price' => 'sometimes|required_without:qty|numeric|min:0'
            
        ]);

        if($request->qty != '')
            $result = DB::update("UPDATE transaction_items SET qty=$request->qty, amount=price*$request->qty WHERE id=$request->itemId");
        else
            $result = DB::update("UPDATE transaction_items SET price=$request->price, amount=$request->price*qty WHERE id=$request->itemId");

        if(!$result)
            abort(500);

        $ticket = Ticket::where('id', $request->ticketId)->with(['items'])->first();

        TicketHelper::computeTotals($ticket);
        $ticket->save();

        return response()->json(['ticket' => $ticket]);
    }

    /**
     * delete item from cart
     * 
     * @param Request $request ['itemId' => integer, 'ticketId' => integer ]
     */
    public function deleteItem(Request $request) 
    {
        TransactionItem::where('id', $request->itemId)->delete();

        $ticket = Ticket::where('id' ,$request->ticketId)->with(['items'])->first();
        
        TicketHelper::computeTotals($ticket);
        $ticket->save();

        return response()->json(['ticket' => $ticket]);
    }

    /**
     * add description to item
     */
    public function addItemDescription(Request $request)
    {
        $item = TransactionItem::where('id', $request->itemId)->first();

        $item->notes = $request->description;
        $item->save() or abort(500);

        return response()->json();
    }

}
