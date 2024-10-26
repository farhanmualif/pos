<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Auth\Role;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $guarded = [''];

    protected $keyType = "string";

    public $primaryKey = "id";

    public $autoIncrement = false;

    public function getAksesNameAttribute()
    {
        $aksesNames = [
            1 => 'Super Admin',
            2 => 'Admin',
            3 => 'Karyawan',
        ];

        return $aksesNames[$this->akses] ?? 'Tidak Diketahui';
    }

    public function mitra()
    {
        return $this->hasOne(Mitra::class, 'userId');
    }

    public function karyawan()
    {
        return $this->hasOne(Karyawan::class, 'userId');
    }
}
