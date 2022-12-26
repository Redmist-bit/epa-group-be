<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemsHutangPembayaran extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'DetailHutangPerkiraan';
    public $timestamps = false;
    use HasFactory;

    protected $casts = [
        'Jumlah' => 'decimal:2',
        'NoUrut' => 'integer'
    ];

    protected $fillable = [
        'NoUrut','Keterangan','Perkiraan','Jumlah'
    ];

    public function perkiraan()
    {
        return $this->belongsTo(Coa::class,'Perkiraan','Kode');
    }
}
