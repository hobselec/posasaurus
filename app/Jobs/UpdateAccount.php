<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Cache;

use App\Helpers\BillingHelper;
use App\Events\UpdateBilling;

class UpdateAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ['customerId' => int]
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
        $balanceData = BillingHelper::getCustomerBalanceData($this->params['customerId']);

        $cache = Cache::get('balances');

        $itemIndex = array_search($this->params['customerId'], array_column($cache, 'id'));

        if(!$itemIndex)
            throw new \Exception('Could not find customer in cache');

        $cache[$itemIndex] = $balanceData;

        Cache::put('balances', $cache);

        $balanceData['jobs'] = null; // not needed
        event (new UpdateBilling($balanceData));
    }
}
