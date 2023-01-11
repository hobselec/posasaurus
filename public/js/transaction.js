
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
/*
function show_payment_methods()
{
	if($pos.cart.html() == '')
	{
		show_note("There are no items in the cart");
		return false;
	}

	$pos.pay_button.prop('disabled', true); //hide();
	$pos.payment_methods.show();
	$pos.customer_ticket_search.show();


}*/


function apply_payment_specialoptions()
{
//	is_resale : 
// freight_number : $('#freight_number'),
// discount_percentage    discount_number
// 
	var resale = 0;
	
	var discount = $pos.discount.val();
	var freight = $pos.freight.val();
	var labor = $pos.labor.val();

	if((isNaN(discount) && discount != '') || discount < 0)
	{
		show_note("Choose a value for the discount");
		return false;
	
	}
	
	if(isNaN(freight) && freight != '')
	{
		show_note("Freight is not a valid number");
		return false;
	}

	if(isNaN(labor) && labor != '')
	{
		show_note("Labor is not a valid number");
		return false;
	}
	
	/*
	if($('#discount_percentage').val() > 100 || $('#discount_percentage').val() < 0 || $('#discount_number').val() < 0)
	{
		alert("Discount % must be between 0 and 100.  Discount number must be less than subtotal.");
		return false;
	
	}
	*/


	if(discount > parseFloat($pos.subtotal.html().replace(',','')))
	{
		show_note("Discount can not exceed the subtotal!");
		return false;
	
	}
	
	// another if() to check discount_number?
	

	if($('#is_resale').attr('checked'))
	{
		resale = 1;
		$pos.tax.html('0.00');
	} else
	{
		resale = 0;
		var tmp_tax = parseFloat($pos.subtotal.html().replace(',','') - discount) * parseFloat($('#tax_rate').val());

		$pos.tax.html(tmp_tax.toFixed(2));
	}
	
	if(discount == '')
		$pos.discount.val('0');
	if(freight == '')
		$pos.freight.val('0');
	
		//alert($pos.tax)

	if(discount.indexOf('.') == '-1' && $pos.useAutoDecimal)
		$pos.discount.val(discount / 100);

	if(freight.indexOf('.') == '-1' && $pos.useAutoDecimal)
		$pos.freight.val(freight / 100);

	if(labor.indexOf('.') == '-1' && $pos.useAutoDecimal)
		$pos.labor.val(labor / 100);

		$('#loading_recv_by').show();

	// save the special options given
	axios.put('/pos/ticket/set-options', { id : $pos.ticket_id.val(),
		 discount : $pos.discount.val(), 
		 resale : resale, 
		 //'tax_exempt' : $pos.tax_exempt.val(), 
		 freight : $pos.freight.val(), 
		 labor : $pos.labor.val(), 
		 refund : $pos.refund_switch.prop('checked'),
		 recv_by : $pos.recv_by_input.val()
		// 'subtotal' : $pos.subtotal.html().replace(',', '') 
		}).then((response) => {

			$('#loading_recv_by').hide();
			// adjust discount
			if(response.data.discount > 0)
			{
				// show discount
				$pos.discount_icon.show();
				$pos.discount_display_total.html("$ -" + response.data.discount.toLocaleString('en-US', { minimumFractionDigits: 2}));
			
			} else
			{
				$pos.discount_icon.hide();
				$pos.discount_display_total.html('');
			}
			
			// freight
			if(response.data.freight > 0)
			{
				$pos.freight_icon.show();
				$pos.freight_display_total.html("$ " + response.data.freight.toLocaleString('en-US', { minimumFractionDigits: 2}));
			} else
			{
				$pos.freight_icon.hide();
				$pos.freight_display_total.html('');
			}
			
			// labor
			if(response.data.labor > 0)
			{
				$pos.labor_icon.show();
				$pos.labor_display_total.html("$ " + response.data.labor.toLocaleString('en-US', { minimumFractionDigits: 2}));
			} else
			{
				$pos.labor_icon.hide();
				$pos.labor_display_total.html('');
			}
			if(response.data.ticket.recv_by != '')
    		{    			 
    	    	$pos.recv_by_name.html(response.data.ticket.recv_by);
				$pos.recv_by_container.show()
    		} else
				$pos.recv_by_container.hide()
			//var total =  parseFloat($pos.subtotal.html().replace(',','')) - parseFloat(data.discount) + parseFloat(data.freight) + parseFloat($pos.tax.html());
		

			//total = total.toFixed(2);
		

		//	$pos.subtotal.html(response.subtotal);

					
			if(response.data.ticket.refund)
				$pos.refund_indicator.html('- Refund');
			else
				$pos.refund_indicator.html('');


			$pos.tax.html(response.data.ticket.tax);
			$pos.display_total.html(response.data.ticket.total);		


			
	}).catch((error) => {
		$('#loading_recv_by').hide();
		show_note("Problem setting special options")
	})


}


// AUTOMATICALLY SET THE DISCOUNT PERCENT WHILE TYING IN THE NUMBER BOX
// not used now since percentage is removed for now
/*
function calculate_discount_percentage()
{
	var dn = $('#discount_number');
	var dp = $('#discount_percentage');
	
	var tmp_subtotal = parseFloat($pos.subtotal.html().replace(',',''));

	if(dn.val() > 0)
	{
		// (data.subtotal - data.discount)/data.subtotal * 100;
		var new_pct = 100 - ((tmp_subtotal - dn.val())/tmp_subtotal) * 100;
		
		if(((tmp_subtotal - dn.val())/tmp_subtotal) * 100 < 1)
			new_pct = '';
		else
			new_pct = new_pct.toFixed(0);
	} else
		var new_pct = '0';
	
	dp.val(new_pct);
	

//alert
}


// AUTOMATICALLY SET THE DISCOUNT AMT WHILE TYPING IN THE PERCENTAGE BOX
function calculate_discount_number()
{
	var dn = $('#discount_number');
	var dp = $('#discount_percentage');
	
	var tmp_subtotal = parseFloat($pos.subtotal.html());

	
	//alert(tmp_subtotal);
	
	var new_number = tmp_subtotal * dp.val() * .01;
	new_number = new_number.toFixed(2);
	
	dn.val(new_number);


}
*/

function choose_pay_job_id()
{
	// need to save this to the server when selected as well as show up on the screen

	var option = document.getElementById('pay_job_id');
	
	var job_name = option.options[option.selectedIndex].text;
	var job_id = option.value;
	
	if(job_id == '')
		return false;

	// save the chosen job id
	axios.put('/pos/ticket/set-customer', { 
		'id' : $pos.ticket_id.val(), 
		'job_id' : job_id }).then((response) =>
		{

				if(job_id != '')
				{
					$pos.customer_job_id.val(job_id);
					$pos.customer_job_display_name.html(job_name).show();
				} else
				{
					$pos.customer_job_id.val('');
					$pos.customer_job_display_name.html('');
				
				}


		}).catch((error) => {
			show_note("Problem setting job name");
		
		})


}

function show_payment(type)
{

	$pos.postPaymentButton.attr('disabled', true)

	if($pos.display_total.html() == '')
	{
		show_note("There is nothing listed on the ticket");
		return false;
	}

	if($pos.customer_id.val() == '' && (type != 'cash'))
	{
		show_note("Please enter the customer's name");
		return false;
	} else if(type == 'acct' && !$pos.allow_credit.val())
	{

		show_note("The customer is not setup for credit");
		return false;

	}

	let paymentTypes = {cc : 'Credit Card', acct : 'Account', cash : 'Cash', check : 'Check'}
	$pos.paymentMethodDisplay.html(paymentTypes[type])
	$pos.paymentMethod = type


	$pos.barcode.attr('disabled',true); // interferes with the focus

	//$('#postpayment_button').show();

	$('#payment_take').show();
	
	if(type == 'acct')
	{
		$pos.take_cc.hide()
		$pos.take_check.hide()
		$pos.take_cash.hide()
	}

	if(type == 'cash' || type == 'check')
	{
		if($pos.refund_switch.prop('checked')) // no check # for return
			$pos.cash_given.val($pos.display_total.html().replace(',', ''));
	
		$pos.take_cash.show()
		$('#cash_given').focus();
		$pos.take_cc.hide();
		$pos.take_check.hide()
	}
	
	if(type == 'check')
	{
		$pos.take_check.show()
		
		if($pos.refund_switch.prop('checked')) // no check # for return
			$pos.check_no.prop('disabled', true);

		$pos.take_cash.show()
		$pos.check_no.focus();
		$pos.take_cc.hide();
	}
		
	if(type == 'cc')
	{
		$pos.cc_trans_no.val('');
		$pos.cc_trans_no.focus();
		$pos.take_check.hide()
		$pos.take_cc.show();
		$pos.take_cash.show()
	}
		
	$pos.postPaymentButton.attr('disabled', false)

}

// accepts argument whether to clear the special options

function cancel_payment(clear_special)
{
	$('#payment_methods').hide();
	$('#payment_take').hide();
	$('#take_cash').hide();
	$('#take_check').hide();
	$('#take_cc').hide();

	// these 3 get hidden when doing charge
	$('#pay_cc').show();
	$('#pay_check').show();
	$('#pay_cash').show();
	
	$pos.pay_job_id.hide().html('');

	$dialogs.cart_item_description_dialog.hide();

	// clear inputs
	$pos.cash_given.val('');
	$pos.check_no.val('');	
	$pos.cc_trans_no.val('');
	
	// charge inputs
	$('#accts').show();
	$('#cancel_payment').show();
	
	$pos.pay_button.attr('disabled', false); //show();

	$pos.barcode.attr('disabled', false);

	if(clear_special)
	{
		$pos.tax_exempt.val('0');

		$pos.refund_switch.prop('checked', false); //show();
		$pos.discount_icon.hide();
		$pos.discount_display_total.html('');
		$pos.freight_icon.hide();
		$pos.freight_display_total.html('');
		$pos.labor_icon.hide();
		$pos.labor_display_total.html('');
	}

}


function post_transaction()
{
	var check_no = $pos.check_no.val();

	let paymentType = $pos.paymentMethod
	
	var amt_given_str = $pos.cash_given.val();
	amt_given_str = amt_given_str.replace(/^0+/,""); // no 0.xx

	var amt_given = 0.0;
	
	var cash_back = '';
	var refund = 0;

	
	var total_sale_str = $pos.display_total.html().replace(/^\s+/,""); // remove spaces
	total_sale_str = total_sale_str.replace(',', '');
	total_sale_str = total_sale_str.replace(/^0+/,"");
	
	var total_sale = parseFloat(total_sale_str);
	
	var recv_by = $pos.recv_by_name.html();
	
	//alert(total_sale);
//	alert($pos.refund_switch.prop('checked'));
	
	$pos.refund_switch.prop('checked') ? refund = true : refund = false;


	if(amt_given_str.indexOf('.') == '-1' && $pos.useAutoDecimal)
		amt_given = parseFloat(amt_given_str) / 100;
	else
		amt_given = parseFloat(amt_given_str);

	// verify amount received for refund
	if(amt_given != total_sale)
	{
		if(refund == 1 && paymentType != 'acct') // 
		{
			$pos.cancel_button.attr('disabled', false);
			show_note("Amount given must equal sale total");
			return false;
		}
	}

	if(total_sale >= 1000000)
	{
		alert("The total sale must be under $1,000,000.  If you need a higher limit you must enlarge the total field in the database table.");
		$pos.cancel_button.attr('disabled', false);
		return false;
	
	}

	// determine payment type
	if($('#take_cc').is(':visible')) // CC
	{
		if(amt_given != total_sale)
		{
			show_note("Amount charged must equal sale total");
			$pos.cancel_button.attr('disabled', false);
			return false;
		}
	
		//show_note("cc");
		//payment_type = 'cc';
	
	} else if(!$('#take_check').is(':visible') && paymentType != 'acct') // cash
	{
		// add decimals if only a two digit number'

	
		if(isNaN(amt_given))
		{
			show_note("Invalid amount");
			$pos.cancel_button.attr('disabled', false);			
			return false;
		} else if(amt_given < total_sale)
		{
			show_note("Amount given is less than the total"); // + ' ' + amt_given + ' and  ' + total_sale);
			$pos.cancel_button.attr('disabled', false);
			return false;
		
		}
		
		cash_back = amt_given - total_sale;
		cash_back = cash_back.toFixed(2)
		
		//paymentType = 'cash';
		// insert into document, the cash back
		//show_note(cash_back);
	
	
	} else if($('#take_check').is(':visible')) // check
	{
		if(amt_given != total_sale)
		{
			show_note("Check amount must equal sale total");
			$pos.cancel_button.attr('disabled', false);
			return false;
		}
		
		
		if(check_no == '' && !refund)
		{
			show_note("Please enter the check number");
			$pos.cancel_button.attr('disabled', false);
			return false;
		}
	
	
		if(refund)
			check_no = 0;
	

	}
	// check again since they can select customer after choosing payment method
	if($pos.paymentMethod == 'acct' && !$pos.allow_credit.val())
	{
		show_note("The customer is not setup for credit");
		return false;
	}


	axios.post('/pos/ticket/submit',
	 { id : $pos.ticket_id.val(), 
	 amount_given : amt_given, 
	 check_no : check_no,
	  cc_trans_no : $pos.cc_trans_no.val(), 
	  payment_type : $pos.paymentMethod, 
	  subtotal : $pos.subtotal.html(), 
	  tax : $pos.tax.html(),
	   total : total_sale, 
	}).then((response) => {
	

			if(cash_back > 0)
				show_note("Cash back: " + cash_back);
				
			if(refund && payment_type != 'acct') // if refund to acct, say nothing
				show_note("Customer Refund: " + total_sale);
		
			show_note("Transaction Complete!");

			// print receipt
			//if($pos.printReceiptChkbox.attr('checked') && payment_type != 'acct')
			//	print_receipt($pos.ticket_id.val(), amt_given, cash_back);

			// remove from open tickets
			$('#open_transactions option').each(function() {
			
				if($(this).val() == $('#ticket_id').val())
					$(this).remove();
			});
			
			clear_pos();

			// todo: disable the save button and hide the dialog when done

	})
	.catch((error) => {
		show_note("Could not finalize the transaction");
	})

}
