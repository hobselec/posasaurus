
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



export function save_payment_recv()
{

	var datePaid = $payments.payment_recv_date.val()
	var pay_type = $payments.payment_recv_method.val()

	// check num
	var extra_info = $payments.payment_recv_extra_info.val()

	var amount = $payments.payment_recv_amt.val()
	var customer_id = $payments.payment_recv_customer_id.val()

	amount = amount.replace(/^0+/,"");// remove preceding zeros if given
	amount = amount.replace(',','')

	if(amount.indexOf('.') == '-1' && $pos.useAutoDecimal)
		amount /= 100

	
	if(pay_type == '')
	{
		show_note("Please enter the type of payment")
		return false
	}
	if(customer_id == '')
	{
		show_note("Please choose a customer")
		return false
	}
	if(datePaid == '')
	{
		show_note("Please provide the date")
		return false
	}

/*
	if(amount.length - amount.indexOf('.') != 3 || isNaN(amount))
	{
		show_note("Price is not in correct format.  Must include 2 digits beyond the decimal point");
		return false;
	}*/


	//let tmp_min = $payments.payment_recv_minute.val().toString();
	//if(tmp_min.length != 2 || isNaN($payments.payment_recv_minute.val()))
	//{
	//    show_note("Minutes has not been given")
	//    return false
	//}


	
	axios.post('/pos/payment', 
		{ customerId : customer_id, date : datePaid, payType : pay_type, amount : amount, 
		extraInfo : extra_info, jobId : $payments.payment_recv_job_id.val() }).then((response) => {

			show_note("Payment Received")
				
			$payments.payment_recv_amt.val('')

			/*
			cash_back = amt_given - total_sale;
			cash_back = cash_back.toFixed(2)
			if(cash_back > 0)
				alert("Cash back: " + cash_back);
			*/
			
			// e-mail?
			//print_receipt(response.ticket_id, amount, '');
			
			//$('#recv_payment_screen').dialog('close')
			closePaymentModal()
			
	}).catch(() => {
		show_note("Could not save payment.  Verify the form was completed correctly.")
	})	


}

export function closePaymentModal() {
		
	$payments.payment_recv_customer_id.val('');
	$payments.payment_recv_search_name.val('');

	$payments.payment_recv_search_name.show();

	$payments.payment_recv_display_name.html('');
	$payments.payment_recv_display_balance.html('')
	$payments.payment_recv_job_id.html('');
	$payments.payment_recv_job_id.hide()

	const div = document.querySelector('#recv_payment_screen');
    const modal = Modal.getInstance(div);    
  
	modal.hide()


}