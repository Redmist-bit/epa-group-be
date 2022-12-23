<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Invoice extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'MasterInvoice';
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $casts = [
        'OnRisk' => 'decimal:2',
        'Kurs' => 'decimal:2',
        'PPn' => 'decimal:2',
        'DPP' => 'decimal:2',
        'PPnPersen' => 'decimal:2',
        'PPnPersenManual' => 'decimal:2',
        'Diskon' => 'decimal:2',
        'KAss' => 'decimal:2',
        'KTtg' => 'decimal:2',
        'Ddtb' => 'decimal:2',
        'KPpn' => 'decimal:2',
        'Kexc' => 'decimal:2',
        'Kund' => 'decimal:2',
        'Total' => 'decimal:2',
        'TotalBayar' => 'decimal:2',
        'Terbayar' => 'decimal:2',
        'SisaBayar' => 'decimal:2',
        'JumlahCetak' => 'integer'
        // 'Tanggal' => 'datetime:Y-m-d',
        // 'TglKirim' => 'datetime:d/m/Y',
        // 'TglKonfirmasiTerima' => 'datetime:d/m/Y'
    ];
    protected $fillable = [
        'TglKirim',
        'TglKonfirmasiTerima',
        'NoResi',
        'NoFakturPajak'
    ];
    public function barang()
    {
        return $this->hasMany(InvoiceDetail::class,'KodeNota','KodeNota');
    }

    public function pekerjaan()
    {
        return $this->hasMany(InvoiceDetailPekerjaan::class,'KodeNota','KodeNota');
    }

    public function wo(){
        return $this->belongsTo(WorkOrder::class,'NomorWO','KodeNota')
        ->select('KodeNota','Pelanggan','NomorPolisi','Pemilik','JenisWorkOrder','Lokasi','Keterangan');
    }

    public function uang(){
        return $this->belongsTo(MataUangs::class,'MataUang','Kode');
    }

    public function deductible(){
        return $this->hasOne(InvoiceDetailDeductible::class,'KodeNota','KodeNota');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Customers::class,'Pelanggan','Kode');
    }
    public function rangka()
    {
        return $this->belongsTo(NomorRangka::class,'NomorRangka','NomorRangka');
    }
}
