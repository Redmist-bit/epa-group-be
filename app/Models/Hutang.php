<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use DateTimeInterface;

class Hutang extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'MasterHutang';
    use HasFactory;
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $casts = [
        'Total' => 'decimal:2',
        'Kurs' => 'decimal:2',
        // 'Tanggal' => 'datetime:d-m-Y'
        'JumlahCetak' => 'integer'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    public function itemspembelian()
    {
        return $this->hasMany(ItemsHutangPembelian::class,'KodeNota','KodeNota');
    }
    public function itemspembayaran()
    {
        return $this->hasMany(ItemsHutangPembayaran::class,'KodeNota','KodeNota');
    }
    public function supplier()
    {
        return $this->belongsTo(Suppliers::class,'Supplier','Kode');
    }
    public function author()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function modifier()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
