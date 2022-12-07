<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket;


class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $appends = ['display_name'];

    protected $fillable = ['active'
                            ,'address'
                            ,'address2'
                            ,'credit'
                            ,'city'
                            ,'company'
                            ,'first_name'
                            ,'id'
                            ,'last_name'
                            ,'mi'
                            ,'phone'
                            ,'phone_ext'
                            ,'state'
                            ,'tax_exempt'
                            ,'use_company'
                            ,'zip'
                            ,'email'];


    public function debts() {

        return $this->hasMany(Ticket::class, 'customer_id', 'id')
            ->where('refund', false)
            ->where(function($q) {
                $q->whereIn('payment_type', ['acct','svc_charge','payment_type','acct_cash', 'acct_check']);
            })
           ->selectRaw('customer_id, SUM(total) as sum_total')
            ->groupBy('customer_id');
    }

    public function payments() {

        return $this->hasMany(Ticket::class, 'customer_id', 'id')
            ->whereRaw("payment_type like 'payment_%' or payment_type='discount'")
        ->selectRaw('customer_id, SUM(total) as sum_total')
            ->groupBy('customer_id');

    }

    public function returns() {

        return $this->hasMany(Ticket::class, 'customer_id', 'id')
            ->where('refund', true)
            ->where('payment_type','acct')
            ->selectRaw('customer_id, SUM(total) as sum_total')
            ->groupBy('customer_id');
    }

    public function getDisplayNameAttribute() {
        if($this->use_company)
            return $this->company;
        else 
            return $this->last_name . ', ' . $this->first_name;
        
    }

}
