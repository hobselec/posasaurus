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
        }
        
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
            'tax' => 'required|in:' . $ticket->tax
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

        return response()->json(['status'=>true]);
    }

    public function voidTicket(Request $request)
    {
        $ticket = Ticket::where('id',$request->id)->with(['items'])->first();

        $ticket->payment_type='VOID';
        $ticket->date = Carbon::now();
        $ticket->save();

        return response()->json(['status' => true]);
    }

    /** 
     * changes of quantity in cart
     * 
     * @param Request $request ['itemId' => integer, 'ticketId' => integer, 'qty' => integer]
     */
    public function modifyItemQty(Request $request) 
    {
        $validated = $request->validate([
            'qty' => 'integer|min:1'
            
        ]);

        //TransactionItem::where('id', $request->itemId)->update(['qty'=>$request->qty, 'amount' => $request->qty * ])
        $result = DB::update("UPDATE transaction_items SET qty=$request->qty, amount=price*$request->qty WHERE id=$request->itemId");
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

}
