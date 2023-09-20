<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Helpers\StatementHelper;

use Config;
use Dompdf\Dompdf;

use Storage;
use App\Mail\ReceiptEmail;
use Illuminate\Support\Facades\Mail;


class PrintStatements implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ['customers' => array of ids, 'endDate' => date]
     */
    public array $params = [];

    /**
     * Create a new job instance.
     *
     * @param array $jobParams ['sendTo' => string email, 'endDate' => date, 'customers' => array of customer ids]
     * @return void
     */
    public function __construct(array $jobParams)
    {
        $this->params = $jobParams;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dompdf = new Dompdf();

        $endDate = $this->params['endDate'];
        $startDate = $endDate->copy()->subDays(30);

        if(count($this->params['customers']) == 0)
            return;

        $statementHtml = '';

        foreach($this->params['customers'] as $i => $customerId)
        {

            $statementData = StatementHelper::getStatement($customerId, $startDate, $endDate, true);

            if($i > 0)
                $statementData->statement = "<div class=\"page_break\">" . $statementData->statement . "</div>";

            $statementHtml .= $statementData->statement;
            foreach($statementData->invoices as $invoice)
                $statementHtml .= $invoice;
    
        }

        $dompdf->loadHtml($statementHtml);
        $dompdf->render();

        $pdf = $dompdf->output();
        $filename = 'statement_' . date("YmdHis") . '.pdf';

        Storage::disk('local')->put($filename, $pdf);

        // e-mail
        $email = $this->params['sendTo'];
        $obj = (object) ['message' => 'Statement is attached', 'subject' => 'Statement from Point of Sale', 'attachment' => $filename];
        Mail::to($email)->send(new ReceiptEmail($obj));

    }
}
