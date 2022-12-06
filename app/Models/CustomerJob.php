<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerJob extends Model
{
    use HasFactory;

    protected $table = 'customer_jobs';

    protected $fillable = ['customer_id','name','active'];
}
