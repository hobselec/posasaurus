
export function ticketDialogs() {

    document.addEventListener("DOMContentLoaded", () => {


        $( "#barcode" ).autocomplete({ delay: 300, minLength: 3, source: 
            function(request, response)
            {
                if(!isNaN(request.term)) //avoid lookups on numbers for now
                    return;
            
                axios.get('/pos/catalog/search/' + request.term).then((cdata) =>
                {
                        
                    let myarr = new Array(cdata.length);
                            
                    let items = cdata.data
                    let tmpobj
                    for(let i = 0; i < items.length; i++)
                    {
                        tmpobj = new Object();
                
                        tmpobj.label = items[i].name;
                        tmpobj.value = items[i].id;
                        tmpobj.extra = `${items[i].product_id} ${items[i].vendor_name} &ndash; ${items[i].manufacturer_id} &ndash; Qty: ${items[i].qty}`
                    
                        myarr[i] = tmpobj
                    }
        
                    response(myarr);
                
                })
                
    
            }, 	open: function() {
        
                $('.ui-autocomplete li').css('font-size','60%');
        
            }, select: function( event, ui ) {
    

                //$pos.barcode.val(ui.item.value);
                //$pos.curItemId = ui.item.value
    
            //	event.stopPropagation(); // so enter key doesn't fire evet twice!

                // don't need for 13 since check_enter() already handles that
                if(event.button == 1 || event.button == 0 ) //|| event.which == 13) // m
                    lookup_item({type: 'catalog_id', id : ui.item.value });
    
    
            }, focus: function( event, ui ) {
    
                var sel_item = event.target;
    
                $('.ac_extra').remove();
    
                $('.ui-autocomplete li div').each(function() {
                
                //alert($(this).html());
    
                    if($(this).html() == ui.item.label)
                        $(this).parent().append(`<div class="ac_extra" style="font-size: 90%; color: #666666">${ui.item.extra}</div>`)
                
                    //$(this).html(ui.item.label + "<BR>" + ui.item.extra);
                
                
                });
                
                //alert(event.target.id);
                //(ui.item.extra);
    
            }
            
        });


        // used for selecting the name on the ticket
        $( "#customer_ticket_search" ).autocomplete({ minLength: 3, source: 
            function(request, acResponse)
            {
            
                axios.get('/pos/customer/search?q=' + request.term).then((response) =>
                {

                    let cdata = response.data

                    let myarr = [];
                    let tmpobj

                    for(let i = 0; i < cdata.length; i++)
                    {
                        tmpobj = new Object();

                        tmpobj.label = cdata[i].display_name;

                        tmpobj.value = cdata[i];
                    
                        myarr.push(tmpobj);
                    }

                    acResponse(myarr);

                })
                

                }, 	open: function() {
                        $('.ui-autocomplete li').css('font-size','70%');
                        //$('.ui-autocomplete ul').css('height','100px');
                        
                }, select: function( event, ui ) {

                    $pos.jobs = ui.item.value.jobs
                
                        // set customer name on the db entry
                        axios.put('/pos/ticket/set-customer', { 
                            'id' : $('#ticket_id').val(), 
                            'customer_id' : ui.item.value.id }).then((response) =>
                        {

                                $pos.customer_display_name.html(ui.item.label);
                                $pos.customer_job_display_name.html('').hide(); // hide job in case changed customer
                                $pos.customer_id.val(ui.item.value.id);
                                $pos.tax_exempt.val(response.data.ticket.customer.tax_exempt);
                                $pos.allow_credit.val(response.data.ticket.customer.credit);
                                $pos.customer_ticket_search.val('');
                                $pos.customer_ticket_search.hide();
                                

                                // reset tax depending on tax exempt


                                if(response.data.ticket.customer.tax_exempt)
                                {
                                    
                                    // get total, remove comma and parse as float
                                    var tmp = $pos.display_total.html();
                                    let cur_total = parseFloat(tmp.replace(',', ''));
                                    
                                    tmp = $pos.tax.html();
                                    let cur_tax = parseFloat(tmp.replace(',', ''));
                                    
                                    // subtract tax from total
                                    let new_total = cur_total - cur_tax;
                                    new_total = new_total.toFixed(2);

                                    // tax to 0 and display new total
                                    $pos.tax.html('0.00');
                                    $pos.display_total.html(new_total);

                                } else
                                { // set tax in case we previously had set to tax-exempt customer
                                    $pos.tax.html(response.data.ticket.tax.toLocaleString('en-US', { minimumFractionDigits: 2}));
                                    $pos.display_total.html(response.data.ticket.total.toLocaleString('en-US', { minimumFractionDigits: 2}));
                                }
                                
                                // update the change-ticket select box with customer's name
                                
                                var new_label = "#" + response.data.ticket.display_id + " - " + ui.item.label;
                                $('#open_transactions option').each(function() {
                    
                                    if($(this).val() == $pos.ticket_id.val())
                                        $(this).html(new_label);
                                });					

                                //
                                // show job listings if available
                                //
                                
                                // populate the job box
                            //	$.get('modify_customer.php', { 'get_customer_jobs' : '', 'job_cust_id' : ui.item.value }, function(response) 
                                //{
                                
                                    let htmlline = "<option value=\"\"> - Choose Job -</option>";
                                    htmlline += "<option value=\"\" disabled=\"disabled\"></option>";
                                    htmlline += "<option value=\"\">No Job Specified</option>";
                                    
                                    for(let i = 0; i < $pos.jobs.length; i++)
                                    {
                                        htmlline += "<option value=\"" + $pos.jobs[i].id + "\">" + $pos.jobs[i].name + "</option>";
                                    }
                                    
                                    if($pos.jobs.length > 0)
                                        $pos.pay_job_id.html(htmlline).show();
                                    //else // if no jobs, then let the search box re-appear
                                //	{
                                        $pos.customer_ticket_search.show();
                                //	}

                                //}, 'json');
                                
                                //$pos.pay_job_id.show();
                            
                            
                        }).catch((error) => {
                            show_note("Could not set customer!");
                        })
                }
            
            })

        // submit transaction on enter key in the cash input box
        document.getElementById('cash_given').addEventListener('keyup', function(evt) {

            let code = (evt.key ? evt.key : evt.code)

            if(code == 'Enter')
                post_transaction()
    
        })


    })


}