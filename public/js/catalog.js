
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


function show_catalog()
{

	$pos.barcode.prop('disabled', true);
	$catalog.dialog.show();
	$pos.mainContainer.hide()
	$billing.dialog.hide()
	$catalog.search_name.select().focus();

}

function close_catalog()
{
	$pos.barcode.prop('disabled', false);
	$catalog.dialog.hide();
	auth_cancel();
}

t= '';

function search_catalog(v)
{

	$catalog.open_record = 0; // unset the open records
	var use_ws = 0; // use wholesaler, can switch data ranges in the database if desired (catalog.php)
	

	if(v != 'go') // wait for additional input before quering server
	{
		clearTimeout(t);
		t = setTimeout("search_catalog('go')", 500);
		return false;
	}

	if($catalog.search_name.val().length < 3 && v != 'go')
		return false;

	($catalog.use_ws.prop('checked') == true) ? use_ws = 1 : use_ws = 0;


	axios.get('/pos/catalog/search/' + $catalog.search_name.val() + '?use_ws=' + use_ws).then((response) => {

		var rows = '';
		let dataRow;

		for(let i = 0; i < response.data.length; i++)
		{
			dataRow = response.data[i]
			
			rows += `<tr id="ct_${dataRow.barcode}">
					<td>
						<button type="button" onclick="edit_cat_row($(this), ${dataRow.id})">Edit</button>
					</td>
					<td>${dataRow.barcode}</td>
					<td>${dataRow.name}</td>
					<td>${dataRow.vendor_name}</td>
					<td>${dataRow.product_id}</td>
					<td>${dataRow.manufacturer_id}</td>
					<td>${dataRow.price}</td>
					<td>${dataRow.qty}</td>
					</tr>`
		
		}

		$catalog.catalog_table.html(rows);
	
		//$('#catalog_table td').each(function() {
		
	//		$(this).css('border','1px solid #000000');
		
	//	});
	
		/*
			$("#catalog_table tr").hover(
			function()
			{
				$(this).addClass("highlight");
			},
			function()
			{			
				$(this).removeClass("highlight");
			});
		*/
	
		//$('#catalog_table td').each(function() { $(this).css('border', '1px solid #000000'); });
		//$('#catalog_headings span').each(function() { $(this).css('border', '1px solid #000000'); });
	
	})
	

}

function add_catalog_item()
{
	var pos = $('#catalog_icon').offset(); // get position to place dialog at

	//var lpos = pos.left + 'px';
	//var tpos = pos.top - 35 + 'px';

	$catalog.add_item_dialog.show(); // css('left', lpos).css('top', tpos).
	

}

function save_new_item()
{
	var item_name = $catalog.new_item_name.val();
	var item_price = $catalog.new_item_price.val();
	var item_skn = $catalog.new_item_skn.val();
	var add_to_cart = 0;

	//var category = ;
	($('#new_item_to_cart').prop('checked') == true) ? add_to_cart=1 : add_to_cart=0;

	if(item_name == '' || isNaN(item_price))
	{
		show_note("Please enter the item name and price");
		return false;
	
	}
	
	axios.post('/pos/catalog/item', 
	{ 'item_name' : item_name, 'item_price' : item_price, 'item_skn' : item_skn }).then((response) => {
	

		show_note("Item Added");
		
		if(add_to_cart == 1)
		{
				$pos.barcode.val(response.new_id);
				lookup_item();
		}
			
		$catalog.add_item_dialog.hide();
		$catalog.new_item_price.val('');
		$catalog.new_item_name.val('');
		
		

		if(response.product_id_conflict)
			alert("A duplicate item exists under this UPC");
	
	})
	

}

function edit_cat_row(button_obj, barcode)
{
	// save an opened row or open a row for editing
	// 
	var save = 0;
	

	if(button_obj.html() == 'Save')
	{
	
		data = new Object();
	
		data.barcode = $('#cat_barcode').val();
		data.name = $('#cat_name').val();
		data.vendor = $('#cat_vendor').val();
		data.sku = $('#cat_sku').val();
		data.manufacturer_id = $('#cat_manufacturer_id').val();
		data.price = $('#cat_price').val();
		data.qty = $('#cat_qty').val();
		data.edit_item = 1;
		
		if(data.price != '')
		{

			if(!(data.price.indexOf('.') == data.price.length - 1 - 2))
			{
				alert("Please include the decimal and cents for the item");
				return false;
			}
		
			if(isNaN(data.price))
			{
				alert("Please enter a valid number with decimal point and cents");
				return false;
			}
			
			if(data.price.indexOf('.') == 0) // add preceding zero to decimal
				$('#cat_price').val('0' + data.price);
		
		}
		
		axios.put('/pos/catalog/item', data).then((response) => {
		

			//	show_note("Could not save item!");

		
			if(response.product_id_conflict)
				alert("A duplicate item exists under this UPC");
		
		})
	
		save = 1;
	
		$catalog.open_record = 0;
	}



	if($catalog.open_record == 1)
		return false;

	if(!save)
		$catalog.open_record = 1;

	var row_id = button_obj.parent().parent().attr('id');
	
	if(!save)
		button_obj.html('Save')
	else
	{
		button_obj.html('Edit');

	}
	

	cell = $('#' + row_id).find("td").eq(1);

	if(save)
		cell.html($('#cat_barcode').val());
	else
	{
		tmp = cell.html();
		cell.html("<input style=\"width: 90%\" type=\"text\" id=\"cat_barcode\" />");
		$('#cat_barcode').val(tmp).prop('disabled', true);
	}

	cell = $('#' + row_id).find("td").eq(2);
	
	if(save)
		cell.html($('#cat_name').val());
	else
	{
		tmp = cell.html();
		cell.html("<input style=\"width: 90%\" type=\"text\" id=\"cat_name\" maxlength=\"30\" />");
		$('#cat_name').val(tmp);
	}
		
	cell = $('#' + row_id).find("td").eq(3);
	
	if(save)
		cell.html($('#cat_vendor').val());
	else
	{
		tmp = cell.html();
		cell.html("<input style=\"width: 90%\" type=\"text\" id=\"cat_vendor\" maxlength=\"20\" />");
		$('#cat_vendor').val(tmp);
	}

	cell = $('#' + row_id).find("td").eq(4);

	if(save)
		cell.html($('#cat_sku').val());
	else
	{
		tmp = cell.html(); // product_id in the database
		cell.html("<input style=\"width: 90%\" type=\"text\" id=\"cat_sku\" maxlength=\"24\" />");
		$('#cat_sku').val(tmp);
	}

	cell = $('#' + row_id).find("td").eq(5);
	
	if(save)
		cell.html($('#cat_manufacturer_id').val());
	else
	{
		tmp = cell.html();
		cell.html("<input style=\"width: 90%\" type=\"text\" id=\"cat_manufacturer_id\" maxlength=\"24\" />");
		$('#cat_manufacturer_id').val(tmp);
	}

	cell = $('#' + row_id).find("td").eq(6);
	
	if(save)
		cell.html($('#cat_price').val());
	else
	{
		tmp = cell.html();
		cell.html("<input style=\"text-align: right\" size=\"5\" type=\"text\" id=\"cat_price\" maxlength=\"10\" />");
		$('#cat_price').val(tmp);
	}

	cell = $('#' + row_id).find("td").eq(7);

	if(save)
		cell.html($('#cat_qty').val());
	else
	{
		tmp = cell.html();
		cell.html("<input size=\"3\" style=\"text-align: right\" type=\"text\" id=\"cat_qty\" maxlength=\"11\" />");
		$('#cat_qty').val(tmp);
	}

}
