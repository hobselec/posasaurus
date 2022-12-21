
<!-- Billing Dialog -->
<div id="billing_dialog" style="display: none" class="container">
<h2>Billing</h2>

	<select id="billing_display_types" onchange="show_billing_dialog()">
	<option value="all">All Accounts</option>
	<option value="balances">Only Balances</option>
	</select>

	<span style="padding-left: 50px">
	<input type="date" size="10" maxlength="10" id="billing_list_end_date" title="Last billing date" value="@php echo date("Y-m-d"); @endphp" onchange="show_billing_dialog()" /></span> &nbsp; &nbsp; <button type="button" onclick="view_customer_bills('', '', event)">View All Transactions</button> &nbsp; <img id="printAllStatementsCtrl" src="img/document-print.png" style="vertical-align: middle; cursor: pointer" onclick="printAllStatements()" title="Print Statements" /> <img src="img/loading.gif" style="display: none" id="printAllStatementsIndicator" /> &nbsp; <img id="showReportsCtrl" src="img/chart.png" style="vertical-align: middle; cursor: pointer; height: 30px" onclick="show_reports_dialog()" title="Show Aging Report" /> 
	&nbsp; &nbsp;<input type="text" class="customer_search" maxlength="20" size="20" placeholder="Search Customer" />

	<!-- headings -->
	<div style="margin-top: 20px; width: 95%">
	<span style="font-weight: bold">Print</span><span style="padding-left: 30px; width: 75%; font-weight: bold">Customer</span><span style="float: right; width: 18%; font-weight: bold">Amount</span>
	</div>

	<div id="billing_container" style="overflow-x: hidden; overflow-y: scroll; margin-left: 10px; height: 350px; background: #ffffff; border: 1px solid #000000; width: 95%">
		<table id="billing_list" class="table table-striped">
			<tbody style="cursor: pointer">
				<tr><td>loading</td></tr>
			</tbody>
		</table>
	</div>

</div>

<!--
<style type="text/css">#billing_container td, th { border: 1px solid #000000 }</style>
-->
<!-- Customer tickets and billing list -->
<div id="customer_bill_dialog" style="display: none">

    <div id="billing_data_view">

        <div class="row">    
        <div class="col col-auto" id="customer_bill_name" style="font-size: 16pt"></div>
        <div class="col col-auto">
            <select id="customer_bill_job_id" class="form-select form-select-small">
            <option value="">&ndash; Choose Job &ndash;</option>
            </select> 
        </div>
        <div class="col col-auto">
        <img src="img/loading.gif" id="customer_activity_indicator" style="display: none" />
        </div>
        </div>

        <div class="row mt-2">

    <div class="col col-auto">
        <input type="date" class="form-control" id="bill_start_date" size="10" onchange="view_customer_bills($billing.customer_bill_customer_id.val())" value="{{ (new \DateTime())->modify('-1 month')->format('Y-m-d') }}" />&nbsp; to &nbsp; <input type="date" id="bill_end_date" class="form-control" size="10" value="@php echo date("Y-m-d"); //$week_ago; @endphp" onchange="view_customer_bills($billing.customer_bill_customer_id.val())" />
    </div>
    <div class="col col-auto">
        <select id="customer_bill_transaction_type" class="form-select form-select-small" onchange="view_customer_bills($billing.customer_bill_customer_id.val())">
        <option value="all" selected="selected">All Transactions</option>
        <option value="payments">Payments</option>
        <option value="returns">Returns</option>
        <option value="charges">Charges</option>
        <option value="paid_transactions">Cash/Check/CC</option>
        <option value="voids">Voids</option>
        </select>
    </div>
    <div class="col col-auto">
        <img id="print_statement_button" src="img/document-print.png" style="vertical-align: middle; cursor: pointer" onclick="printCustomerStatement(0)" title="Print Statement" /> &nbsp;
    </div>
    <div class="col col-auto">
        <img id="viewStatementCtrl" src="img/CCBill-20120401.png" style="width: 30px; height: 30px; vertical-align: middle; cursor: pointer" onclick="view_customer_statement()" title="View Statement" /> 
    </div>
    <div class="col col-auto">
        <!-- <button type="button" onclick="print_customer_statement()" id="print_statement_button">Print Statement</button>-->
        <input type="text" class="ticket_search" maxlength="15" size="15" placeholder="Find Ticket #" onkeyup="viewTicket(this.value, event)" />
    </div>
        
    </div>
    <input type="hidden" id="customer_bill_customer_id" />
        <!-- headings -->

        <table class="table table-sm table-hover">
            <thead>
            <tr id="ticket_heading_sort_row">
                    <th><a href="javascript:view_customer_bills('-1', 'id_sortimg')"><img src="img/arrow_down.gif" id="id_sortimg" />Ticket ID</a></th>
                    <th><a href="javascript:view_customer_bills('-1', 'customer_sortimg')"><img src="img/arrow_down.gif" id="customer_sortimg" />Customer Name</a></th>
                    <th><a href="javascript:view_customer_bills('-1', 'job_sortimg')"><img src="img/arrow_down.gif" id="job_sortimg" />Job</a></th>
                    <th><a href="javascript:view_customer_bills('-1', 'date_sortimg')"><img src="img/arrow_down.gif" id="date_sortimg" />Date</a></th>
                    <th><a href="javascript:view_customer_bills('-1', 'amount_sortimg')"><img src="img/arrow_down.gif" id="amount_sortimg" />Amount</a></th>
                    <th><a href="javascript:view_customer_bills('-1', 'type_sortimg')"><img src="img/arrow_down.gif" id="type_sortimg" />Type</a></th>
            </tr>
            </thead>
            <tbody class="table table-sm table-striped " id="ticket_tbody">

            </tbody>
        </table>

        <div id="customer_tickets_container">
        
        <table class="table table-sm table-striped table-hover">
            <tbody id="customer_tickets_list"></tbody>
        </table>
        <!-- individual ticket headings -->
        <div>
        <!--<table class="table table-sm" class="ticket_heading">

        </table>-->
        </div>


        <!--<div id="ticket_items_container" style="overflow-x: none; overflow-y: scroll; margin-left: 10px; height: 170px; background: #ffffff; border: 1px solid #000000; width: 95%; margin-top: 5px">-->
        <div id="billing_ticket_items_container" style="display: none">
            <button type="button" class="btn btn-link" onclick="closeTicket()">Close Ticket</button>

            <table class="table table-sm table-striped table-hover" id="billing_ticket_items_table">
                <thead>
                <tr><th>Ticket ID</th><th>Item ID</th><th>Quantity</th><th>Item Description</th><th>Price</th><th>Total</th></tr>
                </thead>
                <tbody id="ticket_items_list">
                </tbody>
            </table>
        </div>
    </div>

    </div> <!-- end billing view -->

    <div id="billing_statement_view" style="display: none">
            <button type="button" class="btn btn-link" onclick="$('#billing_data_view').show();	$('#billing_statement_view').hide()">Close Statement</button>

            <div id="statement_contents">
            </div>
    </div>

</div>

