
/*

This file is part of Primitive Point of Sale.

    Primitive Point of Sale is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Primitive Point of Sale is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Primitive Point of Sale.  If not, see <http://www.gnu.org/licenses/>.

*/


function show_billing_dialog()
{


//	$pos.open_transactions.prop('disabled', true);
	$pos.barcode.prop('disabled', true);
	$payments.payment_recv_button.prop('disabled', true);

	axios.get('/pos/billing/list/' + $billing.billing_display_types.val() + '?endDate=' + $billing.billing_list_end_date.val()).then((response) => {

		let rows = response.data;

		let billingTableRows = ''
		let checked

		$billing.dataRows = rows
		let isCredit = '', displayClass = ''

		for(let i = 0; i < rows.length; i++)
		{
			rows[i].print_statement ? checked = 'checked' : checked = ''

			if(rows[i].rawBalance < 0)
			{
				isCredit = `CR `
				displayClass = 'text-success'
			} else
			{
				isCredit = ''
				displayClass = 'text-danger'
			}

			billingTableRows += `<tr id="printAcct_${rows[i].id}" data-customerid="${rows[i].id}" onclick="view_customer_bills(${rows[i].id}, '', event)">
								<td>
								<input id="billing${i}" type="checkbox" ${checked} />
								<label for="billing${i}" class="nice-label"></label>
								&nbsp; ${rows[i].name}
								</td>
								<td style="text-align: right; padding-right: 10px" class="${displayClass}">
									<div style="display: inline">${isCredit}$ </div>
									<div style="display: inline; text-align: right">${rows[i].balance}</div>
								</td>
								</tr>`
		}

		$billing.list.html(billingTableRows);


//		$('#billing_list_end_date').datepicker({'duration' : 0});

		$("#billing_list tbody>tr").each(function() {
	
			$(this).vscontext({menuBlock: 'vs-context-menu', menuType : 'balances'});

		});	

		
	})

}


function close_billing_dialog()
{

	$billing.dialog.hide();
	$pos.open_transactions.prop('disabled', false);
	$pos.barcode.prop('disabled', false);
	$payments.payment_recv_button.prop('disabled', false);

}

function close_customer_bill_dialog()
{

	$billing.customer_bill_dialog.hide();
	$billing.customer_bill_customer_id.val('');
	
}


// load a single ticket ID
//
function viewTicket(id, evt)
{
	if(window.event) // IE
		keynum = evt.keyCode;
	else if(evt.which) // Netscape/Firefox/Opera
		keynum = evt.which;

	
	if(keynum != 13)
		return;

	// limit the search to the customer currently viewing
	axios.get('/pos/billing/ticket/' + id + '?limit_customer_id=' + $billing.customer_bill_customer_id.val()).then((response) => {
		
		let ticket = formatTicketRow(response.data.ticket)
	
		//$billing.customer_bill_name.html(response.data.customer.display_name);
		//$billing.customer_tickets_list.html(tickets);
		$billing.ticket_tbody.html(ticket)
		$billing.ticket_items_list.html('');


		$("#ticket_tbody tr").each(function() {
			
			$(this).vscontext({ menuBlock: 'vs-context-menu', menuType : 'tickets' });
		
		});	

	}).catch(() => {
		show_note("No ticket was found")
	});


}

// show tickets under a customer name 
//
function view_customer_bills(customer_id = '', sort_type = '', evt)
{
	//$billing.customer_tickets_container.html('');
	$('#customer_activity_indicator').show();

	//if(sort_type == false)
	if(evt != undefined)
	{

		if(evt.target.classList.contains('nice-label') || evt.target.type == 'checkbox')
		    return;
	
	}

	if(sort_type == undefined || sort_type == '')
		sort_type = 'date_sortimg';

	$billing.customer_bill_name.html('')
	// blank indicates 'no change' to viewing customer
	if(customer_id != '-1') 
	{// || customer_id == 0)
		$billing.customer_bill_customer_id.val(customer_id);
	}
	// determine sorting image indicator
	$('#ticket_heading_sort_row img').each(function() { 
		if($(this).attr('id') != sort_type)
			$(this).css('display', 'none');
		else
			$(this).show();
	});

	// hide the 
	if(customer_id == 0)
	{
	    $billing.print_statement_button.hide();
	    $billing.viewStatementCtrl.hide();
	} else
	{
	    $billing.print_statement_button.show();
	    $billing.viewStatementCtrl.show();

	}

	let transactionType = $billing.customer_bill_transaction_type.val()
	let startDate = $billing.bill_start_date.val()
	let endDate = $billing.bill_end_date.val()
	let customerId = $billing.customer_bill_customer_id.val()

	axios.get(`/pos/billing/customer/${customerId}?transaction_type=${transactionType}&start_date=${startDate}&end_date=${endDate}&sort_type=${sort_type}`).then((response) => {


			let tickets = ''
			if(response.data.tickets.length == 0)
				tickets = `<tr><td colspan="6" style="width: 650px; text-align: center; margin-left: auto; margin-right: auto; font-weight: bold">No tickets found</td></tr>`

			$billing.tickets = response.data.tickets

			$billing.ticket_items_container.hide()

			let ticket, ticketTotal, ticketJob, typeIndicator
			for(let i = 0; i < response.data.tickets.length; i++)
			{
				ticket = response.data.tickets[i]

				tickets += formatTicketRow(ticket)
			}


			//$billing.customer_tickets_list.html(tickets);
			$billing.ticket_tbody.html(tickets)
			$billing.ticket_items_list.html('');

			let jobs = response.data.jobs
			let jobsHtml = ''

			if(customerId == '')
			{
				$billing.customer_bill_job_id.hide()
				$billing.customer_bill_name.html('')
			} else
			{
				for(let i = 0; i < jobs.length; i++)
					jobsHtml += `<option value="${jobs[i].id}">${jobs[i].name}</option>`
			
				$billing.customer_bill_job_id.html(`<option value="">&ndash; Choose Job &ndash;</option>` + jobsHtml).show()
console.log(jobsHtml)
				$billing.customer_bill_name.html(response.data.customer.display_name).show()
			}

			// changed to image, this doesn't work now, but it won't print anyway
//			if($billing.customer_bill_customer_id.val() == 0) // no printing except for single customers
//				$billing.print_statement_button.prop('disabled', true);
//			else
//				$billing.print_statement_button.prop('disabled', false);
		

			$billing.customer_bill_dialog.dialog('open')	
			

			
			$("#ticket_tbody tr").each(function() {
			
				$(this).vscontext({ menuBlock: 'vs-context-menu', menuType : 'tickets' });
			
			});	
			
			$('#customer_activity_indicator').hide();
	
	})


}


function load_ticket_transactions(ticketId, parent_row)
{
	// reset onclick highlighting
	$('#ticket_tbody tr').each(function() {
		$(this).removeClass('bg-info')
	});
	
	parent_row.addClass('bg-info')
	$('#ticket_tbody tr:not([class=bg-info])').hide()
	
	for(let i = 0; i < $billing.tickets.length; i++)
	{
		

		if($billing.tickets[i].id == ticketId)
		{
			let ticketItemsHtml = ''

			for(j = 0; j < $billing.tickets[i].items.length; j++)
			{
				let item = $billing.tickets[i].items[j]
				let total = item.price * item.qty
				let ticketId = $billing.tickets[i].display_id

				ticketItemsHtml += `<tr>
				<td>${ticketId}</td>
				<td>${item.id}</td>
				<td>${item.qty}</td>
				<td>${item.name}</td>
				<td>${item.price.toLocaleString('en-US', { minimumFractionDigits: 2})}</td>
				<td>${total.toLocaleString('en-US', { minimumFractionDigits: 2})}</td>
				</tr>
				`

			}

			$billing.ticket_items_list.html(ticketItemsHtml)

			break
		}
	}

	$billing.ticket_items_container.show()


}



// show the bill html on the screen
function view_customer_statement()
{
	$('#customer_activity_indicator').show();
	
	let id
	if($billing.customer_bill_customer_id.val() > 0)
		id = $billing.customer_bill_customer_id.val();
	else
		id = $cmenu.id
		
	// check that dates are not out of sequence

	
	// encode date slashes
	let date = $billing.billing_list_end_date.val();

	
	axios.get(`/pos/billing/statement/${id}?startDate=${$billing.bill_start_date.val()}&endDate=${$billing.bill_end_date.val()}`).then((response) => {


		$('#billing_data_view').hide()
		$('#billing_statement_view').show()
	    $billing.statement_contents.html(response.data.html);
	    
	    $('#customer_activity_indicator').hide();

	});
	
}

//
function show_reports_dialog()
{
	$('#only_show_balances').is(':checked') ? only_show_balances = 1 : only_show_balances = 0;

    axios.get(`/pos/billing/aging-report?onlyShowBalances=${only_show_balances}&endDate=${$billing.billing_list_end_date.val()}`
		).then((response) => {

		$('#reports_content').html(response.data.report);

    })

    $billing.reports_dialog.dialog('open');

}

function print_aging_report()
{
    $.get('aging.php', { print : 1 });
}


//
// update the billing listing totals next to the provided customer_id
function refresh_listing_total(customer_id, new_balance)
{
   var searchId = 'printAcct_' + customer_id;

// loop through billing list and update the balance
    $('#billing_list tr').each(function() {

	if($(this).attr('id') == searchId)
	{
		if(new_balance.substr(0, 1) == '-')
		{
		balance_prefix = 'CR $';
		balance = new_balance.substr(1);
		} else
		{
		balance_prefix = '$';
		balance = new_balance;
		}

		$(this).find('td').eq(1).find('div').eq(0).html(balance_prefix);
		$(this).find('td').eq(1).find('div').eq(1).html(balance);

		// make flash red for a second
		$dialogs.tmp_row = $(this).find('td').eq(1);
		$dialogs.tmp_row.css('color', 'red');
		window.setTimeout("$dialogs.tmp_row.css('color', '#000000');", 1000);

		return false;
	}

    });

}

function closeTicket()
{
	$('#ticket_tbody tr').each(function() {
		$(this).removeClass('bg-info')
	});
	
	
	$('#ticket_tbody tr').show()
	$billing.ticket_items_container.hide()

}

// print_tickets = 1|0 to print tickets along with statement
function printCustomerStatement(print_tickets = 0)
{

//	$('#customer_activity_indicator').show();
	let id

	if($billing.customer_bill_customer_id.val() > 0)
		id = $billing.customer_bill_customer_id.val();
	else
	{ // context menu action
		id = $cmenu.id
		
	}


	location.href = `/pos/billing/print-statement/${id}?startDate=${$billing.bill_start_date.val()}&endDate=${$billing.bill_end_date.val()}&printTickets=${print_tickets}`

}

// helper function used for loading tickets and searching tickets

function formatTicketRow(ticket)
{
	let typeIndicator = '';

	let ticketTotal = ticket.total.toLocaleString('en-US', { minimumFractionDigits: 2})
	ticket.job ? ticketJob = ticket.job.name : ticketJob = ''

	if(ticket.display_type == 'PAYMENT' || ticket.display_type == 'discount')
		typeIndicator = ' &ndash; '
	else if(ticket.refund )
		typeIndicator = 'R ';


	return `<tr onclick="load_ticket_transactions(${ticket.id}, $(this))" data-ticketid="${ticket.id}" id="printTicket_${ticket.id}">
	<td style="width: 120px">${ticket.display_id}</td>
	<td style="width: 200px">${ticket.customer.display_name}</td>
	<td style="width: 120px">${ticketJob}</td>
	<td style="width: 140px">${ticket.display_date}</td>
	<td style="width: 100px; text-align: right">${typeIndicator}$ ${ticketTotal}</td>
	<td style="padding-left: 70px; width: 124px">${ticket.display_type}</td>
	</tr>`
}

// for service charge, discount, or cash refunds
function saveBillingAdjustment()
{
	let customerId = $billing.adjustment.customerId

	let data = {
		customerId : customerId,
		jobId : $billing.adjustment.jobId.val(),
		format : $("input[name='billing_adjustment_refund_format']:checked").val(),
		type : $("input[name='billing_adjustment_type']:checked").val(),
		amount : $billing.adjustment.amount.val()
	}


	axios.post('/pos/billing/adjustment', data).then((response) => {

		// todo: update customer balance
		// refresh_listing_total(customerId, response.newBalance)

		$billing.adjustment.dialog.dialog('close')


	}).catch(() => {
		show_note("Error adding adjustment")
	})

}