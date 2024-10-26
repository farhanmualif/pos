<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    use HasFactory, HasUuids;

    protected $table = "keranjang";

    protected $guarded = [''];

    public $primaryKey = "id";

    protected $keyType = "string";

    public $autoIncrement = false;

    public $timestamps = false;


    # baru
    // Di model Keranjang
    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitraId');
    }

    public function keranjangDetails()
    {
        return $this->hasMany(KeranjangDetail::class, 'keranjangId');
    }

    // Di model KeranjangDetail
    public function keranjang()
    {
        return $this->belongsTo(Keranjang::class, 'keranjangId');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produkId');
    }
}
