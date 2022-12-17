<table style="width: 100%">
	<tr>
	<td style="width: 30%; text-align: left">
    {{ $config['company_name'] }}<BR>
    {{ $config['company_address'] }}<BR>
    {{ $config['company_city'] }}, {{ $config['company_state'] }} &nbsp; {{ $config['company_zip'] }}<BR>
    Phone: {{ $config['company_phone'] }}
	</td>

	<td style="width: 70%; text-align: right">
	<H2>Statement</H2>
	<P>Date</P>
	{{ $statement->date }}
	</td>
	</tr>
	</table>

	<table>
	<tr>
	<TD style="width: 15%; text-align: left"></TD>
	<td style="width: 60%; text-align: left">To:</td>
	<td style="width: 25%"></td>
	</TR>
	<TR>
	<td style="width: 15%"></td>
	<TD style="width: 60%; text-align: left">
	{{ $customer->display_name }}<br />
	{{ $customer->address }}<br />
	{{ $customer->city }}, &nbsp; {{ $customer->state }} &nbsp; {{ $customer->zip }}
	</TD>
	<td style="width: 25%"></td>
	</TR>
	</TABLE>

	<PRE>




	</PRE>

	<TABLE BORDER=0 CELLSPACING="3px" align="left" style="width: 100%">
	<TR>
	   <TD width="40%" colspan="2"></TD>
	   <TD width="30%">Terms</TD>
	   <TD width="30%">Amount Due</TD>
	</TR>
	<TR>
	  <TD width="40%" colspan="2"></TD>
	  <TD width="30%">Net 30</TD>
	  <TD width="30%" align="right"> {{ $customer->curBalance }} ??</TD>
	</TR>

	<TR>
	  <TD >Date</TD>
	  <TD ></TD>
	  <TD align="right">Amount</TD>
	  <TD align="right">Balance</TD>
	</TR>

    <TR>
    <TD style="width: 20%"><CENTER>{{ $customer->balanceForwardDate }}</CENTER></TD>
    <TD style="width: 35%"></td><td style="width: 20%; text-align: center"> &nbsp; &nbsp;Balance Forward</TD>
    <TD style="text-align: right; width: 25%"> {{ $customer->forwardBalance }} </TD>
    </TR>

    @foreach($statement->curTickets as $ticket)
    <TR>
        <TD style="width: 20%"><CENTER>{{ $ticket->date }}</CENTER></TD>
        <TD style="width: 35%">&nbsp; &nbsp; &nbsp; {{ $ticket->type }}</TD>
        <TD ALIGN="RIGHT" style="width: 20%"> {{ $ticket->total }} </TD>
        <TD align="right" style="width: 25%"> {{ $ticket->curBalance }} </TD>
    </TR>
    @endforeach

    </table>