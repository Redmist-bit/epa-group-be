<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Satuan extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use \Awobaz\Compoships\Compoships;
    protected $table = 'Satuan';
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $casts = [
        'Rasio' => 'integer',
    ];
    protected $guarded = [];
    public function hrgbeli(){
        return $this->belongsTo(HargaBeli::class,['Barang','Rasio'],['Barang','Rasio']);
    }
    public function hrgjual(){
        return $this->belongsTo(HargaJual::class,['Barang','Rasio'],['Barang','Rasio']);
    }
}
