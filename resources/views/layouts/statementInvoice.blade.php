<html>
<head>
<title></title>
<style type="text/css">
.invoice_border th, .invoice_border td { border: 1px solid #000000 }

</style>
</head>

<body style="text-align: center; font-family: sans-serif">
<BASEFONT FACE="arial" SIZE=1>

<CENTER><B>
{{ $customer->display_name }}<br />
	{{ $customer->address }}<br />
	{{ $customer->city }}, &nbsp; {{ $customer->state }} &nbsp; {{ $customer->zip }}

</CENTER>

<P></P>

<CENTER>
<table width="90%" BORDER=1 class="invoice_heading_table" style="margin-left: auto; margin-right: auto; border-collapse: collapse; border: 1px solid #000000">
<tr class="invoice_border">
    <th>Customer No.</th><th>Job Name</th><th>Invoice No.</th><th>Received by</th><th style="width: 80px">Date</th><th style="width: 80px">Time</th>
</tr>
<tr class="invoice_border">
    <td><center>$customer_id</center></td><td><center>$job_name</center></td><td><CENTER>$ticket_display_id</CENTER></td><td><center>$recv_by</center></td><td><CENTER>$ticket_date</CENTER></td><td><CENTER>$ticket_time</CENTER></td>
</tr>
</table>
</CENTER>

<P></P>

<p></p>


<CENTER>
<table WIDTH="95%" BORDER=0 CELLPADDING="4px" class="items_list" style="margin-top: 15px; margin-left: auto; margin-right: auto; border: none">
<tr><th>Quantity</th><th>SKU</th><th>Description</th><th align="center">Price</th><th align="center">Amount</th></tr>



@foreach($ticket->items as $item)
<tr>
	<td style=""><center>$item->qty</center></td>
	<td style=""><center>$item->item_id</center></td>
	<td class="item_name"><CENTER>$item->name</CENTER></td>
	<td align="right" style="text-align: right; padding-right: 10px;">$refund_indicator $item->price</td>
	<td align="right" style="text-align: right; padding-right: 10px;">$refund_indicator $item->amount</td>
</tr>
@endforeach


<tr class="totals_row"><td colspan="3" style="border-right: none"></td>
<td align="right" style="border-left: none; text-align: right; padding-right: 10px">Subtotal <br />



if($discount > 0)
	Discount<br />

if($tax != '')
Tax<br />

if($freight > 0)
	Freight <br />

if($labor > 0)
	Labor <br />


<p><FONT FACE="arial">Total Amount</FONT></p>
<p><FONT FACE="arial">Payment Type</FONT></p>
</td><td align="right" style="text-align: right; padding-right: 10px; border-bottom: 1px solid #000000">
$refund_indicator$subtotal<br />


if($discount > 0)
	" -" . $discount . "<br />
	
if($tax != '')
$refund_indicator$tax<br />

if($freight > 0)
	$freight <br />

if($labor > 0)
	$doc1 .= "$labor <br />


$total = number_format($total, 2);

$payment_type = strtoupper($payment_type);

if($payment_type == 'CHECK' || $payment_type == 'PAYMENT_CHECK')
    $payment_type .= ' - ' . $check_no;


<p><FONT FACE="arial">$refund_indicator$total</FONT></p>
<p><FONT FACE="arial">$payment_type</FONT></p>

</td></tr>

</table>
</CENTER>

</body>
</html>