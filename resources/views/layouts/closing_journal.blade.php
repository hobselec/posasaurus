Closing report for {{ $date }}

<p>
<table>
    @foreach($tickets as $ticket)
    <tr>
        <td>{{ $ticket->display_id }}</td>
        <td>{{ $ticket->customer->display_name }}</td>
        <td>{{ $ticket->payment_type }}</td>
        <td>{{ $ticket->total }}</td>
    </tr>
    @endforeach
</table>
</p>

Cash       {{ $totals->total_cash }}<br>
Checks     {{ $totals->total_checks }}<br>

<p>
<table style="border: none">
    <tr>
    <td>Opening </td><td>     {{  $totals->opening_drawer }}</td>
    </tr>
    <tr>
    <td>Total Sales</td><td>   {{  $totals->total_sales }}</td>
    </tr>
    <tr>
    <td>O/S Cash</td><td>      {{  $totals->os_cash }}</td>
    </tr>
    <tr>
    <td>O/S Checks </td><td>   {{  $totals->os_checks }}</td>
    </tr>
</table>
</p>