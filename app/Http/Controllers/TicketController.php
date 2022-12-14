<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;

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

        $taxExempt = false;

        if($ticket->customer)
        {
            if($ticket->customer->tax_exempt)
                $taxExempt = true;
        }

        $ticket->discount = $discount;
        $ticket->resale = $resale;
        $ticket->freight = $freight;
        $ticket->labor = $labor;

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


        TicketHelper::computeTotals($ticket);
        $ticket->save();

        return response()->json(['status'=>true]);
    }

}
