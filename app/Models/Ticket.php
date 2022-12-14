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

}
