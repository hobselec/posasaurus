<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ReceiptEmail;
use Illuminate\Support\Facades\Mail;


class TestController extends Controller
{
    public function mail()
    {
        $obj = (object) ['receipt' => 'stuff'];

        Mail::to('jer.house@gmail.com')->send(new ReceiptEmail($obj));

        echo 'ok';
    }
}
