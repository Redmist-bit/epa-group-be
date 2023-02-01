<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class ItemsPenagihan extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'DetailPenagihan';
    public $timestamps = false;
    protected $fillable = [
        'KodeNota',
        'NoInvoice',
        'Keterangan',
    ];
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class,'NoInvoice','KodeNota');
    }
}
