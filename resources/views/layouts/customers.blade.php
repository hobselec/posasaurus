
<div id="customer_dialog" style="display: none" class="container">
	<h2>Customers</h2>        

	<table>
	<tr>
	<td style="height: 300px; vertical-align: top">
		<span style="font-size: 80%">
		<input type="checkbox" id="show_inactive" onclick="customerdialog('reload')" />
	<label for="show_inactive" class="nice-label">Show Inactive &nbsp; 
		</label>
		</span>
		
		<img src="img/addnew.gif" style="cursor: pointer" onclick="add_customer_form()" title="Add Customer" alt="Add Customer" /> 
        <a  href="#">
        <img src="img/jobs.png" title="Customer Jobs" onclick="customer_jobs_dialog()" style="cursor: pointer; width: 42px; height: 42px" alt="Customer Jobs" /> &nbsp;&nbsp;</a>
        <br />
        
        <input type="search" class="customer_search" maxlength="20" size="20" value="Search Customer" style="color: #cccccc"/><br />
		
        <select size="12" onchange="edit_customer_info(this.value)" id="customer_listing" style="width: 180px">
        </select>
		
	</td>

	<td style="vertical-align: top; padding-left: 15px" id="customer_jobs_cell">
	
		<select id="customer_job_listing" onchange="load_edit_job()"></select>
		<p><input type="text" id="customer_job_edit" maxlength="64" /></p><br />
		<p><button type="button" onclick="save_job_edit()">Save</button></p>
	</td>

	<td style="vertical-align: top; padding-left: 15px" id="customer_edit_cell">

	Last name<div style="padding-left: 100px; display: inline"> First name</div>
	<div style="display: inline; padding-left: 100px"> MI</div><br />
	<input type="text" id="edit_last_name" maxlength="50" />, &nbsp;<input type="text" id="edit_first_name" maxlength="50" />, <input type="text" id="edit_mi" size="3" maxlength="3" /><br />
	Company<br />
	<input type="text" id="edit_company" size="40" maxlength="64" />
	<p>Address<br />
	<input type="text" id="edit_address" size="40" maxlength="100" /><br />
	<input type="text" id="edit_address2" size="40" maxlength="100" /><br />
	City <span style="padding-left: 135px; display: inline"> State</span> &nbsp;&nbsp; Zip<br />
	<input type="text" id="edit_city" maxlength="50" />, <input type="text" id="edit_state" size="2" maxlength="30" /> &nbsp;&nbsp;<input type="text" id="edit_zip" size="5" maxlength="10" /><br />

	Phone <span style="padding-left: 120px">Ext</span><br />
	<input type="text" size="20" maxlength="42" id="phone" /> &nbsp; &nbsp;<input type="text" size="4" maxlength="4" id="phone_ext" /><br />
	</p>
	List by: 
	<input type="radio" id="edit_listby_company" name="use_company" /> <label for="edit_listby_company" class="nice-label-radio"> Company</label> 
	<input type="radio" id="edit_listby_lastname" name="use_company" /> <label for="edit_listby_lastname" class="nice-label-radio">Last name</label><br />
	<input type="checkbox" id="edit_allow_credit" value="1" /> <label for="edit_allow_credit" class="nice-label">Has Credit:</label><br />
	<input type="checkbox" id="edit_tax_exempt" value="1" /> <label for="edit_tax_exempt" class="nice-label">Tax Exempt: </label><br />
	<input type="checkbox" id="edit_active" value="1" /> <label for="edit_active" class="nice-label">Active: </label>
	<input type="hidden" id="editing_customer_id" />
	<p style="margin-left: auto; margin-right: auto; text-align: center">
	<button type="button" id="save_customer_button" onclick="save_customer_info()">Save</button>
	</p>
	</td>
	</tr>
	</table>
</div>