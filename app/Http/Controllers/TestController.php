<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ReceiptEmail;
use Illuminate\Support\Facades\Mail;
use Config;

class TestController extends Controller
{
    public function mail()
    {
        //$obj = (object) ['receipt' => 'stuff'];
        $str = 'this is the email';

        Mail::to('jer.house@gmail.com')->send(new ReceiptEmail($str));

        echo 'ok';
    }
}
