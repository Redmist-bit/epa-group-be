<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemsPembelians extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use \Awobaz\Compoships\Compoships;
    use HasFactory;
    protected $table = 'DetailBeli';
    public $timestamps = false;
    protected $casts = [
        'Jumlah' => 'integer',
        'Harga' => 'float',
        'NoUrut' => 'integer',
        'Diskon1' => 'float',
        'Diskon' => 'decimal:2',
        'SubDiskon' => 'decimal:2',
        'SubTotal' => 'decimal:2'
    ];
    protected $fillable = [
        'KodeNota',
        'NoUrut',
        'Barang',
        'Gudang',
        'Keterangan',
        'NoPO',
        'NoBeli',
        'Jumlah',
        'Rasio',
        'Harga',
        'Diskon1',
    ];
    
    public function barang()
    {
        return $this->belongsTo(Barangs::class,'Barang','Kode');
    }
    public function gudang()
    {
        return $this->belongsTo(Gudangs::class,'Gudang','Kode');
    }
    public function satuan()
    {
        return $this->belongsTo(Satuan::class,['Barang','Rasio'],['Barang','Rasio'])->select('Nama','Barang','Rasio');
    }
    public function detailPo()
    {
        return $this->belongsTo(ItemsPurchaseOrders::class,['Barang','NoPO'],['Barang','KodeNota']);
    }
    public function master()
    {
        return $this->belongsTo(Pembelians::class,'KodeNota','KodeNota');
    }
    public function detailBeli()
    {
        return $this->belongsTo(ItemsPembelians::class,['NoPO','Barang'],['NoPO','Barang']);
    }
    public function beliRetur()
    {
        return $this->hasMany(ItemsPembelians::class,['KodeNota','Barang'],['NoBeli','Barang']);
    }
}
