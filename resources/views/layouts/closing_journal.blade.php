Closing report for {{ date? }}

<p>
    @foreach($tickets as $ticket)

    @endforeach
</p>

Cash       $total_cash
Checks     $total_checks

Opening        $opening_drawer
Total Sales    $total_sales
O/S Cash       $over_shortcash_prefix$over_short
O/S Checks     $over_shortchecks_prefix$over_short_checks