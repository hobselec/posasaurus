
<div id="customer_dialog" style="display: none" class="container">
	<h2>Customers</h2>     


	<div class="row">

		<div class="col col-auto">
			<input type="checkbox" id="show_inactive" onclick="customerDialog('reload')" />
			<label for="show_inactive" class="nice-label"> Show Inactive &nbsp; 
			</label>
		</div>

		<div class="col col-auto">
			<img src="img/addnew.gif" style="cursor: pointer" onclick="add_customer_form()" title="Add Customer" alt="Add Customer" /> 
			<a href="#">
				<img src="img/jobs.png" title="Customer Jobs" onclick="customer_jobs_dialog()" style="cursor: pointer; width: 42px; height: 42px" alt="Customer Jobs" />
			</a>
		</div>

	</div>

	<div class="row">

	<div class="col col-auto">
			<input type="search" class="customer_search form-control" maxlength="20" size="20" placeholder="Search Customer" /><br />
			<label class="form-label">
				Customer
			<select onchange="edit_customer_info(this.value)" id="customer_listing" class="form-select">
			</select>
			</label>
		</div>
	</div>

	<div style="vertical-align: top; padding-left: 15px" id="customer_jobs_cell">
	
		<select id="customer_job_listing" class="form-select" onchange="load_edit_job()"></select>
		<p><input type="text" id="customer_job_edit" maxlength="64" /></p><br />
		<p><button type="button" id="customer_job_save_button" onclick="save_job_edit()">Save</button></p>
	</div>

	<div id="customerApp">

		<search-box></search-box>

	</div>

	<div style="vertical-align: top; display: none" id="customer_edit_cell">

	Last name<div style="padding-left: 100px; display: inline"> First name</div>
	<div style="display: inline; padding-left: 100px"> MI</div><br />
	<input type="text" id="edit_last_name" maxlength="50" />, &nbsp;<input type="text" id="edit_first_name" maxlength="50" />, <input type="text" id="edit_mi" size="3" maxlength="3" /><br />
	Company<br />
	<input type="text" id="edit_company" size="40" maxlength="64" />
	
	<p class="mt-3">Address<br />
		<input type="text" id="edit_address" size="40" maxlength="100" />
		<div class="mt-1">
			<input type="text" id="edit_address2" size="40" maxlength="100" placeholder="address line 2" />
		</div>
		City <span style="padding-left: 135px; display: inline"> State</span> &nbsp;&nbsp; Zip<br />
		<input type="text" id="edit_city" maxlength="50" />, <input type="text" id="edit_state" size="2" maxlength="30" /> &nbsp;&nbsp;<input type="text" id="edit_zip" size="5" maxlength="10" /><br />

		Phone <span style="padding-left: 120px">Ext</span><br />
		<input type="text" size="20" maxlength="42" id="phone" /> &nbsp; &nbsp;<input type="text" size="4" maxlength="4" id="phone_ext" /><br />
	</p>
    <p>
        E-mail<br /> <input type="email" maxlength="128" id="edit_email" />
    </p>
	List by: 
	<input type="radio" id="edit_listby_company" name="use_company" /> <label for="edit_listby_company" class="nice-label-radio"> Company</label> 
	<input type="radio" id="edit_listby_lastname" name="use_company" /> <label for="edit_listby_lastname" class="nice-label-radio"> Last name</label><br />
	
	<div class="mt-3">
		<div class="mt-3">
			<input type="checkbox" id="edit_allow_credit" value="1" /> <label for="edit_allow_credit" class="nice-label"> Has Credit</label><br />
		</div>
		<div class="mt-3">
			<input type="checkbox" id="edit_tax_exempt" value="1" /> <label for="edit_tax_exempt" class="nice-label"> Tax Exempt</label><br />
		</div>
		<div class="mt-3">
			<input type="checkbox" id="edit_active" value="1" /> <label for="edit_active" class="nice-label"> Active</label>
		</div>
	</div>

	<p style="margin-left: auto; margin-right: auto; text-align: center">
		<button type="button" class="btn btn-primary btn-lg" id="save_customer_button" onclick="save_customer_info()">Save Customer</button>
	</p>
	</div>

</div>