<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;

use App\Models\Ticket;
use App\Models\CatalogItem;
use App\Models\TransactionItem;

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

        $subtotal = $ticket->items->sum('price');
        $ticket->subtotal = $ticket->items->sum('amount');
        $ticket->tax = $ticket->subtotal * Config::get('pos.sales_tax');
        
        $ticket->total = number_format($ticket->subtotal + $ticket->tax,2);
        $ticket->subtotal = number_format($ticket->subtotal, 2);
        $ticket->tax = number_format($ticket->tax, 2);

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
        }

        // new item or increment qty
        $item = TransactionItem::firstOrNew(['ticket_id' => $request->ticketId, 'catalog_id' => $catalogItem->id]);

        $item->fill(['qty' => $item->qty + 1, 
                    'price' => $catalogItem->price, 'amount' => $catalogItem->price * ($item->qty + 1),
                    'name' => $catalogItem->name, 'product_id' => $catalogItem->product_id, 
                    'catalog_id' => $catalogItem->id]);
        $item->save();

        return response()->json(['status' => true, 'ticket'=>[$ticket] ?? []]);
    }

    public function submitTicket(Request $request)
    {

        $ticket = Ticket::where('id',$request->id)->with(['items'])->first();

        $subtotal = $ticket->items->sum('price');
        $ticket->subtotal = $ticket->items->sum('amount');
        $ticket->tax = $ticket->subtotal * Config::get('pos.sales_tax'); 

        $ticket->total = $ticket->subtotal + $ticket->tax;

        // validate submitted price equals computed price
        $customerTotal = $request->total;
        $customerSubtotal = $request->subtotal;
        $customerTax = $request->tax;

        //if()

        return response()->json(['status'=>true]);
    }

}
