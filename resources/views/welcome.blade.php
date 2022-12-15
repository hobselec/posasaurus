
<!--require("init.php");-->

<!DOCTYPE html> 
<html lang="en-US">

<head>

<title>Point of Sale</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        @vite(['resources/js/app.js'])

<script type="text/javascript" src="/pos/js/bootstrap.bundle.min.js"></script>

<script type="text/javascript" src="/pos/js/jquery-3.6.1.min.js"></script>

<script type="text/javascript" src="/pos/jquery-ui/jquery-ui.min.js"></script>

<script type="text/javascript" src="/pos/js/sweetalert2.min.js"></script>


<script  src="/pos/js/vscontext.js" type="text/javascript"></script>

<script  src="/pos/js/core.js" type="text/javascript"></script>
<script  src="/pos/js/includes.js" type="text/javascript"></script>
<script  src="/pos/js/customer.js" type="text/javascript"></script>
<script  src="/pos/js/transaction.js" type="text/javascript"></script>
<script  src="/pos/js/payments.js" type="text/javascript"></script>
<script  src="/pos/js/cart.js" type="text/javascript"></script>
<script  src="/pos/js/billing.js" type="text/javascript"></script>
<script  src="/pos/js/catalog.js" type="text/javascript"></script>
<script  src="/pos/js/context_menu_actions.js" type="text/javascript"></script>
 
<link rel="stylesheet" type="text/css" href="/pos/css/bootstrap/bootstrap.min.css" />

<link rel="stylesheet" type="text/css" href="/pos/css/pos.css" />
<link rel="stylesheet" type="text/css" href="/pos/css/checkboxes.css" />
<link rel="stylesheet" type="text/css" href="/pos/css/vscontext.css" />

<link rel="stylesheet" type="text/css" href="/pos/jquery-ui/jquery-ui.min.css" />

</head>

<body class="posbody">

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
   @csrf
</form>

<!-- Hidden Transaction variables -->

<div id="transactionVars">
<input type="hidden" id="ticket_id" value="" />
<input type="hidden" id="customer_id" value="" />
<input type="hidden" id="customer_job_id" value="" />
<input type="hidden" id="tax_exempt" value="0" />
<input type="hidden" id="allow_credit" value="0" />

<input type="hidden" id="tax_rate" value="" />
</div>

<!-- Begin POS window -->

<header>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
  
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
	  <li class="nav-item m-2">
		<img src="img/cash_register.png" title="Point of Sale" onclick="$pos.mainContainer.show(); close_billing_dialog()" style="cursor: pointer; width: 42px; height: 42px" alt="Point of Sale" />

        </li>

        <li class="nav-item m-2">
		<img src="img/customer.png" title="Customer Info" onclick="customerdialog()" style="cursor: pointer; width: 42px; height: 42px" alt="Customer Info" />

        </li>

        <li class="nav-item">
          <a class="nav-link" href="#"><img src="img/jobs.png" title="Customer Jobs" onclick="customer_jobs_dialog()" style="cursor: pointer; width: 42px; height: 42px" alt="Customer Jobs" /> &nbsp;&nbsp;</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" tabindex="-1" aria-disabled="true"><img src="img/billing.png" title="Billing" onclick="show_billing_dialog()" style="width: 42px; height: 42px; cursor: pointer" alt="Billing" /> &nbsp;&nbsp; 
			</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">
		  <img src="img/bookOrangeClear.png" style="height: 42px; cursor: pointer" title="Product Catalog" onclick="show_catalog()" alt="Product Catalog" />  &nbsp;
   
			</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">
		  <img src="img/payment.png" style="height: 42px; width: 42px; cursor: pointer" id="recv_payment_button" title="Receive Payment" onclick="recv_payment_screen()" alt="Receive Payments" /> &nbsp; 
   
			</a>
        </li>
		<li class="nav-item">
          <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">
			   
	<img src="img/exit.png" style="height: 42px; width: 42px; cursor: pointer" id="shutdown_button" title="Reconcile and Shutdown" onclick="show_shutdown_dialog()" alt="Shutdown" />

			</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</header>


<!-- Billing Dialog -->
<div id="billing_dialog" style="display: none" class="container">
<h2>Billing</h2>

	<select id="billing_display_types" onchange="show_billing_dialog()">
	<option value="all">All Accounts</option>
	<option value="balances">Only Balances</option>
	</select></span>

	<span style="padding-left: 50px">
	<input type="date" size="10" maxlength="10" id="billing_list_end_date" title="Last billing date" value="@php echo date("Y-m-d"); @endphp" onchange="show_billing_dialog()" /></span> &nbsp; &nbsp; <button type="button" onclick="view_customer_bills(0, '', event)">View All Transactions</button> &nbsp; <img id="printAllStatementsCtrl" src="img/document-print.png" style="vertical-align: middle; cursor: pointer" onclick="printAllStatements()" title="Print Statements" /> <img src="img/loading.gif" style="display: none" id="printAllStatementsIndicator" /> &nbsp; <img id="showReportsCtrl" src="img/chart.png" style="vertical-align: middle; cursor: pointer; height: 30px" onclick="show_reports_dialog()" title="Show Aging Report" /> 
	&nbsp; &nbsp;<input type="text" class="customer_search" maxlength="20" size="20" value="Search Customer" style="color: #cccccc"/>

	<!-- headings -->
	<div style="margin-top: 20px; width: 95%">
	<span style="padding-left: 30px; width: 75%; font-weight: bold">Customer</span><span style="float: right; width: 18%; font-weight: bold">Amount</span>
	</div>

	<div id="billing_container" style="overflow-x: none; overflow-y: scroll; margin-left: 10px; height: 350px; background: #ffffff; border: 1px solid #000000; width: 95%">
		<table id="billing_list" class="table table-striped">
			<tbody>
				<tr><td>loading</td></tr>
			</tbody>
		</table>
	</div>

</div>

<!--
<style type="text/css">#billing_container td, th { border: 1px solid #000000 }</style>
-->
<!-- Customer tickets and billing list -->
<div id="customer_bill_dialog" style="z-index: 110;" class="posdlg">
	<div style="text-align: right">
		<img src="img/close.png" onclick="close_customer_bill_dialog()" style="cursor: pointer" alt="Close" />
	</div>

	<span id="customer_bill_name" style="font-size: 16pt"></span> &nbsp; <img src="img/loading.gif" id="customer_activity_indicator" style="display: none" /><br />
	<select id="customer_bill_job_id">
	<option value="">&ndash; Choose Job &ndash;</option>
	</select> &nbsp; &nbsp; &nbsp; 
	<input type="date" id="bill_start_date" size="10" onchange="view_customer_bills($billing.customer_bill_customer_id.val())" value="" /> &nbsp; to &nbsp; <input type="date" id="bill_end_date" size="10" value="@php echo date("Y-m-d"); //$week_ago; @endphp" onchange="view_customer_bills($billing.customer_bill_customer_id.val())" /> &nbsp; 
	<select id="customer_bill_transaction_type" onchange="view_customer_bills($billing.customer_bill_customer_id.val())">
	<option value="all" selected="selected">All Transactions</option>
	<option value="payments">Payments</option>
	<option value="returns">Returns</option>
	<option value="charges">Charges</option>
	<option value="paid_transactions">Cash/Check/CC</option>
	<option value="voids">Voids</option>
	</select> &nbsp;
	<img id="print_statement_button" src="img/document-print.png" style="vertical-align: middle; cursor: pointer" onclick="print_customer_statement(0)" title="Print Statement" /> &nbsp;

	<img id="viewStatementCtrl" src="img/CCBill-20120401.png" style="width: 30px; height: 30px; vertical-align: middle; cursor: pointer" onclick="view_customer_statement()" title="View Statement" /> 


	<!-- <button type="button" onclick="print_customer_statement()" id="print_statement_button">Print Statement</button>-->
	&nbsp; &nbsp; <input type="text" class="ticket_search" maxlength="15" size="15" value="Find Ticket #" style="color: #cccccc" onkeyup="viewTicket(this.value, event)" />
	<input type="hidden" id="customer_bill_customer_id" />
	<!-- headings -->

	<table class="ticket_heading">
	<tr id="ticket_heading_sort_row">
	<td style="font-weight: bold; padding-left: 10px; width: 100px;"><a href="javascript:view_customer_bills('-1', 'id_sortimg')"><img src="img/arrow_down.gif" id="id_sortimg" />Ticket ID</a></td>
	<td style="float: left; width: 160px; font-weight: bold"><a href="javascript:view_customer_bills('-1', 'customer_sortimg')"><img src="img/arrow_down.gif" id="customer_sortimg" />Customer Name</td>
	<td style="text-align: center; width: 50px; font-weight: bold"><a href="javascript:view_customer_bills('-1', 'job_sortimg')"><img src="img/arrow_down.gif" id="job_sortimg" />Job</td>
	<td style="padding-left: 105px; width: 140px; font-weight: bold"><a href="javascript:view_customer_bills('-1', 'date_sortimg')"><img src="img/arrow_down.gif" id="date_sortimg" />Date</td>
	<td style="float: left; width: 65px; font-weight: bold"><a href="javascript:view_customer_bills('-1', 'amount_sortimg')"><img src="img/arrow_down.gif" id="amount_sortimg" />Amount</td>
	<td style="float: left; padding-left: 60px; width: 65px; font-weight: bold"><a href="javascript:view_customer_bills('-1', 'type_sortimg')"><img src="img/arrow_down.gif" id="type_sortimg" />Type</td>
	</tr>
	</table>

</div>

<div id="customer_tickets_container" style="display: none; overflow-x: none; overflow-y: scroll; margin-left: 10px; height: 200px; background: #ffffff; border: 1px solid #000000; width: 95%; margin-top: 5px">
	<table id="customer_tickets_list" style="font-size: 90%; cursor: default; border-collapse: collapse; background: #ffffff; width: 100%"></table>

	<!-- individual ticket headings -->
	<div style="margin-top: 10px; width: 95%; font-size: 85%">
	<table style="border-collapse: collapse" class="ticket_heading">
	<tr><td style="font-weight: bold; padding-left: 20px; width: 50px;">Item ID</td><td style="width: 100px; font-weight: bold; padding-left: 15px">Quantity</td><td style="width: 210px; font-weight: bold; padding-left: 30px">Item Description</td><td style="padding-left: 105px; width: 130px; font-weight: bold">Price</td><td style="padding-left: 10px; width: 100px; font-weight: bold">Total</td></tr>
	</table>
	</div>


	<div id="ticket_items_container" style="overflow-x: none; overflow-y: scroll; margin-left: 10px; height: 170px; background: #ffffff; border: 1px solid #000000; width: 95%; margin-top: 5px">
	<table id="ticket_items_list" style="overflow-x: none; overflow-y: scroll; font-size: 85%; cursor: default; border-collapse: collapse; background: #ffffff; width: 100%"></table>
	</div>

</div> <!-- end billing -->


<div id="main_container">


<span id="clock_container" style="color: #666666; position: absolute; top: 10px; right: 10px; width: 280px; font-size: 12pt">
@php
 echo date("D M j, Y"). " &nbsp; &nbsp;" . date("g:i a");
@endphp
</span>

<!-- Ticket ID and name/job -->
<div style="margin-top: 20px">
	<span style="padding-left: 30px; font-size: 180%"># </span>
	<span style="font-size: 200%" id="ticket_display_id"></span>
	<span style="padding-left: 150px; font-size: 240%; font-weight: bold; color: brown" id="customer_display_name"></span> &nbsp; &nbsp; 
	<span style="font-size: 110%; color: #996666" id="customer_job_display_name"></span>
	<span style="font-size: 200%; color: blue" id="refund_indicator"></span>
</div>

<!-- Totals -->



<!-- Received by -->
<div style="text-align: center; margin-left: auto; margin-right: auto; display: none" id="recv_by_container">
	<span>Received by:</span> 

	<span id="recv_by_name"></span> &nbsp; 

	<!-- <button type="button" id="add_recv_by_button" class="btn btn-link" onclick="$(this).addClass('visually-hidden'); $pos.">Add received by...</button>
	<button type="button" id="save_recv_by_button" class="btn visually-hidden" onclick="apply_payment_specialoptions()">Save received by</button>
	-->

</div>

&nbsp; <img src="img/loading.gif" style="display: none" id="loading_recv_by" />

<!--    Cart Headings       -->
<div class="row">
	<div class="col col-auto">

<table style="width: 680px; border-collapse: collapse">
<tr>
	<th style="width: 50px"></th>
	<th style="width: 50px">Qty</th>
	<th style="width: 420px">Item</th>
	<th style="width: 100px">Price</th>
	<th style="width: 100px">Amount</th>
</tr>
</table>
 
<div id="cart_container" style="font-size: 80%; overflow-x: none; overflow-y: scroll; margin-left: 10px; height: 300px; background: #ffffff; border: 1px solid #000000; width: 680px">
  
	<table id="cart" class="table table-striped">
		<tbody></tbody>
	</table>
  
</div>
</div>
<div class="col col-auto">

	<div style="float: right; height: 300px; text-align: right; margin-top: 10px; font-size: 120%; margin-right: 5px">

	Sub Total: &nbsp; $ <span id="subtotal"></span>

		<p style="padding-top: 8px">
		<img id="discount_icon" src="img/sale.png" title="Discount" style="width: 40px; height: 35px; display: none; margin-top: 25px" alt="Discount" />
		<span style="color: green" id="discount_display_total">
		</span>
		</p>

			Tax: $ <span id="tax"></span>

			<p>
			<span><img id="freight_icon" src="img/lorrygreen.png" title="freight charges" style="width: 40px; height: 40px; display: none" alt="Freight" /></span> &nbsp; &nbsp; <span id="freight_display_total"></span>
			</p>
			<p>
			<span><img id="labor_icon" src="img/gears.png" title="labor charges" style="display: none; vertical-align: bottom" alt="Labor" /></span> &nbsp; &nbsp; <span id="labor_display_total"></span>
			</p>
			<p>
			<h2>Total: </h2>$<span id="display_total"></span>
			</p>

	</div>

</div>

</div> <!-- end row -->
  
<table>
	<tr>
	<td>

		<select id="open_transactions" onchange="chg_ticket(this.value)" class="form-select">
		<option value="">&ndash; Open Transactions &ndash;</option>
		<option value="-1" disabled="disabled" style="border-top: 1px dashed #999999"></option>
		</select>

	</td>
	<td style="width: 200px; text-align: right">
		<button type="button" onclick="clear_pos()" id="pause_button">Pause Transaction</button>
	</td>
	<td style="width: 200px">
		<button type="button" id="clear_button" onclick="clear_ticket()">Void Transaction</button>
	</td>
	<td style=" text-align: right">

	<button type="button" id="special_options_button" class="btn btn-secondary disabled btn-lg" data-bs-toggle="modal" data-bs-target="#payment_specialoptions_dialog">Special</button> 
	<button type="button" id="pay_button" class="btn btn-primary disabled btn-lg" data-bs-toggle="modal" data-bs-target="#payment_dialog">PAY</button>

	</td>
	</tr>
</table>

<table>
	<tr>
	<td style="padding-top: 20px; padding-left: 200px">
	# <input type="text" style="padding: 3px" id="barcode" onkeyup="check_enter(this.value, event)" size="60" /> 
	&nbsp; 
	<img title="Add new item" src="img/addnew.gif" style="width: 18px; height: 18px; cursor: pointer" onclick="add_catalog_item()" alt="Add item to Catalog" />
	</td>
	<td style="padding-top: 20px; "></td><!--<button type="button" id="lookup_button">Lookup Item</button></td>-->
	</tr>
</table>



<!-- PAYMENT SPECIAL OPTIONS -->
<div class="modal" tabindex="-1" id="payment_specialoptions_dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Special Options</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
				
		<div style="padding-bottom: 4px; text-align: center;">
		<b>Discount</b>

		<!-- no need for radio button given percentage is disabled
		<label for="lbl_disc_num"><input id="lbl_disc_num" type="radio" name="discount_type_selector" value="number" onclick="$pos.disc_pct.prop('disabled', true); $pos.disc_num').removeProp('disabled')" /> &nbsp; 
		-->

		Price: &nbsp;<input style="padding: 3px" size="7" maxlength="11" type="text" id="discount_number" value="" onkeyup="add_decimals(this, event, false)" /></label> <br />

		<!-- percent is disabled
		<label for="lbl_disc_pct"><input onclick="$pos.disc_num.prop('disabled', true); $pos.disc_pct.removeProp('disabled')"  id="lbl_disc_pct" type="radio" name="discount_type_selector" value="percentage" /> &nbsp; &nbsp; &nbsp; &nbsp; %:</label> <input style="padding: 3px; text-align: right" size="7" maxlength="2" type="text" id="discount_percentage" value="" onkeyup="calculate_discount_number()" />
		-->
	
		<div style="margin-top: 15px">
			<div style="padding-bottom: 4px; text-align: center;"><b>Other</b></div>
			&nbsp; &nbsp;<input type="checkbox" id="is_resale" /> <label for="is_resale" class="nice-label"> Resale: </label><br />

			Freight: <input size="7" type="text" id="freight_number" maxlength="11" onkeyup="add_decimals(this, event, false)" />
			<p style="margin-top: 5px">Labor: &nbsp;&nbsp; <input size="7" type="text" id="labor_number" maxlength="11" onkeyup="add_decimals(this, event, false)" /></p>
			</div>
			Received by: <input id="recv_by_input" type="text" maxlength="24" size="24"/>
		</div>

      </div>
      <div class="modal-footer">
	  	<button type="button" class="btn btn-primary" onclick="apply_payment_specialoptions()" data-bs-dismiss="modal">Ok</button>
      </div>
    </div>
  </div>
</div>

<!-- PAYMENT BOX -->

<div class="modal" tabindex="-1" id="payment_dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body ui-front">
        
			<div style="margin-top: 0px; margin-bottom: 10px; margin-right: 5px">

			<div class="mb-2">
			<input type="checkbox" name="pay_refund" id="pay_refund" value="1" onclick="apply_payment_specialoptions()" />
			<label for="pay_refund" class="nice-label">
			Refund:
			</label>
			</div>

			<!--
			<button type="button" id="cancel_payment" style="color: red;" onclick="cancel_payment(0)">Cancel</button> &nbsp;
			-->

			<div class="row">
				<div class="col col-auto">
				<button type="button" id="pay_cash" style="width: 85px; padding: 5px" onclick="show_payment('cash')">Cash</button>
				</div>
				<div class="col col-auto">
				<button type="button" id="pay_check" style="width: 86px; padding: 5px" onclick="show_payment('check')">Check</button>
				</div>
			</div>
			<div class="row mt-2">		
				<div class="col col-auto">
				<button type="button" id="pay_cc" style="padding: 5px" onclick="show_payment('cc')">Credit Card</button>
				</div>
				<div class="col col-auto">
				<button type="button" id="pay_charge" style="padding: 5px" onclick="show_payment('acct')">On Account</button>
				</div>
			</div>

			<div style="padding: 10px">
			<h5>Payment method: <span id="payment_method" style="font-weight: bold"></span></h5>
		</div>

			<div style="margin-top: 5px" id="accts">
				<input type="text" placeholder="Customer Name" class="class_customer_search form-control" id="customer_ticket_search" size="20" />
				
				<select id="pay_job_id" class="form-select" onchange="choose_pay_job_id()">
				</select>
			</div>


			<!-- TRANSACTION -->

			<div id="payment_take">

			<span id="take_check" style="display: none">Check #: &nbsp;<input type="text" class="trans_info" id="check_no" size="10" maxlength="11" style="padding: 5px" /></span><br />
			<span id="take_cc" style="display: none">Trans. #: &nbsp;<input type="text" class="trans_info" id="cc_trans_no" size="10" maxlength="11" style="padding: 5px" /></p><p></p></span><br />
			<span id="take_cash" style="display: none">Amount Received: &nbsp;<input type="text" id="cash_given" size="5" maxlength="9" style="padding: 5px" /></span><br />

			<input type="checkbox" id="printReceiptChkbox" checked="checked" />
			<label for="printReceiptChkbox" class="nice-label"> Print Receipt: </label>
			
			</div>

      </div>
      <div class="modal-footer">

		<button type="button" id="postpayment_button" class="btn btn-primary" onclick="post_transaction()">Finalize Transaction</button>
      </div>
    </div>
  </div>
</div>



</div> <!-- main container -->

<!-- CUSTOMER DIALOG -->

<div id="customer_dialog" style="z-index: 100; display: none; padding: 0; margin: 0" class="posdlg">
	<div style="left: 0; top: 0"><h3>Customer Database</h3></div>

	<div style="position: absolute; top: 0; right: 0; text-align: right;">
	<img src="img/close.png" onclick="close_customerdialog()" style="height: 32px; width: 32px; cursor: pointer" alt="Close" />
	</div>

	<table>
	<tr>
	<td style="height: 300px; vertical-align: top">
		<span style="font-size: 80%">
		<input type="checkbox" id="show_inactive" onclick="customerdialog('reload')" />
	<label for="show_inactive" class="nice-label">Show Inactive &nbsp; 
		</label>
		</span>
		
		<img src="img/addnew.gif" style="cursor: pointer" onclick="add_customer_form()" title="Add Customer" alt="Add Customer" /> <br />
		<input type="text" class="customer_search" maxlength="20" size="20" value="Search Customer" style="color: #cccccc"/><br />
		<select size="12" onchange="edit_customer_info(this.value)" id="customer_listing" style="width: 180px"></select>
		
	</td>

	<td style="vertical-align: top; padding-left: 15px" id="customer_jobs_cell">
	
		<select id="customer_job_listing" onchange="load_edit_job()"></select>
		<p><input type="text" id="customer_job_edit" maxlength="64" /></p><br />
		<p><button type="button" onclick="save_job_edit()">Save</button></p>
	</td>

	<td style="vertical-align: top; padding-left: 15px" id="customer_edit_cell">

	Last name<div style="padding-left: 100px; display: inline"> First name</div>
	<div style="display: inline; padding-left: 100px"> MI</div><br />
	<input type="text" id="edit_last_name" maxlength="50" />, &nbsp;<input type="text" id="edit_first_name" maxlength="50" />, <input type="text" id="edit_mi" size="3" maxlength="3" /><br />
	Company<br />
	<input type="text" id="edit_company" size="40" maxlength="64" />
	<p>Address<br />
	<input type="text" id="edit_address" size="40" maxlength="100" /><br />
	<input type="text" id="edit_address2" size="40" maxlength="100" /><br />
	City <span style="padding-left: 135px; display: inline"> State</span> &nbsp;&nbsp; Zip<br />
	<input type="text" id="edit_city" maxlength="50" />, <input type="text" id="edit_state" size="2" maxlength="30" /> &nbsp;&nbsp;<input type="text" id="edit_zip" size="5" maxlength="10" /><br />

	Phone <span style="padding-left: 120px">Ext</span><br />
	<input type="text" size="20" maxlength="42" id="phone" /> &nbsp; &nbsp;<input type="text" size="4" maxlength="4" id="phone_ext" /><br />
	</p>
	List by: 
	<input type="radio" id="edit_listby_company" name="use_company" /> <label for="edit_listby_company" class="nice-label-radio"> Company</label> 
	<input type="radio" id="edit_listby_lastname" name="use_company" /> <label for="edit_listby_lastname" class="nice-label-radio">Last name</label><br />
	<input type="checkbox" id="edit_allow_credit" value="1" /> <label for="edit_allow_credit" class="nice-label">Has Credit:</label><br />
	<input type="checkbox" id="edit_tax_exempt" value="1" /> <label for="edit_tax_exempt" class="nice-label">Tax Exempt: </label><br />
	<input type="checkbox" id="edit_active" value="1" /> <label for="edit_active" class="nice-label">Active: </label>
	<input type="hidden" id="editing_customer_id" />
	<p style="margin-left: auto; margin-right: auto; text-align: center">
	<button type="button" id="save_customer_button" onclick="save_customer_info()">Save</button>
	</p>
	</td>
	</tr>
	</table>
</div>

<div id="recv_payment_screen" style="z-index: 80; width: 400px" class="posdlg">
<div style="position: absolute; top: 0"><h3>Payment</h3></div>
<div style="text-align: right; margin-bottom: 15px">
<img src="img/close.png" onclick="close_recv_payments()" style="cursor: pointer" alt="Close" /></div>
<input type="hidden" id="payment_recv_customer_id" />
Customer Name<br />
<input type="text" value="" style="padding: 4px" size="20" maxlength="100" id="payment_recv_search_name" /><br />
<h3 style="color: brown" id="payment_recv_display_name"></h3>
<div id="payment_recv_display_balance" style="color: brown; font-size: 14pt; padding-bottom: 15px"></div>
<select style="display: none" id="payment_recv_job_id"></select>
<p>Date <br />
<input type="date" id="payment_recv_date" style="padding: 5px" value="{{ date("Y-m-d") }}" /> <select id="payment_recv_hour"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option></select> :

<input id="payment_recv_minute" type="text" maxlength="2" size="2" style="padding: 5px" />

<select id="payment_recv_ampm"><option value="am">AM</option><option value="pm">PM</option></select>
  
<p><select id="payment_recv_method">
<option value="">&ndash; Payment Type &ndash;</option>
<option value="cash">Cash</option>
<option value="check">Check</option>
<option value="cc">Credit Card</option>
</select>
</p>
<p>Amount: &nbsp; <input type="text" id="payment_recv_amt" size="11" maxlength="11" onkeyup="add_decimals(this, event, 'save_payment_recv')" /></p>
<p>Check or Trans # &nbsp; <input type="text" id="payment_recv_extra_info" size="11" maxlength="11" /></p>

<p><button type="button" onclick="save_payment_recv()">Save Payment</button></p>
</div>

<!-- Catalog Dialog -->
<div id="catalog_dialog" style="margin: 0; z-index: 195" class="posdlg">

	<div style="position: absolute; top: 0"><span style="font-size: 130%; font-weight: bold; background: #ffffff; -moz-border-radius: 3px; border-radius: 3px">Catalog</span><span style="padding-left: 50px"></span>
	</div>

	<div style="text-align: right; margin: 0; padding: 0">
	<img src="img/close.png" onclick="close_catalog()" style="cursor: pointer" alt="Close" />
	</div>

	<div style="margin-left: 150px; font-size: 130%">Search &nbsp; <input type="text" id="catalog_search_name" size="25" maxlength="30" onkeyup="search_catalog()" /> &nbsp;<img src="img/search.gif" onclick="search_catalog('go')" style="cursor: pointer; vertical-align: bottom" alt="Search Catalog" /> &nbsp; &nbsp; <img title="Add new item" src="img/addnew.gif" style="width: 18px; height: 18px; vertical-align: bottom; cursor: pointer" onclick="add_catalog_item()" alt="Add new item" />
	<br />


	@if(Config::get('pos.use_catalog_filter')) 

	<input type="checkbox" id="catalog_use_wholesaler" /> &nbsp; <label for="catalog_use_wholesaler" class="nice-label"> <small>Search Principal Wholesaler Only   &nbsp;</small></label>

	@else
	<input type="hidden" id="catalog_use_wholesaler" />
	@endif

	</div>

	<div id="catalog_headings" style="padding-left: 20px; padding-right: 20px; padding-top: 20px; font-size: 100%; font-weight: bold;">
		<div style="width: 60px; padding-left: 50px; float: left">SKU</div>
		<div style="width: 170px; float: left; padding-left: 53px ">Name</div>
		<div style="width: 150px; float: left; padding-left: 50px ">Vendor</div>
		<div style="width: 105px; padding-left: 20px; float: left">Barcode</div>
		<div style="width: 147px; padding-left: 25px; float: left ">Product ID</div>
		<div style="width: 65px; padding-left: 15px; float: left">Price</div>
		<div style="width: 50px;  float: left; padding-left: 10px">Qty</div>
	</div>
	<br />
	<div style="margin-top: 2px; height: 375px; overflow-x: none; overflow-y: scroll; border-top: 1px solid #000000">
		<table id="catalog_table" class="table table-striped">
			<tbody></tbody>
		</table>
	</div>

</div>

<!-- Add Catalog Item Dialog -->

<div class="posdlg" id="add_item_dialog" style="-moz-border-radius: 5px; border: 1px solid #000000; z-index: 196; display: none; width: 300px; height:240px; position: absolute; top: 15%; left: 20%; background: #cccccc; padding-left: 5px;" >
	<div style="text-align: right"><img src="img/close.png" onclick="$catalog.add_item_dialog.hide()" style="cursor: pointer" alt="Close" /></div>

	<div style="top: 5px; position: absolute">
	Item Name<br />
	<input type="text" id="new_item_name" size="30" maxlength="30" /><br />
	Price<br />
	<input type="text" id="new_item_price" size="10" maxlength="7" onkeyup="add_decimals(this, event, false)" /><br />

	<!--
	<select id="new_item_category">
	<option value="">&ndash; Choose Category &ndash;</option>
	</select>
	-->
	Barcode<br />
	<input type="text" id="new_item_skn" size="14" maxlength="14" />
	&nbsp; &nbsp; &nbsp; <button type="button" onclick="save_new_item()">Save Item</button><br />
		<div style="margin-top: 5px; font-size: 80%">
		<input type="checkbox" id="new_item_to_cart" checked="checked" /> 
		<label for="new_item_to_cart" class="nice-label">Add to cart &nbsp; </label>
		</div>
	</div>

</div>





@include("layouts.dialogs")


    <div class="vs-context-menu" style="border-radius: 4px">

		<input type="hidden" id="context_menu_type" />
		<input type="hidden" id="context_menu_id" />
        <ul>
            <li class="balances_cmenu_action"><a href="javascript:print_customer_statement()">Print Statement</a></li>
  	    <li class="balances_cmenu_action"><a href="javascript:print_customer_statement(1)">Print Statement & Tickets</a></li>
            <!--<li class="balances_cmenu_action"><a href="javascript:customerdialog();edit_customer_info(1127);" >Edit Contact</a></li>-->
	    <li class="balances_cmenu_action"><a href="javascript:alert('not implemented');" >Edit Contact</a></li>
	    <li class="balances_cmenu_action"><a href="javascript:show_service_charge_dialog('svc_charge')" >Add Service Charge...</a></li>
	    <li class="balances_cmenu_action"><a href="javascript:show_service_charge_dialog('discount')" >Add Discount...</a></li>
	    <li class="balances_cmenu_action"><a href="javascript:issue_cash_refund('discount')" >Issue Cash Refund...</a></li>
		    
	    <li class="ticket_cmenu_action"><a href="javascript:contextmenu_print_receipt('receipt');">Print Receipt</a></li>
            <li class="ticket_cmenu_action"><a href="javascript:contextmenu_print_receipt('invoice');">Print Invoice</a></li>
            <li class="ticket_cmenu_action"><a href="javascript:contextmenu_void_receipt()">Void Transaction</a></li>
	   
	    <li class="items_cmenu_action"><a href="javascript:contextmenu_add_cart_item_description()">Add description...</a></li>
        </ul>
    </div>

@php

$date = date('Y-n-d 00:00:00');

$result = DB::select("SELECT * FROM log WHERE date >= '$date' AND action='open'");

@endphp

@if(count($result) == 0)

<script type="text/javascript" >

	$(function() {
		
		// startup dialog for recording the opening balance
		
		$('#startup_dialog').dialog({ title : 'POS Startup', autoOpen: true, modal : true, resizable : false, draggable : false, width: 300, height: 250, buttons : { 'OK' : function() {
		
		var open_val = $('#open_cash').val(); 
		
		// check input is valid
		if(isNaN(open_val) || open_val == '' || open_val < 0)
		{
			alert("Please enter the opening cash value");
			return false;
		}
		
		save_opening_balance();
		
		}}, open: function(event, ui) {
				$(".ui-dialog-titlebar-close").hide();
				$(".ui-button ").css('margin-left' , 'auto').css('margin-right','auto').css('text-align','center');
				$('.ui-dialog-titlebar').css('font-size', '80%');
				$('.ui-button').css({'font-size' : '60%', 'padding' : '1px'});
				
			}
		});
		

	});
</script>
@endif


<script type="text/javascript">

$(function() {
//Swal.fire('Title', 'Testing!', 'info');

	// prevent bootstrap conflit with jquery-ui buttons
	$.fn.bootstrapBtn = $.fn.button.noConflict();

	// enable label printer
	$pos.useLabelPrinter =  false; 
	$pos.useAutoDecimal = false; 
	$edit_customer.default_customer_id = {{ Config::get('pos.default_customer_id') }};
	$pos.tax_rate =   {{ Config::get('pos.sales_tax') }}; 

	document.getElementById('tax_rate').value = $pos.tax_rate

	axios.get('/pos/ticket').then((response) => {

		let customer
		let ticketRows = ''

		response.data.tickets.forEach(item => {

			ticketRows += `<option value="${item.id}" style="padding: 5px">#${item.display_id} - ${item.customer.display_name}</option>`

		})
		let ot = document.getElementById('open_transactions')
		
		ot.innerHTML = ot.innerHTML + ticketRows
	})
});
		
</script>

</body>
</html>
