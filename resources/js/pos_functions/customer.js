
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

export function add_customer_form()
{
	clear_customer_inputs(); // clears inputs
	$edit_customer.save_customer_button.prop('disabled', false);
	
	// unhighlight the select box so to not confuse editing the customer selected
	// changed from removeProp on 2020-10-26
	$('#customer_listing').val('')

	$('#customer_edit_cell').show();
	$('#customer_jobs_cell').hide();

	var input_type = '';

	$('#customer_edit_cell input').each(function() {
	
		input_type = $(this).attr('type');
		
		if(input_type == 'text')
			$(this).css('background','#ffff99');
	});
	
	$edit_customer.edit_active.prop('checked', true);
	$edit_customer.id = '0'
	$edit_customer.edit_last_name.focus();


}

//
// load the customer info in the input boxes for editing
//
export function edit_customer_info(id)
{
	clear_customer_inputs()

	if(id == '' || id == $edit_customer.id) // no noname
	{
		$edit_customer.customer_edit_cell.hide()
		return false
	}

	// this loads the job list if the job list is loaded in the right pane
	if($edit_customer.customer_jobs_cell.css('display') != 'none')
	{
		load_customer_job_list(id);
		return false;
	}

	$('#customer_edit_cell input').each(function() {
	
		$(this).css('background','#ffffff').attr('disabled' , true);
	
	});

	$edit_customer.customer_edit_cell.show()

	$edit_customer.edit_active.prop('checked', false);
	$edit_customer.edit_tax_exempt.prop('checked', false);
	$edit_customer.edit_allow_credit.prop('checked', false);

	// READ the customer details
	axios.get('/pos/customer/' + id).then((response) => {


		response = response.data

		//$('#edit_last_name').val(response.last_name);
		$edit_customer.edit_last_name.val(response.last_name);
		$edit_customer.edit_first_name.val(response.first_name);
		
		$edit_customer.edit_company.val(response.company);
		$edit_customer.edit_mi.val(response.mi);
		
		$edit_customer.edit_address.val(response.address);
		$edit_customer.edit_address2.val(response.address2);
		$edit_customer.edit_city.val(response.city);		
		$edit_customer.edit_state.val(response.state);
		$edit_customer.edit_zip.val(response.zip);
		$edit_customer.phone.val(response.phone);
		$edit_customer.phone_ext.val(response.phone_ext);	
		$edit_customer.email.val(response.email)
	
	
		$edit_customer.id = id


		if(response.credit)
		{
			$edit_customer.edit_allow_credit.prop('checked', true);
			

	
		}
		if(response.tax_exempt == 1)
			$edit_customer.edit_tax_exempt.prop('checked', true);	
		if(response.active == 1)
			$edit_customer.edit_active.prop('checked', true);
		if(response.use_company == 1)
			$edit_customer.edit_listby_company.prop('checked', true);
		else
			$edit_customer.edit_listby_lastname.prop('checked', true);		
		
		$edit_customer.save_customer_button.prop('disabled', false);
		
		$('#customer_edit_cell input').attr('disabled', false)
		
		$edit_customer.edit_first_name.focus();
	
	})


}


// SAVE INFO
//
export function save_customer_info()
{
	var data = new Object();
	var tmp_label = '';

	$edit_customer.edit_active.is(':checked') ? data.active=1 : data.active=0;
	$edit_customer.edit_tax_exempt.is(':checked') ? data.tax_exempt=1 : data.tax_exempt=0;
	$edit_customer.edit_allow_credit.is(':checked') ? data.credit=true : data.credit=false;
	

	if($edit_customer.edit_listby_company.is(':checked'))
		data.use_company = 1;
	else
		data.use_company = 0;
	
	data.id = $edit_customer.id
	data.last_name = $edit_customer.edit_last_name.val();
	data.first_name = $edit_customer.edit_first_name.val();
	data.mi = $edit_customer.edit_mi.val();
	data.company = $edit_customer.edit_company.val();
	
	data.address = $edit_customer.edit_address.val();
	data.address2 = $edit_customer.edit_address2.val();	
	data.city = $edit_customer.edit_city.val();
	data.state = $edit_customer.edit_state.val();
	data.zip = $edit_customer.edit_zip.val();
	
	data.phone = $edit_customer.phone.val();
	data.phone_ext = $edit_customer.phone_ext.val();
	data.email = $edit_customer.email.val()

	// regex the names, basically to avoid spaces
	var name_regex = /^[A-Za-z]+/;
	var company_regex = /^[A-Za-z0-9]/;
	
	var tmpname = data.last_name;
	var tmpco = data.company;

	if((!tmpname.match(name_regex) && data.use_company == 0 ) || (!tmpco.match(company_regex) && data.use_company == 1))
	{
		alert("You must give the customer a name");
		return false;
	}


	axios.post('/pos/customer', { customer: data }).then((response) => {
	

		show_note("Information Saved");
		
		// if the current customer has an open ticket, reload the ticket
		if($pos.customer_id.val() == response.data.id)
		{
			var tmpticket = $pos.ticket_id.val();
			
			clear_pos(tmpticket);
			chg_ticket(tmpticket);
				//$pos.allow_credit = data.allow_credit;
				
				//$pos.tax_exempt = data.tax_exempt
			
			
		}
		// set for new customer
		if($edit_customer.id == 0)
			$edit_customer.id = response.data.id
			
		syncCustomerListChanges(response.data)
		$edit_customer.customer_sel.val(response.data.id) // reset if select box reorders

	
	}).catch(() => {
		show_note("There was an error.")
	})
	
	
	//alert(response.active);


}

export function customerDialog(options)
{
	if(options == 'reload')
	{
		load_customer_list(options);
		clear_customer_inputs()
		return false;
	}
	
	$('#customer_listing option:selected').removeAttr('selected');

		//$pos.open_transactions.prop('disabled',true);
		$pos.barcode.prop('disabled',true);
		$('#customer_jobs_cell').hide();


		$('.posdlg').hide();
		
		$edit_customer.id = ''

		document.getElementById('customer_dialog').style.display = 'block';

		if($edit_customer.id == '')
			$edit_customer.save_customer_button.prop('disabled',true);
	

		if($edit_customer.list_is_loaded == 0 || options == 'reload')
			load_customer_list(options);
		
		//else
		//	document.getElementById('customer_dialog').style.display = 'block';
		


}

export function customer_jobs_dialog()
{

	var cur_cust_id = $edit_customer.customer_sel.val();

	if($edit_customer.list_is_loaded == 0) // || options == 'reload')
		load_customer_list();
	
	$('#customer_edit_cell').hide();
	$('#customer_jobs_cell').show();
	
	if(cur_cust_id > 0)
		load_customer_job_list(cur_cust_id);


}

// edit customer jobs available
export function load_customer_job_list(customer_id)
{
	$edit_customer.customer_job_edit.val('').prop('disabled', true);
	$edit_customer.customer_job_edit.css('background', '#ffffff');

	axios.get('/pos/customer/jobs/' + customer_id).then((response) => {
	
		let customer_html = "<option value=\"\"> - Choose Job -</option>";

				
		for(let i = 0; i < response.data.length; i++)
		{
			customer_html += "<option value=\"" + response.data[i].id + "\">" + response.data[i].name + "</option>";
		}

		customer_html += "<option value=\"\" disabled=\"disabled\"></option>";
		customer_html += "<option value=\"new\">Add New . . .</option>";
					
		$edit_customer.customer_job_listing.html(customer_html);

	
	})

}

export function load_edit_job()
{
	var option = document.getElementById('customer_job_listing');

	if(option.value == '')
		return;
	else if(option.value == 'new')
	{
		$edit_customer.customer_job_edit.css('background','#ffff99').prop('disabled', false).val('');

	} else
	{
		$edit_customer.customer_job_edit.val(option.options[option.selectedIndex].text);
		$edit_customer.customer_job_edit.css('background','#ffffff').prop('disabled', false);

	}

	$edit_customer.customer_job_edit.focus();

}

export function save_job_edit()
{
	var job_name = $edit_customer.customer_job_edit.val();

	var cust_id = $edit_customer.customer_sel.val();
	
	if(job_name == 'No Job Specified')
	{
		alert("This name cannot be used as a job name");
		return false;
	}
	
	axios.post('/pos/customer/job',
	 { customer_id : cust_id, 
		job_name : job_name, 
		job_id : $edit_customer.customer_job_listing.val() }).then((response) => {
	
		if(response.data.status)
		{

			show_note("Job Saved");
			
			// reload the list
			load_customer_job_list(cust_id);
	
		
			$edit_customer.customer_job_edit.val('').css('background','#ffffff');
		}
	
	
	})

}

// set the customer list or update if given data of a customer
export function syncCustomerListChanges(data = null)
{
	
	let customer_html = '<option value=""></option>';

	let customerList = $edit_customer.customers

//	customer_html = "<option value=\"\">&ndash; Choose Customer &ndash;</option>";
//	customer_html += "<option value=\"\" disabled=\"disabled\"></option>";

	// update list
	if(data)
	{
		let reorder = false
		let found = false
		for(let i = 0; i < customerList.length; i++)
		{

			if(customerList[i].id == data.id)
			{
				if(customerList[i].display_name != data.display_name || customerList[i].use_company != data.use_company)
				{
					reorder = true
				}
				customerList[i] = data
				found = true
				break
			}
		}
		if(!found)
		{
			customerList.push(data)
			reorder = true
		}

		if(reorder)
		{
			customerList.sort((a,b) => {
				if(a.display_name < b.display_name)
					return -1
				if(a.display_name < b.display_name)
					return 0
			})
			$edit_customer.customers = customerList

		} else
			return

	}
	
	for(let i = 0; i < customerList.length; i++)
		customer_html += `<option value="${customerList[i].id}">${customerList[i].display_name}</option>`

		
	$edit_customer.customer_sel.html(customer_html);


}
/*
export function close_customerdialog()
{
	clear_customer_inputs();
	document.getElementById('customer_dialog').style.display = 'none';
//	$pos.open_transactions.prop('disabled', false);
	$pos.barcode.prop('disabled', false);
	
	$edit_customer.id = ''

	// clear the value give for a new entry, but other IDs can stay when we close the screen
	if($edit_customer.id == '0')
		$edit_customer.id = ''

	// make inputs white if yellowed from clicking "add new customer"
	$('#customer_edit_cell input').each(function() {
	
		input_type = $(this).attr('type');	
		
		if(input_type == 'text')
			$(this).css('background','#ffffff');
	});

}*/

export function clear_customer_inputs() 
{
	$edit_customer.edit_last_name.val('');
	$edit_customer.edit_first_name.val('');
		
	$edit_customer.edit_company.val('');
	$edit_customer.edit_mi.val('');
		
	$edit_customer.edit_address.val('');
	$edit_customer.edit_address2.val('');
	$edit_customer.edit_city.val('');		
	$edit_customer.edit_state.val('');
	$edit_customer.edit_zip.val('');
	$edit_customer.email.val('')

	$edit_customer.edit_active.prop('checked', false);
	$edit_customer.edit_tax_exempt.prop('checked', false);
	$edit_customer.edit_allow_credit.prop('checked', false);
	
	$edit_customer.id = ''
}

export function load_customer_list(options)
{
		$edit_customer.customer_sel.html(`<option value="">Loading . . .</option>`)

			// toggle
			if($edit_customer.show_inactive == 0 && options == 'reload')
				$edit_customer.show_inactive = 1;
			else if($edit_customer.show_inactive == 1 && options == 'reload')
				$edit_customer.show_inactive = 0; 
		
			axios.get('/pos/customer/list?show_inactive=' + $edit_customer.show_inactive)
				.then( (response) => 
			{
				document.getElementById('customer_dialog').style.display = 'block';

						
				$edit_customer.customers = response.data.customers
				
				syncCustomerListChanges()

				$edit_customer.customer_sel.show();			

				//$edit_customer.customer_sel.val($edit_customer.editing_customer_id.val());
				$edit_customer.list_is_loaded = 1;
	
			}).catch(() => {
				show_note("An error occurred loading the list")
			})

}
