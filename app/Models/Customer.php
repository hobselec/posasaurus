<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

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
}
