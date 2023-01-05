
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

	$payments.payment_recv_customer_id.val('')

	$('#recv_payment_screen').dialog({ title : 'Payment', 
		autoOpen: false, modal : true, resizable : false, 
		draggable : false, width: 520, height: 630, open: function() {

			// set payment date to current time
			let now = new Date()
			let dateAdjusted = new Date(now.getTime() - now.getTimezoneOffset()*60000)

			document.getElementById('payment_recv_date').value = dateAdjusted.toISOString().substring(0,16)

			$pos.barcode.prop('disabled',true);
			$payments.payment_recv_search_name.focus();
		}, close: function() {
			$payments.payment_recv_customer_id.val('');
			$payments.payment_recv_search_name.val('');

			$payments.payment_recv_search_name.show();

			$payments.payment_recv_display_name.html('');
			$payments.payment_recv_display_balance.html('')
			$payments.payment_recv_job_id.html('');
			$payments.payment_recv_job_id.hide()
		}
	});

	$( "#payment_recv_search_name" ).autocomplete({ minLength: 3, delay : 500, source: 
		function(request, acResponse)
		{

			axios.get('/pos/customer/search?q=' + request.term + '&showBalances=1').then((response) =>
			{
				let cdata = response.data

				let myarr = new Array(cdata.length);
				
				for(let i = 0; i < cdata.length; i++)
				{
					tmpobj = new Object();

					tmpobj.label = cdata[i].display_name;

					tmpobj.value = cdata[i].id;

					tmpobj.customer = cdata[i]
				
					myarr[i] = tmpobj;
				}
	
				acResponse(myarr);

			})
			

		}, 	open: function() {
				//$('.ui-autocomplete li').css('font-size','60%');
				//$('.ui-autocomplete ul').css('height','100px');
				
		}, select: function( event, ui ) {

				$payments.customerSelection = ui.item

				$payments.payment_recv_customer_id.val(ui.item.value);
				$payments.payment_recv_search_name.val(ui.item.label);

				$payments.payment_recv_search_name.hide();

				$payments.payment_recv_display_name.html(ui.item.label);

				//ui.item.value = ''; // replace ID var with label so it goes in the box instead

				var payment_job_html = "<option value=\"0\">- Choose Job -</option>";

				for(let i = 0; i < ui.item.customer.jobs.length; i++)
				{
					let job = ui.item.customer.jobs[i]
					payment_job_html += `<option value="${job.id}">${job.name}</option>`
				}
					
				$payments.payment_recv_display_balance.html(`<i>Balance:</i> &nbsp; $ ${ui.item.customer.balance}`)
					
				$payments.payment_recv_job_id.html(payment_job_html);					
				$payments.payment_recv_job_id.show();

				
		
			//alert($payments.payment_recv_search_name.val());

		}
		
	});
	
});


function save_payment_recv()
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
			
			$('#recv_payment_screen').dialog('close')
			
	}).catch(() => {
		show_note("Could not save payment.  Verify the form was completed correctly.")
	})	


}
