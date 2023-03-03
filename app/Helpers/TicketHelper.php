<?php

namespace app\Helpers;

use app\Models\Ticket;
use Config;

class TicketHelper {


    public static function computeTotals(Ticket &$ticket)
    {
        
        $ticket->subtotal = $ticket->items->sum('amount');

        $taxExempt = false;
        if($ticket->customer)
        {
            if($ticket->customer->tax_exempt)
                $taxExempt = true;
        }

        $adjustment = -1 * $ticket->discount + $ticket->labor + $ticket->freight;

        if(!($ticket->resale || $taxExempt))
            $ticket->tax = round(($ticket->subtotal - $ticket->discount) * Config::get('pos.sales_tax'), 2);
        else
            $ticket->tax = 0;
        
        $ticket->total = round($ticket->subtotal + $adjustment + $ticket->tax, 2);
      // $ticket->subtotal = number_format($ticket->subtotal, 2);
       // $ticket->tax = number_format($ticket->tax, 2);

        //return $ticket;
    }
    
}
