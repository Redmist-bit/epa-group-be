<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use DateTimeInterface;

class Mutasi extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $table = 'MasterKasBank';
    protected $casts = [
        'Total' => 'decimal:2',
        // 'Tanggal' => 'datetime:d-m-Y'
        'JumlahCetak' => 'integer'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    public function items()
    {
        return $this->hasMany(ItemsMutasi::class,'KodeNota','KodeNota');
    }
    public function author()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function modifier()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
    public function perkiraan()
    {
        return $this->belongsTo(Coa::class,'PerkiraanKasBank','Kode');
    }
}
