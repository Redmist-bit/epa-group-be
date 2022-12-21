<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemsPembeliansJasa extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use \Awobaz\Compoships\Compoships;
    protected $table = 'DetailBeliPerkiraan';
    public $timestamps = false;
    protected $casts = [
        'Jumlah' => 'integer',
        'Harga' => 'float',
        'NoUrut' => 'integer',
        'Diskon1' => 'float',
        'Diskon' => 'decimal:2',
        'SubTotal' => 'decimal:2'
    ];
    protected $fillable = [
        'KodeNota',
        'NoUrut',
        'JenisPekerjaan',
        'Perkiraan',
        'Keterangan',
        'NoPO',
        'Jumlah',
        'Harga',
        'Diskon1',
        'Rasio'
    ];
    public function pekerjaan()
    {
        return $this->belongsTo(JenisPekerjaan::class,'JenisPekerjaan','Kode');
    }
    public function perkiraan()
    {
        return $this->belongsTo(Coa::class,'Perkiraan','Kode');
    }
    public function detailPo()
    {
        return $this->belongsTo(ItemsPurchaseOrdersJasa::class,['NoUrut','NoPO'],['NoUrut','KodeNota']);
    }
}
