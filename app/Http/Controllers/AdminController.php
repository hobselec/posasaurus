<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use DB;
use Config;


class AdminController extends Controller
{
    public function getSalesTax(Request $request)
    {
        $SALES_TAX_RATE = Config::get('pos.sales_tax');

		if(!$request->has('start'))
			abort(400);

        $start = Carbon::parse($request->start);
        $end = $start->copy()->addMonths(1);

        $startDateTime = $start->format('Y-m-d 00:00:00');
        $endDateTime = $end->format('Y-m-d 00:00:00');

        $query = <<<EOF
        SELECT ticket.*, customers.* FROM ticket 
        LEFT JOIN customers ON customers.id=ticket.customer_id 
        WHERE 
        ticket.date >= '$startDateTime' AND ticket.date < '$endDateTime'
        AND payment_type NOT LIKE 'payment_%' 
        AND payment_type != 'svc_charge' 
        AND payment_type != 'acct_cash' 
        AND payment_type != 'acct_check'
         ORDER BY ticket.date
EOF;


        $result = DB::select($query);


        header("Pragma: public");
		header("Content-type: application/octet-stream");	
		header('Content-Disposition: attachment; filename="taxes.csv"');


		echo "Customer,Ticket,Date,Type,Taxable,Resale Non-tax,Tax Exemp Org,Refunds,Freight,Labor,Sales Tax\r\n\r\n";
		
		$total_taxable = 0;
		$total_resale = 0;
		$total_exemptorg = 0;
		$total_refund = 0;
		$total_freight = 0;
		$total_repairs = 0;
		$total_tax = 0;


		
		foreach($result as $row)
		{
			
			$exempt_org = '';
			$taxable = 0;
			$resale = '';
			$refund = '';

			$tax_exempt_flag = $row->tax_exempt;
			
			if($row->payment_type == 'VOID')
				continue;

			
			$row->use_company ? $customer_name = $row->company : $customer_name = "\"$row->last_name, $row->first_name\"";
			
			$refund = $row->refund;
			
			if($row->use_company)
				$customer_name = str_replace(",", "", $customer_name); // do not want commas
			
			if($row->tax > 0)
			{
				$taxable = $row->subtotal;
				$sales_tax = $row->tax;
			} else if($row->payment_type != 'discount') // don't interpret zero tax as tax-exempt on disc.
			{
				$taxable = '0.00';
				if($row->resale)
				{
					$resale = $row->total;
					$total_resale += $total_resale;
				}
				else if($refund == '0')
				{
					$exempt_org = $row->total;
					$total_exemptorg += $row->total;
				}
				$sales_tax = 0;
			}
			
			if($refund == '1' || $row->payment_type == 'discount')
			{
				$taxable = '0.00';
				
				if($row->tax > 0 || $row->payment_type == 'discount')
				{
					$refund = $row->total - $row->tax;

					// prevent discounts on tax exempt orgs from being recorded
					// this was fixed on 11/17/2012 for october sales tax, but 
					// needs fixed from whenever we started using the POS for discounts
					if(!($row->payment_type == 'discount' && $tax_exempt_flag))
						$total_refund += ($refund);

					$sales_tax = -1 * $row->tax; // tax gets subtracted from total on refunds
				} 
				else
					$refund = 0;

				if($row->payment_type == 'discount')
				{
					//$taxable = $row->total;
					$sales_tax = -1 * ($row->total*$SALES_TAX_RATE); // tax gets subtracted from total on refunds
				}
			}
			
			//$row->freight > 0 ? $freight = $row->freight : $freight = '';
			//$row->labor > 0 ? $labor = $row->label : $labor = '';
			
			$sales_tax = number_format($sales_tax, 2, '.', '');
			$taxable = number_format($taxable, 2, '.', '');
			
			list($date, ) = explode(" ", $row->date);
			
			if($row->payment_type == 'cash' || $row->payment_type == 'check')
				$row->payment_type = 'P';
			if($row->payment_type == 'acct')
				$row->payment_type = 'ACCT';
			else
				$row->payment_type = strtoupper($row->payment_type);
			
			if($row->resale == 0)
				$resale = 0;
			else
			{
				$resale = $row->total;
				$total_resale += $row->total;
			}
			
			$total_taxable += $taxable;
			$total_freight += $row->freight;
			$total_repairs += $row->labor;
			$total_tax += $sales_tax;
			
			
			echo "$customer_name,$row->display_id,$date,$row->payment_type,$taxable,$resale,$exempt_org,$refund,$row->freight,$row->labor,$sales_tax\r\n";
	
		}
		
		
		echo "\r\n";
		
		echo ",,,,$total_taxable, $total_resale,$total_exemptorg,$total_refund,$total_freight,$total_repairs,$total_tax";
		

	
		$total_tax = round($total_tax);
		$total_resale = round($total_resale);
		$total_refund = round($total_refund);
		$total_repairs = round($total_repairs);
		$total_exemptorg = round($total_exemptorg);
		$total_freight = round($total_freight);
	
		$subtotal_deductions = $total_resale + $total_refund + $total_repairs + $total_exemptorg + $total_freight;
		$total_deductions = $subtotal_deductions + $total_tax;


		// form page 1

		$total_receipts = round($total_taxable + $total_resale + $total_repairs + $total_exemptorg + $total_freight+$total_tax);

		$taxable_receipts =  ($total_receipts - $total_deductions);
		
		$tax_due = round($taxable_receipts * $SALES_TAX_RATE);
		$early_discount = round($tax_due * .0175);
		
		$payment_total = ($tax_due - $early_discount);

		echo "\r\n\r\n";

		echo "Step 2\r\n";	
		echo "1. Total receipts,$total_receipts\r\n";		
		echo "2. Deductions,$total_deductions\r\n";
		echo "3. Taxable receipts," . $taxable_receipts . "\r\n";
		echo "\r\n";
		echo "Step 3\r\n";			
		echo "4a. Merchandise,$taxable_receipts, x " . $SALES_TAX_RATE . ",    =	,$tax_due\r\n"; 
		echo "9. Tax due,$taxable_receipts,,,$tax_due\r\n";
		echo "\r\n";
		echo "Step 4\r\n";			
		echo "10. disc.," . $early_discount . "\r\n";		
		echo "11. net tax," . $payment_total . "\r\n";
		echo "\r\n";
		echo "Step 6\r\n";			
		echo "16. rec + purch,$payment_total\r\n";		
		echo "20. net tax due,$payment_total\r\n";
		echo "\r\n";
		echo "Step 7\r\n";			
		echo "23. total tax,$payment_total\r\n";
		echo "25. payment due,$payment_total\r\n";



		// worksheet
		// modified 2/23 to better match IL state form
	
		echo "\r\n\r\nSchedule A Deductions\r\n";
		echo "\r\n";
		echo "1. Retail sales tax," . $total_tax . "\r\n";
		//echo "2. (1a-1d)," . $total_tax . "\r\n";
		echo "\r\n";
		echo "4. resale," . $total_resale . "\r\n";
		echo "\r\n";
		echo " Cash refunds," . $total_refund . "\r\n";
		echo "\r\n";
		echo "13. Exempt Org.," . $total_exemptorg . "\r\n\r\n";
		echo "15. Sales of Service (Repairs)," . $total_repairs . "\r\n";
		echo "\r\n";
		echo "16. Other (Refunds and Freight)," . ($total_refund + $total_freight) . "\r\n";
		echo "\r\n";
		//echo "17. (add 3 - 16d)," .	$subtotal_deductions . "\r\n";
		echo "17. Total Deductions," .	$total_deductions . "\r\n";
	

    }

}
