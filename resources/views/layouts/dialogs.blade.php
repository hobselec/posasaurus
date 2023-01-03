<!-- Dialogs

-->



<!-- Reports dialog -->
<div id="reports_dialog">
    <div id="reports_content" style="font-size: 10pt">
    <input type="checkbox" id="only_show_balances" value="1"><!-- filler before dialog is open -->
    </div>
</div>

<!-- opening balance-->
<div id="startup_dialog" style="display: none; font-size: 12pt" class="posdlg">
Opening cash: &nbsp; <input type="text" class="currency" autocomplete="off" onkeyup="add_decimals(this, event, 'save_opening_balance')" size="8" maxlength="8" id="open_cash" />
<div id="debug_container"></div>
</div>

<!-- shutdown dialog -->
<div id="shutdown_dialog" style="display: none; font-size: 12pt" class="posdlg">
Closing cash: &nbsp; <input type="text" size="8" maxlength="8" id="closing_cash" onkeyup="add_decimals(this, event, false)" /> <br />
Closing checks: &nbsp; <input type="text" size="8" maxlength="8" id="closing_checks" onkeyup="add_decimals(this, event, 'print_end_report')" /><br />

<p style="margin-left: auto ;margin-right: auto"><button class='ui-button' type="button" onclick="print_end_report()">Print Report</button></p>
<p style="margin-left: auto ;margin-right: auto"><button class='ui-button' type="button" onclick="print_weekly_report()">Print Weekly Report</button></p>
<span id="weekly_report_fields" style="display: none"><input type="text" id="report_start_date" size="10" maxlength="10" /> &ndash; <input type="text" value="<?php echo date("n/d/Y"); ?>" id="report_end_date" size="10" maxlength="10" /></span>

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

        <input type="hidden" id="cart_item_description_barcode" />
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
<p>Name: &nbsp; <span class="text-info" id="billing_adjustment_display_name"></span></p>

<b>Type:</b><br />
<input type="radio" id="billing_adjustment_refund" name="billing_adjustment_type"  onclick="$billing.adjustment.refundFormat.show()" /> 
<label for="billing_adjustment_refund" class="nice-label-radio"> Cash Refund</label> <br>
<input type="radio" id="billing_adjustment_discount" name="billing_adjustment_type"  onclick="$billing.adjustment.refundFormat.hide()" />
 <label for="billing_adjustment_discount" class="nice-label-radio"> Discount</label><br>
 <input type="radio" id="billing_adjustment_svccharge" name="billing_adjustment_type" onclick="$billing.adjustment.refundFormat.hide()" />
 <label for="billing_adjustment_svccharge" class="nice-label-radio"> Service Charge</label>


<p class="mt-2" id="billing_adjustment_refund_format">Refund Format: 
    <label for="cash_refund_payment_cash">
        <input type="radio" name="cash_refund_type" id="cash_refund_payment_cash" /> 
        &nbsp; <label for="cash_refund_payment_cash" class="nice-label-radio">Cash </label>
        <input type="radio" name="cash_refund_type" id="cash_refund_payment_check" />
        &nbsp; <label for="cash_refund_payment_check" class="nice-label-radio">Check </label>
<p>Amount: &nbsp; </p>
<input type="text" class="currency" onkeyup="add_decimals(this, event, 'save_service_charge')" size="8" maxlength="8" id="cash_refund_amount" />

<p style="display: none" id="service_charge_job_container">
Job: &nbsp; <select id="service_charge_job_id"></select>
</p>

<p class="mt-2">
<button type="button" class="btn btn-primary" onclick="saveBillingAdjustment()">Save</button>
</p>

</div>

<!-- service charge dialog -->
<!--
<div id="service_charge_dialog" style="display: none; font-size: 12pt; z-index: 1000; width: 300px; left: 20%; top: 25%; height: 200px" class="posdlg">
    <div style="text-align: right">
    <img src="img/close.png" onclick="close_service_charge_dialog()" style="height: 24px; width: 24px; cursor: pointer" alt="Close" />
    </div>
    <b id="special_charge_type">Service Charge:</b><br />
    <p>Name: &nbsp; <span id="service_charge_name"></span></p>
    <p style="display: none" id="service_charge_job_container">Job: &nbsp; <select id="service_charge_job_id"></select></p>
    <p>Amount: &nbsp; <br /><input type="text" class="currency" onkeyup="add_decimals(this, event, 'save_service_charge')" size="8" maxlength="8" id="service_charge_amount" /> <button type="button" onclick="save_service_charge()">Save</button>
    </p>
    <input type="hidden" id="service_charge_customer_id" />
</div>
-->