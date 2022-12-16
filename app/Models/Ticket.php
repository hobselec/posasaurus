<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\TransactionItem;

class Ticket extends Model
{
    use HasFactory;

    public $timestamps;

    protected $table = 'ticket';

    protected $dates = ['date'];

    protected $appends = ['display_type', 'display_date'];

    public function items() {

        return $this->hasMany(TransactionItem::class, 'ticket_id', 'id');
    }

    public function customer() {

        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function job() {

        return $this->hasOne(CustomerJob::class, 'id', 'job_id');
    }
    /**
     * set the subtotal to 0 if null
     *
     */
    public function getSubtotalAttribute($value)
    {
        return $value ?? 0;
    }

    public function getTotalAttribute($value)
    {
        return $value ?? 0;
    }
    public function getDiscountAttribute($value)
    {
        return $value ?? 0;
    }

    public function getDisplayTypeAttribute() {

        $payment_type = strtoupper($this->payment_type);

        if($this->payment_type == 'ACCT_CASH')
			$payment_type = 'CASH REFUND';
		else if($this->payment_type == 'ACCT_CHECK')
			$payment_type = 'CHECK REFUND';
        else if(str_contains($this->payment_type, "payment_"))
            $payment_type = 'PAYMENT';
        else if($this->payment_type == 'acct')
            $payment_type = 'CHARGE';
        else if($this->payment_type == 'svc_charge')
            $payment_type = 'SVC CHG';
        else if($this->payment_type == 'discount')
            $payment_type = 'DISCOUNT';

        return $payment_type;
        
    }

    public function getDisplayDateAttribute() {

        if(!$this->date)
            return null;

        return $this->date->format('m/d/y g:i a');
    }
}
