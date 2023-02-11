
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


export function show_catalog()
{

	$pos.barcode.prop('disabled', true);
	$catalog.dialog.show();
	$pos.mainContainer.hide()
	$billing.dialog.hide()

}

export function close_catalog()
{
	$pos.barcode.prop('disabled', false);
	$catalog.dialog.hide();
	auth_cancel();
}


export function add_catalog_item()
{
	var pos = $('#catalog_icon').offset(); // get position to place dialog at

	//var lpos = pos.left + 'px';
	//var tpos = pos.top - 35 + 'px';

	$catalog.add_item_dialog.show(); // css('left', lpos).css('top', tpos).
	

}

export function save_new_item()
{
	var item_name = $catalog.new_item_name.val();
	var item_price = $catalog.new_item_price.val();
	var item_skn = $catalog.new_item_skn.val();
	var item_qty = $catalog.new_item_qty.val()
	var addToCart



	if(item_name == '' || isNaN(item_price))
	{
		show_note("Please enter the item name and price");
		return false;
	
	}

	$pos.cart_container.is(':visible') ? addToCart = true : addToCart = false

	
	axios.post('/pos/catalog/item', 
	{ 'name' : item_name, 'price' : item_price, 'skn' : item_skn, 'qty' : item_qty }).then((response) => {
	

		show_note("Item Added");
		
		if(addToCart)
		{
				$pos.barcode.val(response.id);
				lookup_item();
		}
			
		$catalog.add_item_dialog.dialog('close')

		$catalog.new_item_price.val('');
		$catalog.new_item_name.val('');
		$catalog.new_item_qty.val('');
		

	}).catch((error) => {
		show_note("An error occurred")
	})
	

}
