<template>
    <div style="margin-left: 150px; font-size: 130%"><label>Search &nbsp; 
    <input type="search" class="form-control" size="25" maxlength="30" v-model="state.query" /> 
    </label> &nbsp;<img src="./assets/search.gif"  style="cursor: pointer; vertical-align: bottom" alt="Search Catalog" @click="getResults" /> &nbsp; &nbsp; 
    <img title="Add new item" src="./assets/addnew.gif" style="width: 32px; height: 32px; vertical-align: bottom; cursor: pointer" onclick="$catalog.add_item_dialog.dialog('open')" alt="Add new item" />
	<br />

	<input type="checkbox" v-model="state.useWholesaler" id="catalog_use_wholesaler" /> &nbsp; <label for="catalog_use_wholesaler" class="nice-label"> <small>Search Principal Wholesaler  &nbsp;</small></label>
	<input type="checkbox" v-model="state.useCustom" id="catalog_use_custom" /> &nbsp; <label for="catalog_use_custom" class="nice-label"> <small>Search Custom Items   &nbsp;</small></label>


	</div>

	<div style="margin-top: 2px; height: 600px;  overflow-y: scroll; border-top: 1px solid #000000">
		<table class="table table-striped">
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
                <tr v-for="row in state.results" :key="row.id">

                    <template v-if="state.editItem.id == row.id">
                        <td>
						<button :disabled="state.saving" type="button" @click="saveItem()">Save</button>
                        </td>
                        <td><input :disabled="state.saving" type="text" v-model="state.editItem.barcode" class="col-sm-10" /></td>
                        <td><input :disabled="state.saving" type="text" v-model="state.editItem.name" /></td>
                        <td><input :disabled="state.saving" type="text" v-model="state.editItem.vendor_name" /></td>
                        <td><input :disabled="state.saving" type="text" v-model="state.editItem.product_id" /></td>
                        <td><input :disabled="state.saving" type="text" v-model="state.editItem.manufacturer_id" /></td>
                        <td><input :disabled="state.saving" type="text" v-model="state.editItem.price" class="col-sm-11" /></td>
                        <td><input :disabled="state.saving" type="text" v-model="state.editItem.qty" class="col-sm-11" /></td>
                    </template>

                    <template v-else>
                    
                        <td>
                            <button :disabled="state.saving" type="button" @click="editItem(row)">Edit</button>
                        </td>
                        <td>{{ row.barcode }}</td>
                        <td>{{ row.name }}</td>
                        <td>{{ row.vendor_name }}</td>
                        <td>{{ row.product_id }}</td>
                        <td>{{ row.manufacturer_id }}</td>
                        <td>{{ row.price.toFixed(2) }}</td>
                        <td>{{ row.qty }}</td>
                     </template>
                </tr>
            </tbody>
		</table>
	</div>
</template>
<script setup>

import { reactive, onMounted, computed, watch} from 'vue'
import { debounce } from "lodash"

const state = reactive({ 
        query : '', results : [], useWholesaler: true, useCustom : true, editRow : '', 
        editItem : {barcode : '', name : '', vendor_name : '', product_id : '', manufacturer_id : '', price: 0, qty: ''},
        saving : false,
        itemOriginal : {}
    })


function editItem(row)
{
    // restore old item
    if(Object.keys(state.itemOriginal).length > 0)
    {
        let index = state.results.findIndex(item => item.id == state.editItem.id)
        state.results[index] = state.itemOriginal
    }
    state.itemOriginal = { ...row }
    state.editItem = row

}

function saveItem()
{

    let priceStr = state.editItem.price.toString()

    if(!(priceStr.indexOf('.') == priceStr.length - 1 - 2))
    {
        alert("Please include the decimal and cents for the item");
        return false;
    }

    if(isNaN(state.editItem.price))
    {
        alert("Please enter a valid number with decimal point and cents");
        return false;
    }
    state.saving = true

    axios.put('/pos/catalog/item', {item : state.editItem }).then((response) => {
		
        state.saving = false

        state.editItem.price = Number(state.editItem.price)
        state.editItem = {barcode : '', name : '', vendor_name : '', product_id : '', manufacturer_id : '', price: 0, qty: ''}
        state.itemOriginal = {}
    
        // not implemented, throws a 422
       // if(response.product_id_conflict)
        //    alert("A duplicate item exists under this UPC");
    
    }).catch(() => {
        state.saving = false
        show_note("An error occurred saving the item.  Might be a duplicate UPC.")
    })
}

function getResults()
{

    axios.get('/pos/catalog/search/' + state.query + '?use_ws=' + state.useWholesaler + '&use_custom=' + state.useCustom).then((response) => {

        state.editItem = {barcode : '', name : '', vendor_name : '', product_id : '', manufacturer_id : '', price: 0, qty: ''},
        state.itemOriginal = {}

        state.results = response.data
        
    })
}

watch(
    () => state.query, 
        debounce(function(q) {
                if(q.length > 2)
                    getResults()
                }, 700)
)


</script>