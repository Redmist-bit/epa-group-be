<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PurchaseOrders extends Model implements Auditable
{   
    use \OwenIt\Auditing\Auditable;
    protected $table = 'MasterPO';
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $casts = [
        'Kurs' => 'float',
        'Diskon' => 'decimal:2',
        'DPP' => 'decimal:2',
        'PPn' => 'decimal:2',
        'PPnPersen' => 'decimal:2',
        'Total' => 'decimal:2',
        'TotalBayar' => 'decimal:2',
        'JumlahCetak' => 'integer',
        'Apply' => 'boolean'
        // 'Tanggal' => 'datetime:d-m-Y'
    ];
    public function itemsJasa(){
        return $this->hasMany(ItemsPurchaseOrdersJasa::class,'KodeNota','KodeNota');
    }
    public function items(){
        return $this->hasMany(ItemsPurchaseOrders::class,'KodeNota','KodeNota');
    }
    public function author()
    {
        return $this->belongsTo(User::class,'DiBuatOleh','Kode');
    }
    public function modifier()
    {
        return $this->belongsTo(User::class,'DiUbahOleh','Kode');
    }
    public function supplier()
    {
        return $this->belongsTo(Suppliers::class,'Supplier','Kode');
    }
    public function BillFrom()
    {
        return $this->belongsTo(Suppliers::class,'BillFrom','Kode');
    }
    public function SellFrom()
    {
        return $this->belongsTo(Suppliers::class,'SellFrom','Kode');
    }
    public function uang()
    {
        return $this->belongsTo(MataUangs::class,'MataUang','Kode');
    }
    public function wo()
    {
        return $this->belongsTo(WorkOrder::class,'NomorWO','KodeNota');
    }
    use HasFactory;
}
