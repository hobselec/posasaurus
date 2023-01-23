<?php

namespace app\Helpers;

use app\Models\Ticket;
use Config;

class TicketHelper {


    public static function computeTotals(Ticket &$ticket)
    {
        
        $ticket->subtotal = $ticket->items->sum('amount') - $ticket->discount + $ticket->labor + $ticket->freight;

        $taxExempt = false;
        if($ticket->customer)
        {
            if($ticket->customer->tax_exempt)
                $taxExempt = true;
        }

        if(!($ticket->resale || $taxExempt))
            $ticket->tax = round($ticket->subtotal * Config::get('pos.sales_tax'), 2);
        else
            $ticket->tax = 0;
        
        $ticket->total = round($ticket->subtotal + $ticket->tax,2);
      // $ticket->subtotal = number_format($ticket->subtotal, 2);
       // $ticket->tax = number_format($ticket->tax, 2);

        //return $ticket;
    }
    
}
