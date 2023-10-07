
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


// EDITABLE QTY FUNCTIONS
//
// change a qty input box back to a text qty
export function restore_qty()
{

	$editable_item.cur_cell.html($editable_item.cur_qty);
	$editable_item.cur_item_id = '';
	$editable_item.cur_qty = '';
	$editable_item.cur_cell.removeAttr('id');
	$editable_item.cur_cell = '';
	$editable_item.edit_contents = '';


}

export function edit_qty(cell_obj, item_id)
{
	// check that the cell is not a number, 
	//item id is not currently stored, and this is only editable field open
	if(isNaN($editable_item.cur_qty) || $editable_item.cur_item_id != '' || $editable_price.cur_item_id != '')
		return false;

	$editable_item.cur_qty = cell_obj.html();

	cell_obj.attr('id', 'cur_edit_cell'); // give cell an ID for keeping track of

	$editable_item.cur_cell = cell_obj; // store this for reference later

	$editable_item.edit_contents = `<input id="cur_edit_item" type="number" value="${$editable_item.cur_qty}" 
									onkeyup="check_update_qty(event, this.value)" />`

	cell_obj.html($editable_item.edit_contents);
	
	//$('#cur_edit_item').focus().select();

	$editable_item.cur_item_id = item_id;

}

// update the cell qty we are editing on enter key press
export function check_update_qty(evt, qty)
{

	if(evt.key != 'Enter')
		return

			
	if(qty >= 1)
	{
		$editable_item.cur_qty = qty;
	
		modify_item($editable_item.cur_item_id, 'chg', qty)
	} else
	{

		show_note("Cannot set quantity less than 1.  Use the remove item button or adjust the price.");
	}
	
	restore_qty();
	$pos.barcode.focus();

}

// remove, increment, decrement item quantity in cart
export function modify_item(item_id, action, qty = '', price = '')
{
	let method = 'put'
	if(action == 'del')
		method = 'delete'

	let postData = { 'ticketId' : $pos.ticket_id.val(), itemId : item_id }

	if(qty != '')
		postData.qty = qty
	if(price != '')
		postData.price = price

	// request to modify
	axios({method: method,
			url : '/pos/ticket/item',
			data : postData
	 }).then((responseData) =>
		{
			let response = responseData.data.ticket

			$pos.subtotal.html(response.subtotal.toLocaleString('en-US', { minimumFractionDigits: 2}));
			$pos.tax.html(response.tax.toLocaleString('en-US', { minimumFractionDigits: 2}));
			$pos.display_total.html(response.total.toLocaleString('en-US', { minimumFractionDigits: 2}));
			$pos.paymentDialogTotal.html($pos.display_total.html())

			add_to_cart(response.items);

			
		}).catch((error) => {
			show_note("Problem modifying the item.  Please restart the transaction");
		})
	
	

}



export function add_to_cart(cart)
{

	$pos.cart.html('');

	$pos.cartItems = cart

	let tmpcart = '';
	let zeroItemWarning = 0; // warn if an item cost is zero

	for(let i = 0; i < cart.length; i++)
	{

		tmpcart += `<tr id="${cart[i].id}" data-itemid="${cart[i].id}">
			<td>
				<img onclick="modify_item(${cart[i].id}, 'del', $(this))" style="cursor: pointer; width: 12px; height: 12px" src="img/del.png" />
			</td>
			<td class="qty" onclick="edit_qty($(this), ${cart[i].id})">${cart[i].qty}</td>
			<td style="cursor: default">
			${cart[i].name}
			</td>
			<td class="qty" onclick="edit_price($(this), ${cart[i].id})">
			${cart[i].price.toFixed(2)}
			</td>
			<td class="qty">
			${cart[i].amount.toFixed(2)}
			</td>
		 </tr>`
		
		if(cart[i].amount == 0 && i == 0)
			zeroItemWarning++;
		

	}

	//$pos.cart.append(row);
	$pos.cart.html(tmpcart);
	
	$('#cart tr').each(function() {
	// this is may need to be applied only to the td of the item name, not the whole row...
		
		$(this).vscontext({menuBlock: 'vs-context-menu', menuType : 'transaction_items'});

	});

	if(zeroItemWarning > 0)
		show_note("There is an item priced at zero in the cart.");

	//
	//var scroll_distance = document.documentElement.scrollTop;
//alert(scroll_distance);
	//$pos.cart.top = scroll_distance;
	//$pos.cart_container.scrollTop = $pos.cart_container.scroll;
///	.,,malert($pos.cart_container.scroll);
		
}



// EDITABLE PRICE FUNCTIONS
//
// change a qty input box back to a text qty
export function restore_price()
{

	$editable_price.cur_cell.html($editable_price.cur_price);
	$editable_price.cur_item_id = '';
	$editable_price.cur_price = '';
	$editable_price.cur_cell.removeAttr('id');
	$editable_price.cur_cell = '';
	$editable_price.edit_contents = '';


}

//
// edit_price of items in the cart
//
export function edit_price(cell_obj, item_id)
{

	//	$editable_price.pre_auth_cell = cell_obj;
	//	$editable_price.pre_auth_item_id = item_id;

	
	// check that the cell is not a number,
	// item id is not current stored, and this is only editable field open
	if(isNaN($editable_price.cur_price) || $editable_price.cur_item_id != '' || $editable_item.cur_item_id != '')
		return false;

	$editable_price.cur_price = cell_obj.html();
	
	cell_obj.attr('id', 'cur_edit_cell_price'); // give cell an ID for keeping track of

	$editable_price.cur_cell = cell_obj; // store this for reference later

	$editable_price.edit_contents = "<input id=\"cur_edit_item_price\" maxlength=\"11\" type=\"text\" style=\"width: 50px\" value=\"" + $editable_price.cur_price + "\" onkeyup=\"check_update_price(event, this.value)\" />";

	cell_obj.html($editable_price.edit_contents);
	
	$('#cur_edit_item_price').focus().select();

	$editable_price.cur_item_id = item_id;			
			

	
}

// update the cell qty we are editing on enter key press
export function check_update_price(evt, price)
{

	if(evt.key != 'Enter')
		return

	// so event doesn't propogate on 'enter key'
	$('#cur_edit_item_price').blur();

	if(isNaN(price))
	{
		show_note("Not a valid number");
		restore_price();
		return false;
	}

	if(price.indexOf('.') == '-1' && $pos.useAutoDecimal)
		price /= 100;

		
	price = parseFloat(price);


	$editable_price.cur_price = price.toFixed(2);
			
	modify_item($editable_price.cur_item_id, 'price', '', $editable_price.cur_price) 

	//restore_price();
	$pos.barcode.focus();

}

export function save_cart_item_description()
{
	$pos.save_cart_item_description_button.prop('disabled', true);

    var itemId = $pos.cart_item_description_barcode;
    var description = $pos.cart_item_description_name.val();

    axios.put('/pos/ticket/item-description', { ticket_id : $pos.ticket_id.val(), itemId : itemId, description : description }).then((response) => {


		$('#item_description_dialog').modal('toggle')

	    show_note("Description saved");
	    $dialogs.cart_item_description_dialog.hide();
	  //  $dialogs.cart_item_description_name.hide();


	    for(let i = 0; i < $pos.cartItems.length; i++)
 	    {
			let item = $pos.cartItems[i]

			if(item.id == itemId)
			{
				item.notes = description
				break;
			}
    	}

		$pos.cart_item_description_name.val('');
		$pos.save_cart_item_description_button.prop('disabled', false);

		$('#item_description_dialog').modal('hide')

    }).catch(() => {
		$('#item_description_dialog').modal('hide')
	})

}


export function clear_pos() {

	$pos.cart.html('');
	$pos.subtotal.html('');
	$pos.tax.html('');
	$pos.ticket_display_id.html('');
	$pos.ticket_id.val('');
	$pos.customer_display_name.html('');
	$pos.customer_id.val('');
	$pos.display_total.html('');
	$pos.paymentDialogTotal.html('')
	$pos.cash_given.val('');
	$pos.check_no.val('');
	$pos.cc_trans_no.val('');
	$pos.tax_exempt = false;
	$pos.pay_button.prop('disabled', false);
	$pos.refund_switch.prop('checked', false);
	$pos.open_transactions.val('').prop('disabled', false);
	$pos.check_no.prop('disabled', false);
	$edit_customer.customer_job_edit.val('');
	$pos.allow_credit = false
	$pos.discount.val('0');
	$pos.freight.val('0');
	$pos.labor.val('0');
	
	$pos.pay_job_id.val('');
	$pos.customer_job_display_name.html('');

	$catalog.add_item_dialog.hide();
	$catalog.new_item_name.val('');
	$catalog.new_item_price.val('');
	
	$pos.printReceiptChkbox.prop('checked', true);
	$pos.refund_indicator.html('');

	$pos.recv_by_name.html('')
	$pos.recv_by_container.hide()
	$pos.recv_by_input.val('')

	$pos.pause_button.prop('disabled', false);

	$pos.jobs = []

	cancel_payment(1);

	//$pos.barcode.focus();
	$pos.paymentMethodDisplay.html('')
	$pos.paymentMethod = ''
	$pos.postPaymentButton.attr('disabled', true)
//alert($pos.customer_display_name.html());	

}


export function chg_ticket(ticket_id)
{
	clear_pos()
	
	axios.get('/pos/ticket/' + ticket_id).then((responsePayload) => {


		let response = responsePayload.data

				cancel_payment(1);
				
				$pos.subtotal.html(response.subtotal.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.tax.html(response.tax.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.display_total.html(response.total.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.paymentDialogTotal.html($pos.display_total.html())
				
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
				$pos.tax_exempt = response.customer.tax_exempt;
				$pos.allow_credit = response.customer.credit;
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
			

			//$pos.barcode.focus();
		}).catch(() => {  
			show_note("Cannot load ticket")
		});	
	
	//add_to_cart(subtotal, cart);
	
	//$('#barcode').focus(); // seems to get focus back on its own...

}




	
export function lookup_item(item = {}) {
	
	
	if($pos.barcode.val() == '')
		return false;
		
	//  barcode lookup instead of results from search
	if(Object.keys(item).length == 0)
	{
		if($pos.barcode.val().length <= 6) // doit best 6 digit barcode
			item = { 'type' : 'wholesaler_barcode', 'id' : $pos.barcode.val() }
		else if($pos.barcode.val().length > 6)
			item = { 'type' : 'sku', 'id' : $pos.barcode.val() }
	}


	axios.put('/pos/ticket/add-item', { item : item, ticketId : $pos.ticket_id.val() }).then((response) => {

				$pos.subtotal.html(response.data.ticket.subtotal.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.tax.html(response.data.ticket.tax.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.display_total.html(response.data.ticket.total.toLocaleString('en-US', { minimumFractionDigits: 2}));
				$pos.paymentDialogTotal.html($pos.display_total.html())
			
				if($pos.ticket_id.val() == '')
				{
					// new ticket
					$pos.ticket_id.val(response.data.ticket.id);
					//$pos.ticket_display_id.html(response.data.ticket[0].display_id);

					let optionRow = `<option value="${response.data.ticket.id}">#${response.data.ticket.display_id} - NONAME</option>`
						
					$pos.open_transactions.append(optionRow);

					chg_ticket(response.data.ticket.id)
						
				} else // else if($pos.ticket_id.val() != response.ticket_id)
					add_to_cart(response.data.ticket.items);
				//chg_ticket($pos.ticket_id.val());
				
				$pos.barcode.val('');


		}).catch(() => {
			show_note("Item not found");
	})
		
}

export function clear_ticket(ticketId = '', displayTicketId = '', customerName = '')
{
	var clearPosVars = false;
	let forNameString = ''

	if(ticketId == '')
	{
		// ticket_id is the currently open ticket, as opposed to voiding through the billing page

		ticketId = $pos.ticket_id.val()
		displayTicketId = $pos.ticket_display_id.html()
		clearPosVars = true
	}
	
	if(customerName  != '')
		forNameString = ` for ${customerName}`
	
	Swal.fire({
		title: 'Please Confirm',
		text: `Void ticket #${displayTicketId}${forNameString}?`,
		icon: 'warning',
		showCancelButton: true
	}).then((result) => {
	
		if(result.isConfirmed) {

			
			const getData = async() => {

				try {
					const response = await axios.delete('/pos/ticket/void/' + ticketId)

					if(!response)
						throw new Error()
					
					if(response.data.status)
					{
						show_note("Ticket Voided");

						// remove from select box
						$('#open_transactions option').each(function() {
							
							if($(this).val() == $pos.ticket_id.val())
								$(this).remove()
						});

						$billing.ticket_tbody.find('tr').each(function() {

							if($(this).data('ticketid') == ticketId)
							{
								let cell = $(this).find('td').eq(5)
								cell.html('VOID')
							}
						})

						// clear display
						if(clearPosVars)
							clear_pos()

					} else
						show_note("Problem", "Cannot void ticket!", "warning")

					return response
			
				} catch(e) {
					show_note("Error", "Cannot void ticket!", "error")
				
				}
			} 
			getData()


		}
	}).catch(e => {


	})

	
}
