//const { default: axios } = require("axios");

function contextmenu_add_cart_item_description()
{

    // add the label of the item to confirm we right clicked the right line
    item_line = $cmenu.obj.find('td').eq(3).html();

    var itemId = $cmenu.id;
    var description = '';

    $pos.cart_item_description_barcode.val(itemId);
    $pos.cart_item_description_label.html(item_line);

    for(let i = 0; i < $pos.cartItems.length; i++)
    {
		let item = $pos.cartItems[i]

		if(item.id == itemId)
		{
			description = item.notes;
			break;
		}
    }

    //$dialogs.cart_item_description_dialog.show();
	$('#item_description_dialog').modal('toggle')
    $pos.cart_item_description_name.val(description).focus();
  
  // alert($cmenu.id.val());

}



function contextmenu_print_invoice()
{

	let ticketId = $cmenu.id
	
	location.href=`/pos/billing/print-invoice/${ticketId}`

}

function contextmenu_email_invoice()
{

	let ticketId = $cmenu.id

	axios.get(`/pos/billing/email-invoice/${ticketId}`).then(() => {
		show_note('email sent')
	})

}


function contextmenu_void_transaction()
{
	// warn with ticket id
	let ticketId = $cmenu.id

	// the id of the row is the same as the real id, however, we 
	// need to reference it by the display id
	$('#customer_tickets_list tr').each(function() {
		
		if($(this).attr('id') == 'printTicket_' + ticketId)
			{
			display_ticket_id = $(this).find('td').eq(0).html();
			return;
			}
	})
	
	if(confirm("Void ticket " + display_ticket_id + "?"))
	{
	
		var ticket_ident = $cmenu.id.val();
	
		var tmp = ticket_ident.split("_");
	
		// store this for later because auth_return() will need it
		$cmenu.void_ticket_id = tmp[1];
	}

}


