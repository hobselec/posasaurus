
export function setupGlobals() {

    window.$pos = 
  {
	mainContainer : $('#main_container'),
	open_transactions : $('#open_transactions'),
	special_options_button : $('#special_options_button'),
	payment_specialoptions_dialog : $('#payment_specialoptions_dialog'),
	is_resale : '0',
	freight_display_total : $('#freight_display_total'),
	labor_display_total : $('#labor_display_total'),
	discount_display_total : $('#discount_display_total'),
	discount_icon : $('#discount_icon'),
	freight_icon : $('#freight_icon'),
	labor_icon : $('#labor_icon'),
	barcode : $("#barcode"),
	curItemId : '',
	ticket_id : $("#ticket_id"),
	customer_id : $("#customer_id"),
	customer_job_id : $("#customer_job_id"),
	customer_display_name : $("#customer_display_name"),
	ticket_display_id : $("#ticket_display_id"),
	tax_exempt : $('#tax_exempt'),
	discount : $('#discount_number'),
	freight : $('#freight_number'),
	labor : $('#labor_number'),
	subtotal : $('#subtotal'),
	tax : $('#tax'),
	display_total : $('#display_total'),
	cart : $('#cart tbody'),
	cart_container : $('#cart_container'),
	cash_given : $('#cash_given'),
	cancel_button : $('#cancel_pay_button'),
	cc_trans_no : $('#cc_trans_no'),
	check_no : $('#check_no'),
	customer_ticket_search : $('#customer_ticket_search'),
	pay_button : $('#pay_button'),
	payment_methods : $('#payment_methods'),
	class_customer_search : $('.class_customer_search'),
	customer_dialog : $('#customer_dialog'),
	customer_search : $('.customer_search'),
	pause_button : $('#pause_button'),
	void_button : $('#clear_button'),
	refund_switch : $('#pay_refund'),
	take_check : $('#take_check'),
	take_cc : $('#take_cc'),
	take_cash : $('#take_cash'),
	pay_job_id : $('#pay_job_id'),
	customer_job_display_name : $('#customer_job_display_name'),
	allow_credit : $('#allow_credit'),
	previous_value : '', // for auto setting the decimal
	previous_decimal : 0, // binary if last value had the decimal
	closing_cash : $('#closing_cash'),
	closing_checks : $('#closing_checks'),
	useLabelPrinter : 0,
	useAutoDecimal : 0,
	printReceiptChkbox : $('#printReceiptChkbox'),
	tax_rate : '',
	refund_indicator : $('#refund_indicator'),

	recv_by_name : $('#recv_by_name'),
	recv_by_container : $('#recv_by_container'),
	recv_by_input : $('#recv_by_input'),

	cart_item_description_name : $('#cart_item_description_name'),
	cart_item_description_label : $('#cart_item_description_label'),
	cart_item_description_barcode : '',
	save_cart_item_description_button : $('#save_cart_item_description_button'),

	jobs : [],
	cartItems : [],
	paymentMethodDisplay : $('#payment_method'),
	paymentMethod : '',
	postPaymentButton : $('#postpayment_button'),
	paymentDialogTotal : $('#payment_dialog_total')
  };


  
  window.$edit_customer =
  {
	// Initialize all the queries you want to use more than once
	edit_last_name : $('#edit_last_name'),
	edit_first_name : $('#edit_first_name'),
	edit_company : $('#edit_company'),
	edit_mi : $('#edit_mi'),
	edit_address : $('#edit_address'),
	edit_address2 : $('#edit_address2'),
	edit_city : $('#edit_city'),
	edit_state : $('#edit_state'),
	edit_zip : $('#edit_zip'),
	edit_phone : $('#edit_phone'),
	id : '',
	edit_tax_exempt : $('#edit_tax_exempt'),
	edit_allow_credit: $('#edit_allow_credit'),
	edit_active : $('#edit_active'),
	edit_listby_company : $('#edit_listby_company'),
	edit_listby_lastname : $('#edit_listby_lastname'),
	save_customer_button : $('#save_customer_button'),
	email : $('#edit_email'),
	customer_sel : $('#customer_listing'),
	customer_job_listing : $('#customer_job_listing'),
	customers : [],
	customer_edit_cell : $('#customer_edit_cell'),
	customer_jobs_cell : $('#customer_jobs_cell'),
	customer_job_edit : $('#customer_job_edit'),
	customer_job_save_button : $('#customer_job_save_button'),
	phone : $('#phone'),
	phone_ext : $('#phone_ext'),
	show_inactive : 0,
	list_is_loaded : 0,
	default_customer_id : ''
	
  };

  

  window.$catalog =
  {
  	add_item_dialog : $('#add_item_dialog'),
  	new_item_name : $('#new_item_name'),
	new_item_price : $('#new_item_price'),
	new_item_skn : $('#new_item_skn'),
	new_item_qty : $('#new_item_qty'),
	newItemSaveButton : $('#new_item_save_button'),
	icon : $('#catalog_icon'),
	dialog : $('#catalog_dialog'),
	search_name : $('#catalog_search_name'),
	catalog_table : $('#catalog_table tbody'),
	use_ws : $('#catalog_use_wholesaler'),
	open_record : 0,
	pre_auth_button_obj : '',
	pre_auth_barcode : ''
  };
  
  window.$payments =
  {
	payment_recv_customer_id : $('#payment_recv_customer_id'),
	payment_recv_search_name : $('#payment_recv_search_name'),
	payment_recv_method : $('#payment_recv_method'),
	payment_recv_extra_info : $('#payment_recv_extra_info'),
	payment_recv_amt : $('#payment_recv_amt'),
	payment_recv_display_name : $('#payment_recv_display_name'),
	payment_recv_date : $('#payment_recv_date'),
	payment_recv_job_id : $('#payment_recv_job_id'),
	payment_recv_button : $('#recv_payment_button'),
	payment_recv_display_balance : $('#payment_recv_display_balance'),

	customerSelection : {}
  };
  
  window.$editable_item =
  {
	cur_item_id : '',
	cur_cell : '0',
	cur_qty : '',
	edit_contents : ''
  
  };

  window.$editable_price =
  {
	cur_item_id : '',
	cur_cell : '0',
	cur_price : '',
	edit_contents : '',
	pre_auth_cell : '',
	pre_auth_item_id : ''
  
  };
  
  window.$auto_decimal = 
  {
	box : null,
	count : 0,
	str : ''
  }
  
  window.$billing = 
  {
  
	dialog : $('#billing_dialog'),
	container : $('#billing_container'),
	list : $('#billing_list tbody'),
	customer_bill_dialog : $('#customer_bill_dialog'),
	customer_bill_name : $('#customer_bill_name'),
	customer_bill_job_id : $('#customer_bill_job_id'),
	customer_tickets_container : $('#customer_tickets_container'),
	customer_tickets_list : $('#customer_tickets_list'),
	bill_start_date : $('#bill_start_date'),
	bill_end_date : $('#bill_end_date'),
	customer_bill_customer_id : $('#customer_bill_customer_id'),
	customer_bill_transaction_type : $('#customer_bill_transaction_type'),
	ticket_items_list : $('#ticket_items_list'),
	ticket_tbody : $('#ticket_tbody'),
	//ticket_items_container : $('#ticket_items_cotainer'),
	billing_display_types : $('#billing_display_types'),
	billing_list_end_date : $('#billing_list_end_date'),
	print_statement_button : $('#print_statement_button'),
	viewStatementCtrl : $('#viewStatementCtrl'),
	dataRows : [],

	ticket_items_container : $('#billing_ticket_items_container'),

	tickets : [],

	printAllStatementsCtrl : $('#printAllStatementsCtrl'),
	printAllStatementsIndicator : $('#printAllStatementsIndicator'),

	//statement : $('#statement_view_dialog'),
	statement_contents : $('#statement_contents'),

	reports_dialog : $('#reports_dialog'),

	adjustment : {
		dialog : $('#billing_adjustment_dialog'),
		displayName : $('#billing_adjustment_display_name'),
		refundFormat : $('#billing_adjustment_refund_format'),

		//type : $("input[name='billing_adjustment_type']")

		jobs : $('#billing_adjustment_job_container'),
		jobId : $('#billing_adjustment_job_id'),
		customerId : '',
		amount : $('#billing_adjustment_amount')
	},

  };

  window.$cmenu =
  {
	id : '',
	type : '',
	void_ticket_id : 0,
	row : false, // hold the row for changing the background of the open line
	row_shade : false, // to tell the striping pattern
	prev_id : false, // to see the former row's id
	obj : false

  }

  window.$dialogs = 
  {
	cart_item_description_dialog : $('#cart_item_description_dialog'),
	tmp_row : false
  }

  window.$clock = 
  {
    container : $('#clock_container'),
    currentHours : 0,
    currentMinutes : 0,
    ampm : ''
	
  }

  window.$reports = 
  {
	start_date : $('#report_start_date'),
	end_date : $('#report_end_date'),
	weekly_report_fields : $('#weekly_report_fields')

  }

}