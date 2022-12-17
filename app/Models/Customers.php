<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Customers extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $table = 'Pelanggan';
    protected $casts = [
        'Aktif' => 'boolean',
    ];
    public function author()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function modifier()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
