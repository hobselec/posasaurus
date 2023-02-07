export function billingDialogs() {

    document.addEventListener("DOMContentLoaded", () => {

        $billing.reports_dialog.dialog({ title : 'Reports', autoOpen: false, modal : false, resizable : false, draggable : true, width: 850, height: 600 });

        $billing.customer_bill_dialog.dialog(
            { title : 'Tickets', autoOpen: false, modal : true, resizable : false, draggable : true, 
                width: 1150, height: 600, close : function() {	
                $('#billing_data_view').show();
                $('#billing_statement_view').hide() 
            }
         });
    
        $('#billing_adjustment_dialog').dialog({ title : 'Billing Adjustment', 
        open : function() {
            let customerId = $cmenu.id
            
            for(let i = 0; i < $billing.dataRows.length; i++)
            {
                if($billing.dataRows[i].id == customerId)
                {
    
                    $billing.adjustment.displayName.html($billing.dataRows[i].name)
    
                    let jobs = `<option value="">&ndash;</option>`
                    $billing.dataRows[i].jobs.forEach(item => {
                        jobs += `<option value="${item.id}">${item.name}</option>`
                    })
    
                    $billing.adjustment.jobId.html(jobs)
                    break
                }
            }
    
            // todo: add jobs
    
            $('#billing_adjustment_refund').prop('checked', true)
            $billing.adjustment.customerId = customerId
            $billing.adjustment.jobs.hide
            $billing.adjustment.amount.val('')
    
        },
        close : function() {
            $billing.adjustment.displayName.html('')
            $billing.adjustment.refundFormat.show()
            $billing.adjustment.customerId = ''
            $billing.adjustment.jobId.html('')
        },
        autoOpen: false, modal : true, resizable : false, draggable : true, width: 450, height: 500 });
    

    })

}