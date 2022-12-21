<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemsHutangPembelian extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'DetailHutang';
    public $timestamps = false;
    use HasFactory;

    protected $casts = [
        'Jumlah' => 'decimal:2'
    ];
    
    protected $fillable = [
        'Faktur','Keterangan','Jumlah'
    ];

    public function faktur()
    {
        return $this->belongsTo(Pembelians::class,'Faktur','KodeNota');
    }
}
