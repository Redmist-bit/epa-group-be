<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use DateTimeInterface;

class WorkOrder extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'MasterWorkOrder';
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';
    protected $fillable = [
        'KeteranganWIP',
        'Coding',
        'Lessor',
        'Persetujuan',
        'TglSPK',
        'TglTerimaSPK',
        'RFU',
        'AdmClear',
        'ReserveOutcome',
        'ReserveOutcomeJasa',
        'MataUang',
        'Kurs',
        'CekList',
        'Remarks',
        'TglDOL',
        'NoPolisAsuransi',
        'NoRegistrasi',
        'Adjuster',
        'PICAdj',
        'Broker',
        'PICBroker',
        'Analisa',
        'NoteAnalis',
        'DiUbahOleh',
        'PICSite',
        'Surveyor',
        'JenisKerusakan',
        'ProgressPengerjaan',
        'DetailProgress',
        'RemarksSCM',
        'PICSCM1',
        'PICSCM2',
        'OwnRisk',
        'TglOwnRisk'
    ];
    protected $casts = [
        'Kurs' => 'float',
        'DPP' => 'float',
        'PPn' => 'float',
        'PPnPersen' => 'float',
        'PPnPersenManual' => 'float',
        'TotalBayar' => 'float',
        'CekList' => 'boolean',
        'IsClose' => 'boolean',
        'AvailableBudget' => 'decimal:2',
        'AvailableBudgetJasa' => 'decimal:2',
        'Diskon' => 'decimal:2',
        'Penawaran' => 'decimal:2',
        'Persetujuan' => 'decimal:2',
        'ReserveOutcome' => 'decimal:2',
        'ReserveOutcomeJasa' => 'decimal:2',
        'SisaBayar' => 'decimal:2',
        'Terbayar' => 'decimal:2',
        'Total' => 'decimal:2',
        'TotalBayar' => 'decimal:2',
        // 'Tanggal' => 'datetime:d-m-Y',
        'OwnRisk' => 'float',
        'JumlahCetak' => 'integer'
    ];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    public function unit(){
        return $this->belongsTo(Unit::class,'Unit','Kode');
    }
    public function bonBahan(){
        return $this->hasMany(NotaGudang::class,'KodeNota','KodeNota');
    }
    public function bonBahanHrg(){
        return $this->hasMany(ItemsNotaGudang::class,'KodeNota','KodeNota');
    }
    public function keluhan(){
        return $this->hasMany(WoKeluhan::class,'KodeNota','KodeNota');
    }
    public function ipo(){
        return $this->hasMany(RecomendPart::class,'KodeNota','KodeNota');
    }
    public function pelanggan(){
        return $this->belongsTo(Customers::class,'Pelanggan','Kode');
    }
    public function pemilik(){
        return $this->belongsTo(Customers::class,'Pemilik','Kode');
    }
    public function lessor(){
        return $this->belongsTo(Customers::class,'Lessor','Kode');
    }
    public function rangka(){
        return $this->belongsTo(NomorRangka::class,'NomorRangka','NomorRangka')->select('NomorRangka','Kendaraan');
    }
    public function uang(){
        return $this->belongsTo(MataUangs::class,'MataUang','Kode');
    }
    public function rwl(){
        return $this->hasMany(RecomendWork::class,'KodeNota','KodeNota');
    }
    public function repairer(){
        return $this->hasMany(WorkListRepairer::class,'KodeNota','KodeNota');
    }
    public function waktu(){
        return $this->hasMany(WorkListWaktu::class,'KodeNota','KodeNota');
    }
    public function invoice()
    {
        return $this->hasMany(Invoice::class,'NomorWO','KodeNota')
        ->where('KodeNota','not like','%FD%')
        // ->orWhere('KodeNota','like','%FX%')
        ->whereNull('Status')
        // ->where('Pelanggan','!=','MasterWorkOrder.Pemilik')
        ->orderByDesc('KodeNota');
    }
    public function invoiceAss()
    {
        return $this->invoice()->whereColumn('Pelanggan','<>','Pemilik');
    }
    public function invoiceDeductible()
    {
        return $this
        ->hasMany(Invoice::class,'NomorWO','KodeNota')
        ->where('KodeNota','like','%FD%')
        ->whereNull('Status')
        ->orderByDesc('KodeNota');
    }
    // public function invoiceNonClaim()
    // {
    //     return $this
    //     ->hasMany(Invoice::class,'NomorWO','KodeNota')
    //     ->where('KodeNota','like','%FW%')
    //     ->where('Pelanggan','Pemilik')
    //     ->orderByDesc('KodeNota');
    // }
    public function estimasi()
    {
        return $this->hasOne(Estimasi::class,'NomorWO','KodeNota')->whereNull('Status')
        ->orderByDesc('KodeNota');
    }
}
