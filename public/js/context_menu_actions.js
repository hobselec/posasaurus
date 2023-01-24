
function contextmenu_add_cart_item_description()
{

    // add the label of the item to confirm we right clicked the right line
    item_line = $cmenu.obj.find('td').eq(3).html();

    var itemId = $cmenu.id;
    var description = '';

    $pos.cart_item_description_barcode = itemId;
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

	axios.get(`/pos/billing/email-invoice/${ticketId}`).then((response) => {
		if(response.data.status)
			show_note('email sent')
		else
			Swal.fire('Error', 'Could not send e-mail.  Customer does not have an e-mail address.', 'error')
	}).catch((error) => {
		show_note("An error occurred")
	})

}


function contextmenu_void_transaction()
{
	// warn with ticket id
	let ticketId = $cmenu.id

	let displayTicketId = $cmenu.obj.find('td').eq(0).html()
	let customerName = $cmenu.obj.find('td').eq(1).html()	

	clear_ticket(ticketId, displayTicketId, customerName)

}


