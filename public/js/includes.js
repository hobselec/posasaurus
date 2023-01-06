
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


$(function() {

	//$('#ticket_display_id').html($('#ticket_id').val());

	$("#show_inactive").prop('checked', false); // don't want this in clear_pos() i don't think since

	clear_pos();

	$pos.barcode.focus();

	// setup ticket heading sorting
	$('#ticket_heading_sort_row img').each(function() { 
		if($(this).attr('id') != 'date_sortimg')
			$(this).css('display', 'none');
	});

	// force focus back to barcode
	$(document).on('click', function(evt) {
	
		if($editable_item.cur_item_id != '') // remove editable item in cart
		{
			console.log(evt.target.id)
			if(evt.target.id != 'cur_edit_cell' && evt.target.id != 'cur_edit_item') // only restore in an onblur to the cell
				restore_qty();
			else
				return false;
		} else if($editable_price.cur_item_id != '') // remove editable item in cart
		{
			if(evt.target.id != 'cur_edit_cell_price' && evt.target.id != 'auth_confirm') // only restore in an onblur to the cell
				restore_price();
			else
				return false;

		}

		if(evt.which != 3)
		{
		    $('.vs-context-menu').hide();

		  //  if($cmenu.row){
/*
		    $cmenu.row.css({'background-color':'#ffffff','color' : '#000000'});

		    if($cmenu.row_shade)
			 $cmenu.row.addClass('even').css({'background-color':'#dddddd','color' : '#000000'});
				
		    $cmenu.row_shade = 0;
		    $cmenu.prev_row = $cmenu.id.val();
		    $cmenu.row = false; // unset
		    $cmenu.id.val('');	
*/
		}

		//alert($('.vs-context-menu').css('display'));
		// restore the striping to the open context menu row
		//alert($cmenu.id.val());
		//if($('.vs-context-menu').css('display') != 'block' || ($cmenu.id.val() == '' && $cmenu.row) || ($cmenu.prev_id == $cmenu.id.val() && $cmenu.row) && $cmenu.row)
		//{

		//}

		if(evt.target.type != 'select-one' && evt.target.type != 'text')
		{
			if(!(evt.target.value == '' || evt.target.value > 0))
				$pos.barcode.focus();
		}
	});

	
	//
	//	AUTOCOMPLETE -- BARCODE
	//
	//$('.ui-autocomplete ul').css('height','100px'); // fix height

	$( "#barcode" ).autocomplete({ delay: 300, minLength: 3, source: 
		function(request, response)
		{
			if(!isNaN(request.term)) //avoid lookups on numbers for now
				return;
		
			axios.get('/pos/catalog/search/' + request.term).then((cdata) =>
			{
			//	response( $.map( data.items, function( item ) {
				//		return { label: item.name, value: item.barcode	}
						
					//	}));
					
				let myarr = new Array(cdata.length);
						
				let items = cdata.data
				for(let i = 0; i < items.length; i++)
				{
					tmpobj = new Object();
			
					tmpobj.label = items[i].name;
					tmpobj.value = items[i].id;
					tmpobj.extra = `${items[i].product_id} ${items[i].vendor_name} &ndash; ${items[i].manufacturer_id} &ndash; Qty: ${items[i].qty}`
				
					myarr[i] = tmpobj
				}
	
				response(myarr);
			
			})
			

		}, 	open: function() {
	
			$('.ui-autocomplete li').css('font-size','60%');
	
		}, select: function( event, ui ) {

			//alert(event.which);

			//$pos.barcode.val(ui.item.value);
			$pos.curItemId = ui.item.value

		//	event.stopPropagation(); // so enter key doesn't fire evet twice!

		//console.log(event.which);
			// don't need for 13 since check_enter() already handles that
			if(event.which == 1 || event.which == 0 ) //|| event.which == 13) // m
				lookup_item();


		}, focus: function( event, ui ) {

			var sel_item = event.target;

			$('.ac_extra').remove();

			$('.ui-autocomplete li div').each(function() {
			
			//alert($(this).html());

				if($(this).html() == ui.item.label)
					$(this).parent().append(`<div class="ac_extra" style="font-size: 90%; color: #666666">${ui.item.extra}</div>`)
			
				//$(this).html(ui.item.label + "<BR>" + ui.item.extra);
			
			
			});
			
			//alert(event.target.id);
			//(ui.item.extra);

		}
		
	});
	
	$('.customer_search').autocomplete({ minLength: 3, source:
		function(request, acResponse)
		{
		
			axios.get('/pos/customer/search?q=' + request.term).then((response) =>
			{
				let cdata = response.data

				myarr = new Array(cdata.length);
				
				let name = ''

				for(let i = 0; i < cdata.length; i++)
				{
					tmpobj = new Object();

					tmpobj.label = cdata[i].display_name;

					tmpobj.value = cdata[i].id;
				
					myarr[i] = tmpobj;
				}
	
				acResponse(myarr);

			})
			
			

		}, 	open: function() {
				
				//$('.ui-autocomplete li').css('font-size','60%');
				//$('.ui-autocomplete ul').css('height','100px');
				
		}, select: function( event, ui ) {

			if($pos.customer_dialog.css('display') == 'block')
			{
				$edit_customer.customer_sel.val(ui.item.value);
				edit_customer_info(ui.item.value);


			} else if($billing.dialog.css('display') == 'block')
			{
				view_customer_bills(ui.item.value);

			}
		}
	})

	// used for selecting the name on the ticket
	$( "#customer_ticket_search" ).autocomplete({ minLength: 3, source: 
		function(request, acResponse)
		{
		
			axios.get('/pos/customer/search?q=' + request.term).then((response) =>
			{

				let cdata = response.data

				let myarr = [];

				for(let i = 0; i < cdata.length; i++)
				{
					tmpobj = new Object();

					tmpobj.label = cdata[i].display_name;

					tmpobj.value = cdata[i];
				
					myarr.push(tmpobj);
				}

				acResponse(myarr);

			})
			

		}, 	open: function() {
				$('.ui-autocomplete li').css('font-size','70%');
				//$('.ui-autocomplete ul').css('height','100px');
				
		}, select: function( event, ui ) {

			$pos.jobs = ui.item.value.jobs
		
				// set customer name on the db entry
				axios.put('/pos/ticket/set-customer', { 
					'id' : $('#ticket_id').val(), 
					'customer_id' : ui.item.value.id }).then((response) =>
				{
// todo: figure out how to set job



						$pos.customer_display_name.html(ui.item.label);
						$pos.customer_job_display_name.html('').hide(); // hide job in case changed customer
						$pos.customer_id.val(ui.item.value.id);
						$pos.tax_exempt.val(response.data.ticket.customer.tax_exempt);
						$pos.allow_credit.val(response.data.ticket.customer.allow_credit);
						$pos.customer_ticket_search.val('');
						$pos.customer_ticket_search.hide();
						
	
						// reset tax depending on tax exempt


						if(response.data.ticket.customer.tax_exempt)
						{
							
							// get total, remove comma and parse as float
							var tmp = $pos.display_total.html();
							cur_total = parseFloat(tmp.replace(',', ''));
							
							tmp = $pos.tax.html();
							cur_tax = parseFloat(tmp.replace(',', ''));
							
							// subtract tax from total
							new_total = cur_total - cur_tax;
							new_total = new_total.toFixed(2);

							// tax to 0 and display new total
							$pos.tax.html('0.00');
							$pos.display_total.html(new_total);
	
						}
						
						// update the change-ticket select box with customer's name
						
						var new_label = "#" + response.data.ticket.display_id + " - " + ui.item.label;
						$('#open_transactions option').each(function() {
			
							if($(this).val() == $pos.ticket_id.val())
								$(this).html(new_label);
						});					

						//
						// show job listings if available
						//
						
						// populate the job box
					//	$.get('modify_customer.php', { 'get_customer_jobs' : '', 'job_cust_id' : ui.item.value }, function(response) 
						//{
						
							let htmlline = "<option value=\"\"> - Choose Job -</option>";
							htmlline += "<option value=\"\" disabled=\"disabled\"></option>";
							htmlline += "<option value=\"\">No Job Specified</option>";
							
							for(let i = 0; i < $pos.jobs.length; i++)
							{
								htmlline += "<option value=\"" + $pos.jobs[i].id + "\">" + $pos.jobs[i].name + "</option>";
							}
							
							if($pos.jobs.length > 0)
								$pos.pay_job_id.html(htmlline).show();
							//else // if no jobs, then let the search box re-appear
						//	{
								$pos.customer_ticket_search.css('color', '#999999').show();
						//	}

						//}, 'json');
						
						//$pos.pay_job_id.show();
					
					
				}).catch((error) => {
					show_note("Could not set customer!");
				})
		}
		
	});

	$('#shutdown_dialog').dialog({ title : 'POS Shutdown', 
		autoOpen: false, modal : true, resizable : false, 
		draggable : false, width: 330, height: 400
	});


	// Additional display fixes

	//$payments.payment_recv_date.datepicker({'duration' : 0});
	//$billing.bill_start_date.datepicker({'duration' : 0});
	//$billing.bill_end_date.datepicker({'duration' : 0});
	//$('#ui-datepicker-div').css('font-size','80%');


});



function show_note(msg, type='info')
{
    Swal.fire('Alert', msg, type)

}



function chg_ticket(ticket_id)
{
	
	axios.get('/pos/ticket/' + ticket_id).then((responsePayload) => {


		let response = responsePayload.data

				cancel_payment(1);
				
				$pos.subtotal.html(response.subtotal.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.tax.html(response.tax.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.display_total.html(response.total.toLocaleString('en-US', { minimumFractionDigits: 2}));
				
				//var tmp_subtotal = response.subtotal.replace(',', '');
				
				add_to_cart(response.items);
				
				$pos.ticket_id.val(response.id);
				$pos.ticket_display_id.html(response.display_id);
				if(response.job)
				{
					$pos.pay_job_id.val(response.job.id)
					$pos.customer_job_display_name.html(response.job.name);
				}
				
				$pos.open_transactions.val('');
				$pos.pay_button.removeClass('disabled')
				$pos.special_options_button.removeClass('disabled')
				
				// set the inputs used to modify the discount/freight/resale
				/*
				if(response.discount > 0)
					var tmppct = 100 - ((parseFloat(tmp_subtotal) - parseFloat(response.discount))/parseFloat(tmp_subtotal)) * 100;
				else
					var tmppct = 0;
					
				//	alert(parseFloat(response.subtotal) - parseFloat(response.discount));
		//alert(tmppct);

				
				if(tmppct == 100)
					tmppct = '';
				else
					tmppct = tmppct.toFixed(0);

				$('#discount_percentage').val(tmppct);
				*/
		
				$pos.discount.val(response.discount.toFixed(2));
				
				$('#freight_number').val(response.freight.toFixed(2));
				$('#labor_number').val(response.labor.toFixed(2));

				// show discount in main totals				
				if(response.discount > 0)
				{

					$pos.discount_icon.show();
					$pos.discount_display_total.html("$ -" + response.discount.toLocaleString('en-US', { minimumFractionDigits: 2}));
			
				}
			
				if(response.freight > 0)
				{
					$pos.freight_icon.show();
					$pos.freight_display_total.html("$ " + response.freight.toLocaleString('en-US', { minimumFractionDigits: 2}));
				}
				if(response.labor > 0)
				{
					$pos.labor_icon.show();
					$pos.labor_display_total.html("$ " + response.labor.toLocaleString('en-US', { minimumFractionDigits: 2}));
				}
				
				if(response.resale == 1)
					$('#is_resale').prop('checked', true);
				else
					$('#is_resale').prop('checked', false);

				if(response.refund == 1)
				{
				    $pos.refund_switch.prop('checked', true);
				    $pos.refund_indicator.html('- Refund');
				} else
 			 	    $pos.refund_indicator.html('');

				// these will be set from the db eventually i think
				$pos.customer_display_name.html(response.customer.display_name);
				$pos.customer_id.val(response.customer.id);
				$pos.tax_exempt.val(response.customer.tax_exempt);
				$pos.allow_credit.val(response.customer.credit);
				$pos.cash_given.val('');
				$pos.check_no.val('');
				$pos.cc_trans_no.val('');

				$pos.recv_by_input.val(response.recv_by);

				//$item_descriptions = response.item_descriptions;

				if(response.recv_by != '' && response.recv_by != null)
				{
					$pos.recv_by_container.show()
				    $pos.recv_by_name.html(response.recv_by)

				}
			

			$pos.barcode.focus();
		}).catch(() => {  
			show_note("Cannot load ticket")
		});	
	
	//add_to_cart(subtotal, cart);
	
	//$('#barcode').focus(); // seems to get focus back on its own...

}




	
function lookup_item() {
	
	
	if($pos.barcode.val() == '')
		return false;
		
	//  barcode lookup

	axios.put('/pos/ticket/add-item', { itemId : $pos.curItemId, ticketId : $pos.ticket_id.val() }).then((response) => {

				$pos.subtotal.html(response.data.ticket.subtotal.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.tax.html(response.data.ticket.tax.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.display_total.html(response.data.ticket.total.toLocaleString('en-US', { minimumFractionDigits: 2}));
			
				if($pos.ticket_id.val() == '')
				{
					// new ticket
					$pos.ticket_id.val(response.data.ticket.id);
					//$pos.ticket_display_id.html(response.data.ticket[0].display_id);

					let optionRow = `<option value="${response.data.ticket.id}">#${response.data.ticket[0].display_id} - NONAME</option>`
						
					$pos.open_transactions.append(optionRow);
						
				}// else if($pos.ticket_id.val() != response.ticket_id)

				add_to_cart(response.data.ticket.items);
				//chg_ticket($pos.ticket_id.val());
				
				$pos.barcode.val('');


		}).catch(() => {
			show_note("Item not found");
	})
		
}

function clear_ticket(ticket_id)
{
	var clear_pos_vars = 0;

	if(ticket_id == null && $pos.ticket_id.val() == '')
		return false;
	
	// ticket_id is the currently open ticket, as opposed to voiding through the billing page
	if(!(ticket_id > 0))
	{
		ticket_id = $pos.ticket_id.val();
		clear_pos_vars = 1;
	}

	axios.delete('/pos/ticket/void/' + ticket_id).then((response) => {
		
		// clear display
		show_note("Ticket Voided");
			
		// remove from select box
		$('#open_transactions option').each(function() {
			
			if($(this).val() == $pos.ticket_id.val())
				$(this).remove();
		});

		if(clear_pos_vars == 1)
			clear_pos();
		
	}).catch((e) => {

		show_note("Cannot void ticket!");
	})

	
}



// after entering password, return to action
function auth_return()
{


			if($pos.authenticate_action == 'editable_price')
				edit_price($editable_price.pre_auth_cell, $editable_price.pre_auth_item_id);
			else if($pos.authenticate_action == 'void_ticket')
				clear_ticket($cmenu.void_ticket_id);

			else if($pos.authenticate_action == 'edit_catalog')
				edit_cat_row($catalog.pre_auth_button_obj, $catalog.pre_auth_barcode)
				
	
		auth_cancel();
		

}



function print_end_report()
{

	
	var counted_cash = $pos.closing_cash.val();
	var counted_checks = $pos.closing_checks.val();
	
	if(counted_cash.indexOf('.') == '-1' && $pos.useAutoDecimal && counted_cash != 0)
		counted_cash /= 100;
		
	if(counted_checks.indexOf('.') == '-1' && $pos.useAutoDecimal && counted_checks != '0')
		counted_checks /= 100;
	

	if((isNaN(counted_cash) || isNaN(counted_checks)) || counted_cash == '' || counted_checks == '')
	{
		alert("Please provide the amount of checks and cash");
		return false;
	}

	axios.post('/pos/journal/close', { 'cash' : counted_cash, 'checks' : counted_checks, 'printLabel' : 1 }).then((response) => {
	
			$pos.closing_checks.blur();
	
			//alert(data_xml);
			
	}).catch(() => {
		show_note("Error")
	})

}


var t;
function add_decimals(box, evt, callback)
{
	var str = box.value;
	
	if(window.event) // IE
	{
		keynum = evt.keyCode;
		if(keynum == 13 && callback)
			window[callback]();
	}
	else if(evt.which == 13 && callback)			//alert(evt.target);
	{

		//if(callback == 'post_transaction')
		//	return false;
		
		window[callback]();
		//save_opening_balance();

	}

	if(!$pos.useAutoDecimal)
		return;

	$auto_decimal.box = box;
	$auto_decimal.count++;
	$auto_decimal.str = str;
	
	count = $auto_decimal.count;
	
	clearTimeout(t);
	t = setTimeout("add_decimals_go(count)", 400);

}

function add_decimals_go(cur_count)
{

	// let delay prevent firing in rapid keystrokes
	if($auto_decimal.count != cur_count)// || $auto_decimal.count == 0)
		return;
//	alert($auto_decimal.count + ' and ' + cur_count);	
	$auto_decimal.count = 0;
	
	//box = $auto_decimal.box;
	str = $auto_decimal.str;
//	alert(str);alert(box);
//alert('hi');
	var box = '';

	if(isNaN(str))
	{
		if(str.length > 1)
			box = str.substr(0, str.length-1);
		else
			box = '';
			
		return;
	}
	
	str = str.replace(".", "");
	
	var origvalue = str;
	var offset = 0;
	
//alert(str);
	//$('#debug_container').html(str.length + ' and ' + $pos.previous_value.length);

	if(str.length == 2 )//&& $pos.previous_value.length == 3 && $pos.previous_decimal)
	{
		//alert('replace');
		box = str.replace(".", "");
		//box.value = str;
		//$pos.previous_value--; // remove the decimal
	offset = -1;
	} else if(str.length == 3 ) //&& ($pos.previous_value.length == 2 || $pos.previous_value.length == 4))
	{
		left_side = str.substr(0, 1);
		right_side = str.substr(1);
		box = left_side + '.' + right_side;
		//	alert('2');
		//$pos.previous_value++; // since we add the decimal
		//alert(left_side);
		$pos.previous_decimal = 1;
		offset = 1;
	} else if(str.length > 3)
	{
		left_side = str.substr(0, str.length-2);
		right_side = str.substr(str.length-2) 
//	alert('3');
		box = left_side + '.' + right_side;
		$pos.previous_decimal = 1;
	} else
		box = str;
		
	$pos.previous_value = origvalue;// + offset;

	$auto_decimal.box.value=box;
}



function show_shutdown_dialog()
{
	$('.posdlg').hide();
	$(".ui-dialog-titlebar-close").show();
	$('#shutdown_dialog').dialog('open'); 

}


function print_weekly_report()
{
    if($reports.start_date.val() == '' || $reports.end_date.val() == '')
    {
		$reports.weekly_report_fields.show();
		return;
    }


    $.get('weekly_report.php', { start : $('#report_start_date').val(), end : $('#report_end_date').val() }, function(response) {

	

    });
}
