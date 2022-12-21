<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use DateTimeInterface;

class Pembelians extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'MasterBeli';
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    use HasFactory;
    protected $casts = [
        'Kurs' => 'float',
        'Diskon' => 'decimal:2',
        'DPP' => 'decimal:2',
        'CekFisikInv' => 'boolean',
        'FPComplete' => 'boolean',
        'PPn' => 'decimal:2',
        'PPnPersen' => 'decimal:2',
        'PPnFaktur' => 'decimal:2',
        'Total' => 'decimal:2',
        'TotalBayar' => 'decimal:2',
        'Terbayar' => 'decimal:2',
        'SisaBayar' => 'decimal:2',
        'JumlahCetak' => 'integer'
        // 'Tanggal' => 'datetime:d-m-Y'
    ];
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    public function uang()
    {
        return $this->belongsTo(MataUangs::class,'MataUang','Kode');
    }
    public function itemRetur()
    {
        return $this->hasMany(ItemsPembelians::class,'KodeNota','KodeNota');
    }
    public function itemsJasa()
    {
        return $this->hasMany(ItemsPembeliansJasa::class,'KodeNota','KodeNota');
    }
    public function items()
    {
        return $this->hasMany(ItemsPembelians::class,'KodeNota','KodeNota');
    }
    public function author()
    {
        return $this->belongsTo(User::class,'DiBuatOleh');
    }
    public function modifier()
    {
        return $this->belongsTo(User::class,'DiUbahOleh');
    }
    public function wo()
    {
        return $this->belongsTo(WorkOrder::class,'NoWorkOrder','KodeNota');
    }
    public function supplier()
    {
        return $this->belongsTo(Suppliers::class,'Supplier','Kode');
    }
    public function BillFrom()
    {
        return $this->belongsTo(Suppliers::class,'Supplier','Kode');
    }
    public function SellFrom()
    {
        return $this->belongsTo(Suppliers::class,'Supplier','Kode');
    }
    public function po()
    {
        return $this->belongsTo(PurchaseOrders::class,'NoPO','KodeNota');
    }
    public function beliRetur()
    {
        return $this->hasMany(ItemsPembelians::class,'NoBeli','KodeNota');
    }
}
