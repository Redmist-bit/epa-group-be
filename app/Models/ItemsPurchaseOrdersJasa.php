<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemsPurchaseOrdersJasa extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use \Awobaz\Compoships\Compoships;
    protected $table = 'DetailPOPerkiraan';
    public $timestamps = false;
    protected $fillable = [
        'KodeNota',
        'NoUrut',
        'Keterangan',
        'Jumlah',
        'Harga',
        'Diskon1',
        'JenisPekerjaan',
        'Perkiraan'
    ];
    protected $casts = [
        'Jumlah' => 'integer',
        'Harga' => 'float',
        'NoUrut' => 'integer',
        'Diskon1' => 'float',
        'Diskon' => 'decimal:2',
        'SubTotal' => 'decimal:2'
    ];
    public function pekerjaan()
    {
        return $this->belongsTo(JenisPekerjaan::class,'JenisPekerjaan','Kode');
    }
    public function perkiraan()
    {
        return $this->belongsTo(Coa::class,'Perkiraan','Kode');
    }
}
