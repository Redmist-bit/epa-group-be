<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class Gudangs extends Model implements Auditable
{
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    use HasFactory;
    protected $table = 'Gudang';
    protected $casts = [
        'Aktif' => 'boolean',
    ];

    use \OwenIt\Auditing\Auditable;
}
