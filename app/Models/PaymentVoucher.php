<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PaymentVoucher extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'MasterPreKasBank';
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $casts = [
        'Total' => 'decimal:2',
        // 'Tanggal' => 'datetime:d-m-Y'
        'JumlahCetak' => 'integer'
    ];
    
    public function detail()
    {
        return $this->hasMany(ItemsPaymentVoucher::class,'KodeNota','KodeNota');
    }
}
