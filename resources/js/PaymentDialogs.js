
export function paymentDialogs() {

    document.addEventListener("DOMContentLoaded", () => {
 
        document.getElementById('recv_payment_screen').addEventListener('show.bs.modal', function(event) {
            // set to current time
            let now = new Date()
            let dateAdjusted = new Date(now.getTime() - now.getTimezoneOffset()*60000)
    
            document.getElementById('payment_recv_date').value = dateAdjusted.toISOString().substring(0,16)
    
            $pos.barcode.prop('disabled',true);
            $payments.payment_recv_search_name.focus();
        })
        document.getElementById('recv_payment_screen').addEventListener('hide.bs.modal', function(event) {
            $payments.payment_recv_customer_id.val('');
            $payments.payment_recv_search_name.val('');
    
            $payments.payment_recv_search_name.show();
    
            $payments.payment_recv_display_name.html('');
            $payments.payment_recv_display_balance.html('')
            $payments.payment_recv_job_id.html('');
            $payments.payment_recv_job_id.hide()
        })
    
    
        $( "#payment_recv_search_name" ).autocomplete({ minLength: 3, delay : 500, source: 
            function(request, acResponse)
            {
    
                axios.get('/pos/customer/search?q=' + request.term + '&showBalances=1').then((response) =>
                {
                    let cdata = response.data
    
                    let myarr = new Array(cdata.length);
                    
                    for(let i = 0; i < cdata.length; i++)
                    {
                        tmpobj = new Object();
    
                        tmpobj.label = cdata[i].display_name;
    
                        tmpobj.value = cdata[i].id;
    
                        tmpobj.customer = cdata[i]
                    
                        myarr[i] = tmpobj;
                    }
        
                    acResponse(myarr);
    
                })
                
    
            }, 	open: function() {
                    //$('.ui-autocomplete li').css('font-size','60%');
                    //$('.ui-autocomplete ul').css('height','100px');
                    
            }, select: function( event, ui ) {
    
                    $payments.customerSelection = ui.item
    
                    $payments.payment_recv_customer_id.val(ui.item.value);
                    $payments.payment_recv_search_name.val(ui.item.label);
    
                    $payments.payment_recv_search_name.hide();
    
                    $payments.payment_recv_display_name.html(ui.item.label);
    
                    //ui.item.value = ''; // replace ID var with label so it goes in the box instead
    
                    var payment_job_html = "<option value=\"0\">- Choose Job -</option>";
    
                    for(let i = 0; i < ui.item.customer.jobs.length; i++)
                    {
                        let job = ui.item.customer.jobs[i]
                        payment_job_html += `<option value="${job.id}">${job.name}</option>`
                    }
                        
                    $payments.payment_recv_display_balance.html(`<i>Balance:</i> &nbsp; $ ${ui.item.customer.balance}`)
                        
                    $payments.payment_recv_job_id.html(payment_job_html);					
                    $payments.payment_recv_job_id.show();
    
                    
            
                //alert($payments.payment_recv_search_name.val());
    
            }
            
        });

    })

}