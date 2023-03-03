
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
        @vite(['resources/js/app.js', 'resources/scss/app-pos.scss'])


<script type="text/javascript" src="/pos/js/jquery-3.6.1.min.js"></script>

<script type="text/javascript" src="/pos/jquery-ui/jquery-ui.min.js"></script>

<script type="text/javascript" src="/pos/js/sweetalert2.min.js"></script>


<script  src="/pos/js/vscontext.js" type="text/javascript"></script>

<script  src="/pos/js/context_menu_actions.js" type="text/javascript"></script>
 

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

<input type="hidden" id="tax_rate" value="" />
</div>

<!-- Begin POS window -->

<header>
<nav class="navbar navbar-expand navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
  
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
	  <li class="nav-item m-2">
		<img src="img/cash_register.png" title="Point of Sale" onclick="chgView($pos.mainContainer); close_billing_dialog()" style="cursor: pointer; width: 42px; height: 42px" alt="Point of Sale" />

        </li>

        <li class="nav-item m-2">
		<img src="img/customer.png" title="Customer Info" onclick="chgView($pos.customer_dialog); customerDialog()" style="cursor: pointer; width: 42px; height: 42px" alt="Customer Info" />

        </li>

        <li class="nav-item">
          <a class="nav-link" href="#" tabindex="-1" aria-disabled="true"><img src="img/billing.png" title="Billing" onclick="chgView($billing.dialog); show_billing_dialog()" style="width: 42px; height: 42px; cursor: pointer" alt="Billing" /> &nbsp;&nbsp; 
			</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">
		  <img src="img/bookOrangeClear.png" style="height: 42px; cursor: pointer" title="Product Catalog" onclick="chgView($catalog.dialog); show_catalog()" alt="Product Catalog" />  &nbsp;
   
			</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">
		  <img src="img/payment.png" style="height: 42px; width: 42px; cursor: pointer" id="recv_payment_button" title="Receive Payment" data-bs-toggle="modal" data-bs-target="#recv_payment_screen" alt="Receive Payments" /> &nbsp; 
   
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

@include('layouts.billing')

<div id="main_container">

@if(Config::get('pos.showClock'))
<span id="clock_container" style="color: #666666; position: absolute; top: 10px; right: 10px; width: 280px; font-size: 12pt">
@php
 echo date("D M j, Y"). " &nbsp; &nbsp;" . date("g:i a");
@endphp
</span>
@endif

<!-- Ticket ID and name/job -->
<div  class="container">
	<span class="ps-3" style="font-size: 180%"># </span>
	<span style="font-size: 200%" id="ticket_display_id"></span>
	<span class="ms-5" style="font-size: 240%; font-weight: bold; color: brown" id="customer_display_name"></span> &nbsp; &nbsp; 
	<br>
	<span class="ps-3" style="font-size: 110%; color: #996666" id="customer_job_display_name"></span>
	<span style="font-size: 200%; color: blue" id="refund_indicator"></span>
</div>

<!-- Totals -->
<!--
<i className="icon bi-envelope"></i>
<i class="bi-alarm" style="font-size: 2rem; color: cornflowerblue;"></i>
-->

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
	<div class="col col-lg-9">

		<div id="cart_container" style="font-size: 80%; overflow-x: hidden; overflow-y: scroll; margin-left: 10px; height: 300px; background: #ffffff; ">
		
			<table id="cart" class="table table-striped table-bordered table-sm" style="">
				<thead class="sticky-top" style="z-index: 1">
					<tr>
						<th></th>

						<th>Qty</th>
						<th>Item</th>
						<th>Price</th>
						<th>Amount</th>
					</tr>
				</thead>
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
  
<div class="row mt-3">

<div class="col col-auto">
		<select id="open_transactions" onchange="chg_ticket(this.value)" class="form-select">
		<option value="">&ndash; Open Transactions &ndash;</option>
		<option value="-1" disabled="disabled" style="border-top: 1px dashed #999999"></option>
		</select>

</div>
<div class="col col-auto">
		<button type="button" onclick="clear_pos()" id="pause_button">Pause Transaction</button>
</div>
<div class="col col-auto">
		<button type="button" id="clear_button" onclick="clear_ticket()">Void Transaction</button>
</div>
<div class="col col-auto">
	<button type="button" id="special_options_button" class="btn btn-secondary disabled btn-lg" data-bs-toggle="modal" data-bs-target="#payment_specialoptions_dialog">Special</button> 
	<button type="button" id="pay_button" class="btn btn-primary disabled btn-lg" data-bs-toggle="modal" data-bs-target="#payment_dialog">PAY</button>
</div>

</div>


	<div class="row ml-2 mt-2 p-3 mb-5">
		<div class="col col-auto">Item # or search </div>
		<div class="col col-auto">
			<input class="form-control " type="search" id="barcode" onkeyup="check_enter(this.value, event)" /> 
		</div>
		<div class="col col-auto">
			<img title="Add new item" src="img/addnew.gif" style="width: 18px; height: 18px; cursor: pointer" onclick="$catalog.add_item_dialog.dialog('open')" alt="Add item to Catalog" />
		</div>
	</div>



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

		Price: &nbsp;<input style="padding: 3px" size="7" maxlength="11" type="text" id="discount_number" value="" /> <br />

		<!-- percent is disabled
		<label for="lbl_disc_pct"><input onclick="$pos.disc_num.prop('disabled', true); $pos.disc_pct.removeProp('disabled')"  id="lbl_disc_pct" type="radio" name="discount_type_selector" value="percentage" /> &nbsp; &nbsp; &nbsp; &nbsp; %:</label> <input style="padding: 3px; text-align: right" size="7" maxlength="2" type="text" id="discount_percentage" value="" onkeyup="calculate_discount_number()" />
		-->
	
		<div style="margin-top: 15px">
			<div style="padding-bottom: 4px; text-align: center;"><b>Other</b></div>
			&nbsp; &nbsp;<input type="checkbox" id="is_resale" /> <label for="is_resale" class="nice-label"> Resale: </label><br />

			Freight: <input size="7" type="text" id="freight_number" maxlength="11" />
			<p style="margin-top: 5px">Labor: &nbsp;&nbsp; <input size="7" type="text" id="labor_number" maxlength="11" /></p>
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
					<input type="text" placeholder="Customer Name" class="class_customer_search form-control" id="customer_ticket_search" size="20" autocomplete="off" />
					
					<select id="pay_job_id" class="form-select" onchange="choose_pay_job_id()">
					</select>
				</div>


			<!-- TRANSACTION -->

			<div id="payment_take">

				<span id="take_check" style="display: none">Check #: &nbsp;<input type="text" class="trans_info" id="check_no" size="10" maxlength="11" style="padding: 5px" /></span><br />
				<span id="take_cc" style="display: none">Trans. #: &nbsp;<input type="text" class="trans_info" id="cc_trans_no" size="10" maxlength="11" style="padding: 5px" />
				</span>


				<div class="mt-2" id="take_cash" style="display: none">Amount Received: &nbsp;
					<input type="text" id="cash_given" size="5" maxlength="9" style="padding: 5px" />
				</div>

				<!-- not being used -->
				<span style="display: none">
				<input type="checkbox" id="printReceiptChkbox" checked="checked" />
				<label for="printReceiptChkbox" class="nice-label"> Print Receipt: </label>
				</span>

				<p>
					<h5>Total: $<span id="payment_dialog_total"></span></h5>
				</p>

			</div>

      </div>
      <div class="modal-footer">

		<button type="button" id="postpayment_button" class="btn btn-primary" onclick="post_transaction()" data-bs-dismiss="modal">Finalize Transaction</button>
      </div>
    </div>
  </div>
</div>



</div> <!-- main container -->

<!-- CUSTOMER DIALOG -->
@include('layouts.customers')


<div class="modal" tabindex="-1" id="recv_payment_screen">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body ui-front">

		<input type="hidden" id="payment_recv_customer_id" />
		Customer Name<br />
		<input type="search" class="form-control" maxlength="100" id="payment_recv_search_name" /><br />
		<h3 style="color: brown" id="payment_recv_display_name"></h3>
		<div id="payment_recv_display_balance" style="color: brown; font-size: 14pt; padding-bottom: 15px"></div>
		
		<select style="display: none" class="form-select" id="payment_recv_job_id"></select>
		<p>Date <br />
		<input type="datetime-local" id="payment_recv_date" class="form-control" />

		<p>
			<select id="payment_recv_method" class="form-select">
			<option value="">&ndash; Payment Type &ndash;</option>
			<option value="cash">Cash</option>
			<option value="check">Check</option>
			<option value="cc">Credit Card</option>
			</select>
		</p>
		<p>Amount: &nbsp; <input type="text" class="form-control" id="payment_recv_amt" size="11" maxlength="11" /></p>
		<p>Check or Trans # &nbsp; <input type="text" class="form-control" id="payment_recv_extra_info" size="11" maxlength="11" /></p>


	  </div>
      <div class="modal-footer">

	  <button type="button" class="btn btn-primary" onclick="save_payment_recv()" data-bs-dismiss="modal">Save Payment</button>
      </div>
    </div>
  </div>
</div>




@include("layouts.catalog")


@include("layouts.dialogs")


    <div class="vs-context-menu" style="border-radius: 4px">

        <ul>
            <li class="balances_cmenu_action"><a href="javascript:printCustomerStatement()">Print Statement</a></li>
  	    <li class="balances_cmenu_action"><a href="javascript:printCustomerStatement(1)">Print Statement & Tickets</a></li>
            <!--<li class="balances_cmenu_action"><a href="javascript:customerdialog();edit_customer_info(1127);" >Edit Contact</a></li>-->
	    <li class="balances_cmenu_action"><a href="javascript:alert('not implemented');" >Edit Contact</a></li>

		<!--
	    <li class="balances_cmenu_action"><a href="javascript:show_service_charge_dialog('svc_charge')" >Add Service Charge...</a></li>
	    <li class="balances_cmenu_action"><a href="javascript:show_service_charge_dialog('discount')" >Add Discount...</a></li>
	    <li class="balances_cmenu_action"><a href="javascript:issue_cash_refund('discount')" >Issue Cash Refund...</a></li>
		-->    
		<li class="balances_cmenu_action"><a href="javascript:$('#billing_adjustment_dialog').dialog('open')" >Add Adjustment...</a></li>

	    <li class="ticket_cmenu_action"><a href="javascript:contextmenu_email_invoice()">E-mail Invoice</a></li>
            <li class="ticket_cmenu_action"><a href="javascript:contextmenu_print_invoice()">Print Invoice</a></li>
            <li class="ticket_cmenu_action"><a href="javascript:contextmenu_void_transaction()">Void Transaction</a></li>
	   
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
		
		window.openingBalanceModal = new Modal(document.getElementById('startup_dialog'))
		openingBalanceModal.show()

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

			if(!item.customer)
				item.customer = { display_name : 'NONAME' }

			ticketRows += `<option value="${item.id}" style="padding: 5px">#${item.display_id} - ${item.customer.display_name}</option>`

		})
		let ot = document.getElementById('open_transactions')
		
		ot.innerHTML = ot.innerHTML + ticketRows
	})
});
		
</script>

</body>
</html>
