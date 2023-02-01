<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class Penagihan extends Model implements Auditable
{
    use HasFactory;
    protected $table = 'MasterPenagihan';
    use \OwenIt\Auditing\Auditable;
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $casts = [
        'JumlahCetak' => 'integer'
    ];

    public function items()
    {
        return $this->hasMany(ItemsPenagihan::class,'KodeNota','KodeNota');
    }
    public function collector()
    {
        return $this->belongsTo(Collector::class,'Collector','Kode');
    }
}
