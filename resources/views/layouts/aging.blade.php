<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Point of Sale Aging Report</title>
</head>

<body class="posbody" style="font-family: sans-serif">
<style type="text/css">

.reports_table td, .reports_table th { border: 1px solid #000000; padding: 3px }

</style>

<div style="font-size: 14pt; font-weight: bold; margin-left: auto; margin-right: auto; text-align: center">
Aging Summary as of {{ Carbon\Carbon::now()->format('m/d/Y') }}
&nbsp; <label><input type="checkbox" id="only_show_balances" onclick="show_reports_dialog()">
 <label for="only_show_balances" class="nice-label" checked> Balances only</label>
</p>
</div>

<table border=1 style="border: 1px solid #000000; border-collapse: collapse" class="reports_table">
  <tr>
    <th align="center">Customer</th>
    <th>Current</th>
    <th>1-30 days</th>
    <th>30-60 days</th>
    <th>60-90 days</th>
    <th>&gt; 90 days</th>
    <th>Total</th>
  </tr>

  @if(count($results) == 0)
  <tr><td colspan="7">
    No customers found
    </td>
    </tr>
    @endif

    @foreach($results as $customer)
    <tr>
        <td>{{ $customer->name }}</td>

        @foreach($customer->periods as $period)
            <td>$ {{ number_format($period->balance, 2) }}</td>
        @endforeach
    </tr>

    @endforeach

<tr>
    <th>Totals</th>
    @foreach($totals as $total)
    <th>$  {{ number_format($total, 2) }} </th>
    @endforeach
    
</tr>

</body>
</html>