<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'DetailJurnal';
    public $timestamps = false;
    protected $casts = [
        'Jumlah' => 'decimal:2',
        'Kurs' => 'decimal:2',
        'JumlahAsing' => 'decimal:2'
    ];
    protected $fillable = [
        'Perkiraan',
        'Departemen',
        'JenisWorkOrder',
        'Jumlah',
        'JumlahAsing',
        'NoUrut',
        'Keterangan',
        'Lokasi',
        'MataUang',
        'NomorWO',
        'Sisi'
    ];

    public function perkiraan()
    {
        return $this->belongsTo(Coa::class,'Perkiraan','Kode');
    }
    public function mataUang()
    {
        return $this->belongsTo(MataUangs::class,'MataUang','Kode');
    }
}
