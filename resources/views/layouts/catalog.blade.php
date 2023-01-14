
<!-- Catalog Dialog -->
<div id="catalog_dialog" style="display: none" class="container">
<h2>Catalog</h2>



	<div style="margin-left: 150px; font-size: 130%"><label>Search &nbsp; 
    <input type="search" class="form-control" id="catalog_search_name" size="25" maxlength="30" oninput="search_catalog()" /> 
    </label> &nbsp;<img src="img/search.gif" onclick="search_catalog('go')" style="cursor: pointer; vertical-align: bottom" alt="Search Catalog" /> &nbsp; &nbsp; 
    <img title="Add new item" src="img/addnew.gif" style="width: 32px; height: 32px; vertical-align: bottom; cursor: pointer" onclick="$catalog.add_item_dialog.dialog('open')" alt="Add new item" />
	<br />


	@if(Config::get('pos.use_catalog_filter')) 

	<input type="checkbox" id="catalog_use_wholesaler" /> &nbsp; <label for="catalog_use_wholesaler" class="nice-label"> <small>Search Principal Wholesaler Only   &nbsp;</small></label>

	@else
	<input type="hidden" id="catalog_use_wholesaler" />
	@endif

	</div>

	<div style="margin-top: 2px; height: 600px; overflow-x: hidden; overflow-y: scroll; border-top: 1px solid #000000">
		<table id="catalog_table" class="table table-striped">
            <thead class="bg-light sticky-top" style="z-index: 1">
                <tr>
                    <th></th>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Vendor</th>
                    <th>Barcode</th>
                    <th>Product ID</th>
                    <th>Price</th>
                    <th>Qty</th>
                </tr>

            </thead>
			<tbody>

            </tbody>
		</table>
	</div>

</div>

<!-- Add Catalog Item Dialog -->

<div id="add_item_dialog" style="display: none">

	<div class="container">
		Item Name<br />
		<input type="text" class="form-control" id="new_item_name" size="30" maxlength="30" /><br />
		Price<br />
		<input type="text" class="form-control" id="new_item_price" size="10" maxlength="7" onkeyup="add_decimals(this, event, false)" /><br />

		<!--
		<select id="new_item_category">
		<option value="">&ndash; Choose Category &ndash;</option>
		</select>
		-->
		Barcode<br />
		<input type="text" class="form-control" id="new_item_skn" size="14" maxlength="14" />

		Quantity<br />
		<input type="text" class="form-control" id="new_item_qty" size="14" maxlength="14" />

		<div class="m-3">
			<button type="button" id="new_item_save_button" class="btn btn-primary" onclick="save_new_item()">Save Item</button><br />
		</div>

	</div>

</div>