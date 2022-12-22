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

        foreach($this->params['customers'] as $customerId)
        {

            $statementData = StatementHelper::getStatement($customerId, $startDate, $endDate, true);

            $statementHtml .= $statementData->statement;
            foreach($statementData->invoices as $invoice)
                $statementHtml .= $invoice;
    
        }

        $dompdf->loadHtml($statementHtml);
        $dompdf->render();

        $pdf = $dompdf->output();
        $filename = 'statement_' . date("YmdHis") . '.pdf';

        Storage::disk('local')->put($filename, $pdf);

    }
}
