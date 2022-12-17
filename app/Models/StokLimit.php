<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class StokLimit extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'StokLimit';
    protected $guarded = [];
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
}
