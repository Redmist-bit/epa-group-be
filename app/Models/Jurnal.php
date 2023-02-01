<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use DateTimeInterface;

class Jurnal extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'MasterJurnal';
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $casts = [
        'Total' => 'decimal:2',
        'JumlahCetak' => 'integer'
        // 'Tanggal' => 'datetime:d-m-Y'
    ];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function detail()
    {
        return $this->hasMany(JurnalDetail::class,'KodeNota','KodeNota');
    }
}
