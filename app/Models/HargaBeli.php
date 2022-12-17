<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class HargaBeli extends Model implements Auditable
{
    use HasFactory;
    use \Awobaz\Compoships\Compoships;
    use \OwenIt\Auditing\Auditable;
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $table = 'HrgBeli';
    protected $guarded = [];
    protected $casts = [
        'Harga' => 'decimal:2'
    ];
    public function barang()
    {
        return $this->belongsTo(Barangs::class,'Barang','Kode');
    }
    public function satuan()
    {
        return $this->belongsTo(Satuan::class,['Barang','Rasio'],['Barang','Rasio']);
    }
    public function mataUang()
    {
        return $this->belongsTo(MataUangs::class,'MataUang','Kode');
    }
}
