<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\CatalogItem;

class TransactionItem extends Model
{
    use HasFactory;

    protected $table = 'transaction_items';

    protected $fillable = [
        'ticket_id', 'qty', 'price' , 'amount','name', 'product_id', 'catalog_id', 'notes'
    ];

    public function catalog()
    {
        return $this->belongsTo(CatalogItem::class, 'catalog_id', 'id');
    }

}
