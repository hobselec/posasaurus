
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

//const { default: axios } = require("axios");

// EDITABLE QTY FUNCTIONS
//
// change a qty input box back to a text qty
function restore_qty()
{

	$editable_item.cur_cell.html($editable_item.cur_qty);
	$editable_item.cur_item_id = '';
	$editable_item.cur_qty = '';
	$editable_item.cur_cell.removeAttr('id');
	$editable_item.cur_cell = '';
	$editable_item.edit_contents = '';


}

function edit_qty(cell_obj, item_id)
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
function check_update_qty(evt, qty)
{
	if(window.event) // IE
		keynum = evt.keyCode;
	else if(evt.which) // Netscape/Firefox/Opera
		keynum = evt.which;

		if(keynum == 13)
		{
			//alert('update');
			// do update
			
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
}

// remove, increment, decrement item quantity 0in cart
function modify_item(item_id, action, qty = '', price = '')
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
			

			add_to_cart(response.items);

			
		}).catch((error) => {
			show_note("Problem modifying the item.  Please restart the transaction");
		})
	
	

}



function add_to_cart(cart)
{

	$pos.cart.html('');

	let tmpcart = '';
	let zeroItemWarning = 0; // warn if an item cost is zero

	for(let i = 0; i < cart.length; i++)
	{

		tmpcart += `<tr id="${cart[i].id}">
		<td><img onclick="modify_item(${cart[i].id}, 'del', $(this))" style="cursor: pointer; width: 12px; height: 12px" src="img/del.png" /></td>
		 <td class="qty" onclick="edit_qty($(this), ${cart[i].id})">${cart[i].qty}</td>
		 <td>
		 <!-- remove since may be inefficient/unreliable
			<table class="incrctrl">
			<tr class="nostripe">
				<td>
				<img onclick="modify_item(${cart[i].id}, 'add', $(this))" style="cursor: pointer" src="img/up.png">
				</td>
		 	</tr>
			<tr class="nostripe">
				<td>
				<img onclick="modify_item(${cart[i].id}, 'sub', $(this))" style="cursor: pointer" src="img/down.png">
				</td>
			</tr>
			</table>
			-->
		 </td>
		 <td style="padding-left: 60px; width: 300px; cursor: default">
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
function restore_price()
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
function edit_price(cell_obj, item_id)
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

	$editable_price.edit_contents = "<input id=\"cur_edit_item_price\" maxlength=\"11\" type=\"text\" style=\"width: 50px\" value=\"" + $editable_price.cur_price + "\" onkeyup=\"add_decimals(this, event, false); check_update_price(event, this.value)\" />";

	cell_obj.html($editable_price.edit_contents);
	
	$('#cur_edit_item_price').focus().select();

	$editable_price.cur_item_id = item_id;			
			

	
}

// update the cell qty we are editing on enter key press
function check_update_price(evt, price)
{
	if(window.event) // IE
		keynum = evt.keyCode;
	else if(evt.which) // Firefox/Opera
		keynum = evt.which;

	if(keynum != 13)
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

function save_cart_item_description()
{
	$pos.save_cart_item_description_button.prop('disabled', true);

    var barcode = $pos.cart_item_description_barcode.val();
    var description = $pos.cart_item_description_name.val();

    $.post('update_ticket.php', { item_description : 1, ticket_id : $pos.ticket_id.val(), barcode : barcode, description : description }, function(response) {



	if(response.status)
	{
	    show_note("Description saved");
	    $dialogs.cart_item_description_dialog.hide();
	  //  $dialogs.cart_item_description_name.hide();

	    var found = 0; // see if we need to store this description entry or if it already exists

	    for(i = 0; i < $item_descriptions.length; i++)
 	    {
		if($item_descriptions[i].barcode == barcode)
		{
		    $item_descriptions[i].description = description;
		    found = 1;
		    break;
		}
    	     }

	    if(!found)
		$item_descriptions.push({'barcode' : barcode, 'description' : description});

	} else
	    alert("Could not save the description");

	$pos.cart_item_description_name.val('');
	$pos.save_cart_item_description_button.prop('disabled', false);

    });

}
