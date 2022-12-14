<?php

namespace app\Helpers;

use app\Models\Ticket;
use Config;

class TicketHelper {


    public static function computeTotals(Ticket &$ticket)
    {
        
        $ticket->subtotal = $ticket->items->sum('amount') - $ticket->discount;
        $ticket->tax = round($ticket->subtotal * Config::get('pos.sales_tax'), 2);

        
        $ticket->total = round($ticket->subtotal + $ticket->tax,2);
      // $ticket->subtotal = number_format($ticket->subtotal, 2);
       // $ticket->tax = number_format($ticket->tax, 2);

        //return $ticket;
    }
    
}
