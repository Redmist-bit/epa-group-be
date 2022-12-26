<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use DateTimeInterface;

class Piutang extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'MasterPiutang';
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
    public function itemspembayaran()
    {
        return $this->hasMany(ItemsPiutangPerkiraan::class,'KodeNota','KodeNota');
    }
    public function itemsinvoice()
    {
        return $this->hasMany(ItemsPiutangInvoice::class,'KodeNota','KodeNota');
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class,'Pelanggan','Kode');
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
