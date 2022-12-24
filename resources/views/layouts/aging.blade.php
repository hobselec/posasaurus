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
Aging Summary as of $todays_date 
$print_icon &nbsp; <label><input type="checkbox" id="only_show_balances" onclick="show_reports_dialog()" $checked> <label for="only_show_balances" class="nice-label">Balances only</label>
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

  @if($results->count() == 0)
  <tr><td colspan="7">
    No customers found
</td>
</tr>

<tr>
    <th>Totals</th>
    <th>$   number_format($column_totals[0], 2)  </th>
    <th>$ " . number_format($column_totals[1], 2)  </th
    ><th>$  " . number_format($column_totals[2], 2) </th>
    <th>$ " . number_format($column_totals[3], 2) </th>
<th>$ " . number_format($column_totals[4], 2) <th>
    $ " . number_format($column_totals[5], 2) . "</th>
</tr>

</body>
</html>