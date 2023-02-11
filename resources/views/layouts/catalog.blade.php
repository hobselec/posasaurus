
<!-- Catalog Dialog -->
<div id="catalog_dialog" style="display: none" class="container">
<h2>Catalog</h2>


	<item-catalog></item-catalog>
	

</div>

<!-- Add Catalog Item Dialog -->

<div id="add_item_dialog" style="display: none">

	<div class="container">
		Item Name<br />
		<input type="text" class="form-control" id="new_item_name" size="30" maxlength="30" /><br />
		Price<br />
		<input type="text" class="form-control" id="new_item_price" size="10" maxlength="7" /><br />

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