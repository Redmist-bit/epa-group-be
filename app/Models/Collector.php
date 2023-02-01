<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Collector extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $table = 'Collector';
    protected $casts = [
        'Aktif' => 'boolean',
    ];
}
