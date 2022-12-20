<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemsPurchaseOrders extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use \Awobaz\Compoships\Compoships;
    use HasFactory;
    protected $table = 'DetailPO';
    // protected $primaryKey = null;
    // public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'KodeNota',
        'NoUrut',
        'Barang',
        'Gudang',
        'Keterangan',
        'NoPR',
        'Jumlah',
        'Rasio',
        'Harga',
        'Diskon1',
        'NoIPO',
        'ETA',
        'Unit',
        'Site'
    ];
    protected $casts = [
        'Jumlah' => 'integer',
        'Terpenuhi' => 'integer',
        'Harga' => 'float',
        'NoUrut' => 'integer',
        'Diskon1' => 'float',
        'Diskon' => 'decimal:2',
        'SubTotal' => 'decimal:2'
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
    public function detailBeli()
    {
        return $this->belongsTo(ItemsPembelians::class,['Barang','KodeNota'],['Barang','NoPO'])->where('KodeNota','LIKE','%FB%');
    }
    public function rpl()
    {
        return $this->belongsTo(RecomendPart::class,['Barang','NoPR'],['Barang','NoPartOrder']);
    }
    public function masterpo()
    {
        return $this->belongsTo(PurchaseOrders::class,'KodeNota','KodeNota');
    }
    public function itemsbeli()
    {
        return $this->hasMany(ItemsPembelians::class,'NoPO','KodeNota');
    }
}
