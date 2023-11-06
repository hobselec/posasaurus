

export function startUp() {

    document.addEventListener("DOMContentLoaded", () => {
 
        clear_pos()

        // setup ticket heading sorting
        $('#ticket_heading_sort_row img').each(function() { 
            if($(this).attr('id') != 'date_sortimg')
                $(this).css('display', 'none');
        });

        document.addEventListener('click', function(evt) {
            if($editable_item.cur_item_id != '') // remove editable item in cart
            {
    
                if(evt.target.id != 'cur_edit_cell' && evt.target.id != 'cur_edit_item') // only restore in an onblur to the cell
                    restore_qty();
                else
                    return false;
            } else if($editable_price.cur_item_id != '') // remove editable item in cart
            {
                if(evt.target.id != 'cur_edit_cell_price' && evt.target.id != 'auth_confirm') // only restore in an onblur to the cell
                    restore_price();
                else
                    return false;
    
            }

            // should be mousedown event with button == 2?
            if(evt.button != 3) 
                $('.vs-context-menu').hide();
    



        })

        
        $('.customer_search').autocomplete({ minLength: 3, source:
            function(request, acResponse)
            {
            
                axios.get('/pos/customer/search?q=' + request.term).then((response) =>
                {
                    let cdata = response.data
    
                    let myarr = new Array(cdata.length);
                    
                    let name = ''
                    let tmpobj
    
                    for(let i = 0; i < cdata.length; i++)
                    {
                        tmpobj = new Object();
    
                        tmpobj.label = cdata[i].display_name;
    
                        tmpobj.value = cdata[i].id;
                    
                        myarr[i] = tmpobj;
                    }
        
                    acResponse(myarr);
    
                })
                
                
    
            }, 	open: function() {
                    
                    //$('.ui-autocomplete li').css('font-size','60%');
                    //$('.ui-autocomplete ul').css('height','100px');
                    
            }, select: function( event, ui ) {
    
                if($pos.customer_dialog.css('display') == 'block')
                {
                    $edit_customer.customer_sel.val(ui.item.value);
                    edit_customer_info(ui.item.value);
    
    
                } else if($billing.dialog.css('display') == 'block')
                {
                    view_customer_bills(ui.item.value);
    
                }
            }
        })
    
       
    
        $('#shutdown_dialog').dialog({ title : 'POS Shutdown', 
            autoOpen: false, modal : true, resizable : false, 
            draggable : false, width: 350, height: 420
        });

          
        if($clock.container.length)
            window.setTimeout("update_clock()", 1000);

    })
    // end Document Ready

    window.axios.interceptors.response.use(function (response) {
        // Do something with response data
    
            return response;
        }, function (error) {
        
            if(error.response.status == 401 || error.response.status == 405 || error.response.status == 419) // might change this, 405 returns on put method, 302 on post I think
            {
    
                Swal.fire('', 'Your session has expired.  You will be sent to the login page', 'error')
                    .then(function() { location.href='/pos/' });
                        
                // stop the response
             }
             return Promise.reject(error);
        })
    
        
    Echo.private('channel-billing')
        .listen('UpdateBilling', (e) => {
    
            $billing.dataRows.forEach(item => {
                if(item.id == e.balance.id)
                    item.balance = e.balance
            })
        })
}
