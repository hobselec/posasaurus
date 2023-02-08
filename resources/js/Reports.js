
    
export function save_opening_balance()
{
    let amt = $('#open_cash').val();
    let tmpamt = amt.toString();

    
    if(tmpamt.indexOf('.') == '-1' && $pos.useAutoDecimal)
        amt /= 100;
        

    axios.post('/pos/journal/open', { amount : amt }).then((response) => {
        
                /*
                $.get('print_opening_journal.php', { amount : amt, printLabel : $pos.useLabelPrinter }, function(data_xml) {
                
                    if($pos.useLabelPrinter) // print is done internally
                    {
                        //var printers = dymo.label.framework.getPrinters();
                        //printer_index = find_dymo_printer();
                    
                        //label = dymo.label.framework.openLabelXml(data_xml);
            
                        //label.print(printers[printer_index].name);
                    } else
                        alert(data_xml);			
                
                });
                */
                
            $('#startup_dialog').dialog('close');
            $(".ui-dialog-titlebar-close").show(); // add back the close button
        
            
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

