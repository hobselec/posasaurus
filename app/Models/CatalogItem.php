<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogItem extends Model
{
    use HasFactory;

    protected $table = 'catalog';

    protected $fillable = ['barcode','name','manufacturer_id','price','qty','sku','vendor'];

    public function getVendorNameAttribute($value)
    {
        return $value ?? '';
    }
    public function getManufacturerIdAttribute($value)
    {
        return $value ?? '';
    }

    public function getProductIdAttribute($value)
    {
        return $value ?? '';
    }

    public function getBarcodeAttribute($value)
    {
        return $value ?? '';
    }
}
