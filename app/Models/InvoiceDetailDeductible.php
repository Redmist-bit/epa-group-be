<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InvoiceDetailDeductible extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'DetailInvoiceDeductible';
    // const CREATED_AT = 'DiBuatTgl';
    // const UPDATED_AT = 'DiUbahTgl';
    // public $incrementing = false;
    // protected $primaryKey = 'KodeNota';
    public $timestamps = false;
    protected $fillable = [
        'KodeNota',
        'Deductible',
        'Surcharge',
        'SelisihProrata',
        'SelisihDepresiasi',
        'SelisihUnderInsured',
        'Diskon',
        'SubTotal',
    ];
    protected $casts = [
        'Deductible' => 'decimal:2',
        'Surcharge' => 'decimal:2',
        'SelisihProrata' => 'decimal:2',
        'SelisihDepresiasi' => 'decimal:2',
        'SelisihUnderInsured' => 'decimal:2',
        'Diskon' => 'decimal:2',
        'SubTotal' => 'decimal:2',
    ];
}
