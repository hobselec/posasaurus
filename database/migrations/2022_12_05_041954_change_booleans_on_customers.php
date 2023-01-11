<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('tax_exempt')->default(false)->change();
            $table->boolean('active')->default(true)->change();
            $table->boolean('use_company')->default(false)->change();
            $table->boolean('credit')->default(true)->change();
            $table->boolean('print_statement')->default(false)->change();
            $table->char('email', 100)->nullable();
        
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
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
