export function catalogDialogs() {

    document.addEventListener("DOMContentLoaded", () => {

        $catalog.add_item_dialog.dialog({ title : 'New Item', autoOpen: false, modal : true, resizable : false, draggable : true, width: 450, height: 500,
            open : function() {
                if($pos.cart_container.is(':visible'))
                    $catalog.newItemSaveButton.html('Save and Add to Cart')
                else
                    $catalog.newItemSaveButton.html('Save Item')
            } 
        });    

    })

}