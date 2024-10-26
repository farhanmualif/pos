<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    use HasFactory, HasUuids;
    protected $table = "mitra";

    protected $guarded = [''];

    public $primaryKey = "id";

    protected $keyType = "string";

    public $autoIncrement = false;

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'mitraId');
    }
}
