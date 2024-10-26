<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokProduk extends Model
{
    use HasFactory, HasUuids;

    protected $table = "stok_produk";

    protected $guarded = [''];

    public $primaryKey = "id";

    protected $keyType = "string";

    public $autoIncrement = false;

    public $timestamps = false;

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produkId', 'id');
    }
}
