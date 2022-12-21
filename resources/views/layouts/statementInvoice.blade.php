@if(!$breakPage)
<html>
<body>
@endif
<style type="text/css">
.invoice_border th, .invoice_border td { border: 1px solid #000000 }
.page_break { page-break-before: always; }
</style>

<div style="font-size: 10pt" @if($breakPage) class="page_break"@endif>

<div class="font-weight: bold; margin-left: auto; margin-right: auto; text-align: center">
<center>
	{{ $config['company_name'] }}<BR>
    {{ $config['company_address'] }}<BR>
    {{ $config['company_city'] }}, {{ $config['company_state'] }} &nbsp; {{ $config['company_zip'] }}<BR>
    Phone: {{ $config['company_phone'] }}
</center>
</div>

<P></P>


<table class="invoice_heading_table" style="width: 90%; margin-left: auto; margin-right: auto; border-collapse: collapse; border: 1px solid #000000">
<tr class="invoice_border">
    <th>Customer No.</th><th>Job Name</th><th>Invoice No.</th><th>Received by</th><th style="width: 80px">Date</th><th style="width: 80px">Time</th>
</tr>
<tr class="invoice_border" style="text-align: center">
    <td>{{ $customer->id }}</td>
	<td>{{ $ticket->job->name  ?? ''}}</td>
	<td>{{ $ticket->display_id }}</td>
	<td>{{ $ticket->recv_by }}</td>
	<td>{{ $ticket->date->format('m/d/Y') }}</td>
	<td>{{ $ticket->date->format('g:i a') }}</td>
</tr>
</table>


<P></P>

<p></p>


<CENTER>
<table WIDTH="95%" BORDER=0 CELLPADDING="4px" class="items_list" style="margin-top: 15px; margin-left: auto; margin-right: auto; border: none">
<tr>
	<th>Quantity</th><th>SKU</th><th>Description</th><th align="center">Price</th><th align="center">Amount</th>
</tr>



@foreach($ticket->items as $item)
<tr style="text-align: center">
	<td>{{ $item->qty }}</td>
	<td><center>
		@if($item->legacy_item_barcode != '')
		DB {{ $item->legacy_item_barcode }}
		@elseif($item->product_id !=  '')
		{{ $item->product_id }}
		@else
		HL {{ $item->id }}
		@endif
	</center>
	</td>
	<td class="item_name"><CENTER>{{ $item->name }}</CENTER></td>
	<td align="right" style="text-align: right; padding-right: 10px;">$
	@if($item->refund)
	- 
	@endif
	{{ $item->price }}
	</td>
	<td align="right" style="text-align: right; padding-right: 10px;">$
	@if($item->refund)
	- 
	@endif 
	{{ $item->amount }}
	</td>
</tr>
@endforeach


<tr class="totals_row">
	<td colspan="3" style="border-right: none"></td>
	<td align="right" style="border-left: none; text-align: right; padding-right: 10px">Subtotal <br />



@if($ticket->discount > 0)
	Discount<br />
@endif

@if($ticket->tax > 0)
	Tax<br />
@endif

@if($ticket->freight > 0)
	Freight <br />
@endif

@if($ticket->labor > 0)
	Labor <br />
@endif

	<p><FONT FACE="arial">Total Amount</FONT></p>
	<p><FONT FACE="arial">Payment Type</FONT></p>
	</td>

	<td align="right" style="text-align: right; padding-right: 10px; border-bottom: 1px solid #000000">$
	@if($item->refund)
		- 
	@endif 
	{{ number_format($ticket->subtotal, 2) }}<br />


	@if($ticket->discount > 0)
	$	- {{ number_format($ticket->discount, 2) }}<br />
	@endif

	@if($ticket->tax > 0)
	$	@if($item->refund)
			- 
		@endif 
	{{ number_format($ticket->tax, 2) }}<br />
	@endif

	@if($ticket->freight > 0)
	$	{{ number_format($ticket->freight, 2) }}<br />
	@endif

	@if($ticket->labor > 0)
	$	{{ number_format($ticket->labor, 2) }} <br />
	@endif


	<p><FONT FACE="arial">
	@if($item->refund)
		- 
	@endif 

	$ {{ number_format($ticket->total, 2) }}</FONT>
	</p>
	<p><FONT FACE="arial">
	{{ strtoupper($ticket->payment_type) }}

	@if($ticket->payment_type == 'CHECK' || $ticket->payment_type == 'PAYMENT_CHECK')
		- {{ $ticket->check_no }}
	@endif
	</FONT>
	</p>

	</td>
</tr>

</table>
</CENTER>

</div>
@if(!$breakPage)
</body>
</html>
@endif