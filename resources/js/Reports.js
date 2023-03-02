
    
export function saveOpeningBalance(event = null)
{
    var open_val = $('#open_cash').val(); 
		
    // check input is valid
    if(isNaN(open_val) || open_val == '' || open_val < 0)
    {
        alert("Please enter the opening cash value");
        return false;
    }

    let tmpamt = open_val.toString();

    
    if(tmpamt.indexOf('.') == '-1' && $pos.useAutoDecimal)
        open_val /= 100;
    
    if(event)
    {
        if(event.key != 'Enter')
            return
    }

    axios.post('/pos/journal/open', { amount : open_val }).then((response) => {

		openingBalanceModal.hide()

            
        }).catch(() => {
            show_note("An error occurred")
        })

}

export function print_end_report()
{

    
    var counted_cash = $pos.closing_cash.val();
    var counted_checks = $pos.closing_checks.val();
    
    if(counted_cash.indexOf('.') == '-1' && $pos.useAutoDecimal && counted_cash != 0)
        counted_cash /= 100;
        
    if(counted_checks.indexOf('.') == '-1' && $pos.useAutoDecimal && counted_checks != '0')
        counted_checks /= 100;
    

    if((isNaN(counted_cash) || isNaN(counted_checks)) || counted_cash == '' || counted_checks == '')
    {
        alert("Please provide the amount of checks and cash");
        return false;
    }

    axios.post('/pos/journal/close', { 'cash' : counted_cash, 'checks' : counted_checks, 'printLabel' : 1 }).then((response) => {
    
            $pos.closing_checks.blur();
    
            //alert(data_xml);
            
    }).catch(() => {
        show_note("Error")
    })

}



export function show_shutdown_dialog()
{
    $('.posdlg').hide();
    $(".ui-dialog-titlebar-close").show();
    $('#shutdown_dialog').dialog('open'); 

}


export function print_weekly_report()
{
    if($reports.start_date.val() == '' || $reports.end_date.val() == '')
    {
        $reports.weekly_report_fields.show();
        return;
    }


    $.get('weekly_report.php', { start : $('#report_start_date').val(), end : $('#report_end_date').val() }, function(response) {

    

    });
}
