<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Set default 0 for float columns
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket', function (Blueprint $table) {

            $table->float('discount', 8, 2)->default(0)->change();
            $table->float('tax', 6, 2)->default(0)->change();
            $table->float('subtotal', 8, 2)->default(0)->change();
            $table->float('total', 8, 2)->default(0)->change();
            $table->float('freight', 8, 2)->default(0)->change();
            $table->float('labor', 8, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
