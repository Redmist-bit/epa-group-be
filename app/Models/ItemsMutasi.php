<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemsMutasi extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    public $timestamps = false;
    protected $table = 'DetailKasBank';
    protected $casts = [
        'Jumlah' => 'decimal:2',
        'JumlahAsing' => 'decimal:2',
        'Kurs' => 'decimal:2',
        'NoUrut' => 'integer'
    ];
    protected $fillable = [
        'Perkiraan',
        // 'Departemen',
        // 'JenisWorkOrder',
        'Jumlah',
        'JumlahAsing',
        'NoUrut',
        'Keterangan',
        // 'Lokasi',
        'MataUang',
        // 'NomorPV',
        // 'NomorWO'
    ];

    public function mutasi()
    {
        return $this->belongsTo(Mutasi::class,'KodeNota','KodeNota');
    }
    public function perkiraan()
    {
        return $this->belongsTo(Coa::class,'Perkiraan','Kode');
    }
    public function mataUang()
    {
        return $this->belongsTo(MataUangs::class,'MataUang','Kode');
    }
    public function pv()
    {
        return $this->belongsTo(PaymentVoucher::class,'NomorPV','KodeNota');
    }
    public function wo()
    {
        return $this->belongsTo(WorkOrder::class,'NomorWO','KodeNota');
    }
}
