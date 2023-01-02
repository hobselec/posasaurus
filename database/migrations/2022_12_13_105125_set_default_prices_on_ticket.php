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

            // laravel float() changes these to double!

            DB::statement('alter table ticket change column discount discount float(8,2) default 0');
            DB::statement('alter table ticket change column tax tax float(8,2) default 0');
            DB::statement('alter table ticket change column subtotal subtotal float(8,2) default 0');
            DB::statement('alter table ticket change column total total float(8,2) default 0');
            DB::statement('alter table ticket change column freight freight float(8,2) default 0');
            DB::statement('alter table ticket change column labor labor float(8,2) default 0');

            DB::statement('update ticket set freight=0 where freight is null');
            DB::statement('update ticket set labor=0 where labor is null');
            DB::statement('update ticket set discount=0 where discount is null');

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
