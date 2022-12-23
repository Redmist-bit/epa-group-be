<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InvoiceDetailPekerjaan extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    // protected $table = 'DetailInvoicePekerjaan';
    protected $table = 'DetailInvoicePerkiraan';
    // const CREATED_AT = 'DiBuatTgl';
    // const UPDATED_AT = 'DiUbahTgl';
    public $timestamps = false;
    protected $fillable = [
        'Diskon1',
        'Harga',
        'KodeNota',
        'NoUrut',
        'Perkiraan',
        // 'Gudang',
        'Keterangan',
        'Rasio',
        'Jumlah',
        'JenisPekerjaan'
    ];
    protected $casts = [
        'Jumlah' => 'integer',
        'Rasio' => 'integer',
        'Harga' => 'decimal:2',
        'Diskon' => 'decimal:2',
        'Diskon1' => 'float',
        'SubTotal' => 'float'
    ];
    public function kerja()
    {
        return $this->belongsTo(JenisPekerjaan::class,'JenisPekerjaan','Kode')
        ->select('Kode','Nama');
    }
    public function perkiraan()
    {
        return $this->belongsTo(Coa::class,'Perkiraan','Kode')
        ->select('Kode','Nama');
    }
}
