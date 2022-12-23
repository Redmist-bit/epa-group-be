<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemsPiutangInvoice extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'DetailPiutang';
    public $timestamps = false;
    use HasFactory;

    protected $casts = [
        'Jumlah' => 'decimal:2'
    ];
    
    protected $fillable = [
        'Faktur','Keterangan','Jumlah'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class,'Faktur','KodeNota');
    }
}
