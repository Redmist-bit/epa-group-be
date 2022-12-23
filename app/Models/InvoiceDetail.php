<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InvoiceDetail extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use \Awobaz\Compoships\Compoships;
    protected $table = 'DetailInvoice';
    // const CREATED_AT = 'DiBuatTgl';
    // const UPDATED_AT = 'DiUbahTgl';
    public $timestamps = false;
    protected $fillable = [
        'Diskon1',
        'Harga',
        'KodeNota',
        'NoUrut',
        'Gudang',
        'Keterangan',
        'Perkiraan',
        'Rasio',
        'Jumlah',
        'BarangBekas',
        'Barang'
    ];
    protected $casts = [
        'BarangBekas' => 'boolean',
        'Harga' => 'float',
        'Diskon1' => 'float',
        'Diskon' => 'decimal:2',
        'SubTotal' => 'float'
    ];
    public function barang()
    {
        return $this->belongsTo(Barangs::class,'Barang','Kode');
    }
    public function barangSp()
    {
        return $this->belongsTo(BarangSP::class,'Barang','Kode');
    }
    public function satuan()
    {
        return $this->belongsTo(Satuan::class,['Barang','Rasio'],['Barang','Rasio'])->select('Nama','Barang','Rasio');
    }
    public function perkiraan()
    {
        return $this->belongsTo(Coa::class,'Perkiraan','Kode');
    }
}
