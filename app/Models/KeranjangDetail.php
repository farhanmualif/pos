<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeranjangDetail extends Model
{
    use HasFactory, HasUlids;

    protected $table = "keranjang_detail";

    protected $guarded = [''];

    public $primaryKey = "id";

    protected $keyType = "string";

    public $autoIncrement = false;

    public $timestamps = false;

    public function produk()
    {
        return $this->belongsTo(Produk::class, "produkId");
    }

    public function keranjang()
    {
        return $this->belongsTo(Keranjang::class, "keranjangId");
    }
}
