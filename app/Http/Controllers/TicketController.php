<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;

use App\Models\Ticket;

class TicketController extends Controller
{
    
    public function getOpenTickets()
    {
        $tickets = DB::select("SELECT ticket.*, CONCAT(customers.last_name, ', ', customers.first_name, ' ', customers.mi) AS customer, customers.first_name, company, use_company FROM ticket LEFT JOIN customers ON customers.id=ticket.customer_id WHERE payment_type IS NULL");


        return response()->json(['tickets' => $tickets]);
    }

    public function loadTicket(Request $request)
    {

        $ticket = Ticket::where('id',$request->id)->with(['items','customer','job'])->first();

        $subtotal = $ticket->items->sum('price');
        $ticket->subtotal = $ticket->items->sum('price');
        $ticket->tax = $ticket->subtotal * Config::get('pos.sales_tax');
        
        $ticket->total = number_format($ticket->subtotal + $ticket->tax,2);
        $ticket->subtotal = number_format($ticket->subtotal, 2);
        $ticket->tax = number_format($ticket->tax, 2);

        return response()->json($ticket);
    }

    public function addItemToTicket(Request $request)
    {

        return response()->json($ticket);
    }
}
