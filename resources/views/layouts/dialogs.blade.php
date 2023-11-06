<!-- Dialogs

-->



<!-- Reports dialog -->
<div id="reports_dialog">
    <div id="reports_content" style="font-size: 10pt">
    <input type="checkbox" id="only_show_balances" value="1"><!-- filler before dialog is open -->
    </div>
</div>

<!-- opening balance-->
<!--<div id="startup_dialog" style="display: none" class="posdlg">-->

<div class="modal" tabindex="-1" id="startup_dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Startup</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
     <div class="container">
            Opening cash: &nbsp; <input type="text" class="currency" autocomplete="off" onkeyup="saveOpeningBalance(event)" size="8" maxlength="8" id="open_cash" class="form-control" />


      </div>
      <div class="modal-footer">

		<button type="button" id="save_item_description_button" class="btn btn-primary" onclick="saveOpeningBalance()">Save</button>
      </div>
    </div>
  </div>
</div>


<!-- shutdown dialog -->
<div id="shutdown_dialog" style="display: none; font-size: 12pt" class="posdlg">
<div class="mt-2">
    Closing cash: &nbsp; <input type="text" size="8" maxlength="8" id="closing_cash" />
</div>
<div class="mt-2">
Closing checks: &nbsp; <input type="text" size="8" maxlength="8" id="closing_checks" />
</div>

<p style="margin-left: auto ;margin-right: auto"><button class='btn btn-light' type="button" onclick="print_end_report()">Print Report</button></p>

<!-- not implemented
  <p style="margin-left: auto ;margin-right: auto"><button class='btn btn-light' type="button" onclick="print_weekly_report()">Print Weekly Report</button></p>
-->

<span id="weekly_report_fields" style="display: none"><input type="date" id="report_start_date" size="10" maxlength="10" /> &ndash; <input type="date" value="<?php echo date("Y-m-d"); ?>" id="report_end_date" size="10" maxlength="10" /></span>
<p>
Tax Reports: 
@php
$startMonth = Carbon\Carbon::now()->startOfMonth()->addMonths(-2);
$months = [$startMonth, $startMonth->clone()->addMonths(1), $startMonth->clone()->addMonths(2)];
@endphp
@foreach($months as $month)
<a class="p-2" href="/pos/admin/sales-tax?start={{ $month->format('Y-m-d') }}">{{ $month->format('M') }}</a>
@endforeach
</p>

<button type="button" class="btn btn-link" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Log out</button>

</div>


<!-- Cart item add description dialog -->
<!--
<div id="cart_item_description_dialog" style="display: none; font-size: 12pt; z-index: 1000; width: 330px; left: 20%; top: 25%; height: 200px" class="posdlg">
<div style="text-align: right">
<img src="img/close.png" onclick="$dialogs.cart_item_description_dialog.hide()" style="height: 24px; width: 24px; cursor: pointer" alt="Close" />
</div>

<button type="button" onclick="save_cart_item_description()" id="save_cart_item_description_button">Save</button>
</p>

</div>
-->

<div class="modal" tabindex="-1" id="item_description_dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Item Edit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body ui-front">
        <b>Add Description:</b><br />
        <span id="cart_item_description_label"></span>
        <p>
        <input type="text" id="cart_item_description_name" class="form-control" maxlength="128" />

      </div>
      <div class="modal-footer">

		<button type="button" id="save_item_description_button" class="btn btn-primary" onclick="save_cart_item_description()">Save</button>
      </div>
    </div>
  </div>
</div>



<!-- add cash refund dialog -->
<div id="billing_adjustment_dialog" style="display: none">

    <!-- cash_refund_dialog, close_cash_refund_dialog() -->
    <p>Name: &nbsp; <span class="text-info" id="billing_adjustment_display_name"></span>
    </p>

    <b>Type:</b><br />
    <input type="radio" id="billing_adjustment_refund" value="refund" name="billing_adjustment_type"  onclick="$billing.adjustment.refundFormat.show(); $billing.adjustment.jobs.hide()" /> 
    <label for="billing_adjustment_refund" class="nice-label-radio"> Cash Refund</label> <br>
    <input type="radio" id="billing_adjustment_discount" value="discount" name="billing_adjustment_type"  onclick="$billing.adjustment.refundFormat.hide(); $billing.adjustment.jobs.show()" />
    <label for="billing_adjustment_discount" class="nice-label-radio"> Discount</label><br>
    <input type="radio" id="billing_adjustment_svccharge" value="svc_charge" name="billing_adjustment_type" onclick="$billing.adjustment.refundFormat.hide(); $billing.adjustment.jobs.hide()" />
    <label for="billing_adjustment_svccharge" class="nice-label-radio"> Service Charge</label>


    <p class="mt-2" id="billing_adjustment_refund_format">Refund Format: 
        <label for="cash_refund_payment_cash">
            <input type="radio" name="billing_adjustment_refund_format" id="cash_refund_payment_cash" value="cash" /> 
            &nbsp; <label for="cash_refund_payment_cash" class="nice-label-radio">Cash </label>
            <input type="radio" name="billing_adjustment_refund_format" id="cash_refund_payment_check" value="check" />
            &nbsp; <label for="cash_refund_payment_check" class="nice-label-radio">Check </label>
    </p>

    <p style="display: none" id="billing_adjustment_job_container">
    Job: &nbsp; <select class="form-select" id="billing_adjustment_job_id"></select>
    </p>

    <p class="mt-2">Amount: &nbsp; </p>
    <input type="text" class="currency form-control" size="8" maxlength="8" id="billing_adjustment_amount" />


    <p class="mt-2">
    <button type="button" class="btn btn-primary" onclick="saveBillingAdjustment()">Save</button>
    </p>

</div>
