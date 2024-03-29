<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Models\Customer;
use App\Models\CustomerJob;

use Carbon\Carbon;
use Cache;

class CustomerController extends Controller
{
    public function getCustomers(Request $request) {



       // $result = DB::select("(SELECT company, id, last_name, first_name FROM customers WHERE use_company=1 $active) UNION (SELECT CONCAT(last_name, ', ', first_name, ' ', mi), id, last_name, first_name FROM customers WHERE use_company=0 $active) ORDER BY company ASC");
       $results = Customer::where('active', true);
       if($request->show_inactive)
            $results = $results->orWhere('active', false);

        $customers = $results->get()->sortBy('display_name')->values();

        return response()->json(['customers' => $customers]);
    }

    public function getCustomer(Request $request) {

        $customer = DB::select("select * from customers where id=$request->id");

        return response()->json($customer[0] ?? []);
    }

    public function getCustomerJobs(Request $request) {

        $jobs = DB::select("SELECT * FROM customer_jobs WHERE customer_id=$request->id");

        return response()->json($jobs);
    }

    public function searchCustomer(Request $request) {

        $q = '%' . $request->q . '%';

        $showBalance = $request->showBalances ?? false;

       // $results = DB::select("SELECT first_name, last_name, company, CONCAT(last_name, ' ', first_name, ' ' , mi) AS customer_reverse, CONCAT(first_name, ' ', mi, ' ', last_name) as customer, CONCAT(first_Name, ' ', last_name) AS customer_short, id, tax_exempt, use_company FROM customers HAVING first_name like '$q%' OR last_name like '%$q%' OR customer_short LIKE '$q%' OR customer like '$q%' OR customer_reverse like '$q%' OR company LIKE '%$q%'");
        $relations = ['jobs'=>function($q) {
            $q->where('active', true);
        }];

        $endDate = Carbon::now();

        $balanceData = [];
        
        if($showBalance)
            $balanceData = Cache::get('balances');
        else
            $balances = [];

        $results = Customer::where('first_name', 'like', $q)
                            ->orWhere('last_name', 'like', $q)
                            ->orWhere('company', 'like', $q)
                            ->with($relations)
                            ->get();



        $results = $results->map(function($c) use($showBalance, $balanceData) {
                
                if($showBalance)
                {
                    $itemIndex = array_search($c->id, array_column($balanceData, 'id'));

                    $itemIndex ? $balance = $balanceData[$itemIndex]['balance'] : $balance = 0;
                } else
                    $balance = null;

                $obj = ['display_name' => $c->display_name, 
                        'id' => $c->id,
                        'balance' => $balance,
                        'jobs' => $c->jobs];

                return $obj;
        });
                


              //              ->get();
       
       // first_name like '$q%' OR last_name like '%$q%' OR customer_short LIKE '$q%' OR customer like '$q%' OR customer_reverse like '$q%' OR company LIKE '%$q%'

        return response()->json($results);
    }

    public function save(Request $request) {

        $customerData = $request->customer;

        if($customerData['id'] > 0)
            $customer = Customer::find($customerData['id']);
        else
            $customer = new Customer();

        $customer->fill($customerData);

        $customer->save();

        return response()->json($customer);
    }

    public function saveJob(Request $request)
    {
        if($request->job_id == 'new')
            $job = new CustomerJob();
        else
            $job = CustomerJob::find($request->job_id);

        $job->name = $request->job_name;
        $job->customer_id = $request->customer_id;
        $job->save();

        return response()->json(['status'=> true]);
    }
}
