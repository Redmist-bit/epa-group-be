<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemsPaymentVoucher extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'DetailPreKasBank';
    public $timestamps = false;
    protected $casts = [
        'NoUrut' => 'integer',
        'Kurs' => 'decimal:2',
        'Jumlah' => 'decimal:2',
        'JumlahAsing' => 'decimal:2'
    ];
    protected $fillable = [
        'KodeNota',
        'NoUrut',
        'Perkiraan',
        'MataUang',
        'Kurs',
        'Lokasi',
        'Keterangan',
        'Jumlah',
        'JumlahAsing',
        'Departemen',
        'JenisWorkOrder',
        'NomorWO'
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
