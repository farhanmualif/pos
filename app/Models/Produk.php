<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory, HasUuids;

    protected $table = "produk";

    protected $guarded = [''];

    protected $keyType = "string";

    public $primaryKey = "id";

    public $timestamps = false;

    public function stokProduk()
    {
        return $this->hasMany(StokProduk::class, 'produkId', 'id');
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitraId');
    }
}
