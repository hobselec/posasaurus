<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use DB;

class ChangeCatalogIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This adds the catalog primary id to transaction_items
     * php artisan db:seed --class=ChangeCatalogIdSeeder
     *
     * @return void
     */
    public function run()
    {

        DB::update("update transaction_items ti join catalog c on c.barcode=ti.legacy_item_barcode set ti.catalog_id=c.id");
    }
}
