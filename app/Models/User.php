<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'User';
    const CREATED_AT = 'DiBuatTgl';
    const UPDATED_AT = 'DiUbahTgl';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Nama',
        'Email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'Aktif' => 'boolean'
    ];

    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function unit()
    {
        return $this->hasMany(Unit::class,'DiBuatOleh','Kode');
    }

    public function customers()
    {
        return $this->hasMany(Customers::class,'DiBuatOleh','Kode');
    }

    public function barangs()
    {
        return $this->hasMany(Barangs::class,'DiBuatOleh','Kode');
    }

    public function gudangs()
    {
        return $this->hasMany(Gudangs::class,'DiBuatOleh','Kode');
    }

    public function suppliers()
    {
        return $this->hasMany(Suppliers::class,'DiBuatOleh','Kode');
    }

    public function Perusahaans()
    {
        return $this->hasMany(Perusahaan::class,'DiBuatOleh','Kode');
    }
    public function mekaniks()
    {
        return $this->hasMany(Mekaniks::class,'DiBuatOleh','Kode');
    }
    public function MataUangs()
    {
        return $this->hasMany(MataUangs::class,'DiBuatOleh','Kode');
    }
    public function Periodes()
    {
        return $this->hasMany(Periodes::class,'DiBuatOleh','Kode');
    }
    public function Coa()
    {
        return $this->hasMany(Coa::class,'DiBuatOleh','Kode');
    }
    public function pembelians()
    {
        return $this->hasMany(Pembelians::class,'DiBuatOleh','Kode');
    }
    public function PurchaseOrders()
    {
        return $this->hasMany(PurchaseOrders::class,'DiBuatOleh','Kode');
    }
    public function ReturPembelians()
    {
        return $this->hasMany(ReturPembelians::class,'created_by','id');
    }
    public function SalesOrders()
    {
        return $this->hasMany(SalesOrders::class,'created_by','id');
    }
    public function penjualans()
    {
        return $this->hasMany(Penjualans::class,'created_by','id');
    }
    public function ReturPenjualans()
    {
        return $this->hasMany(ReturPenjualans::class,'created_by','id');
    }
    public function Adjustment()
    {
        return $this->hasMany(Adjustment::class,'DiBuatOleh','Kode');
    }
    public function transfer()
    {
        return $this->hasMany(TransferStok::class,'DiBuatOleh','Kode');
    }
    public function notagudangs()
    {
        return $this->hasMany(NotaGudang::class,'created_by','id');
    }
    public function konversi()
    {
        return $this->hasMany(Konversi::class,'created_by','id');
    }
    public function report()
    {
        return $this->hasMany(Reporting::class,'DiBuatOleh','Kode');
    }
    public function mutasi()
    {
        return $this->hasMany(Mutasi::class,'DiBuatOleh','Kode');
    }
    public function piutang()
    {
        return $this->hasMany(Piutang::class,'DiBuatOleh','Kode');
    }
    public function hutang()
    {
        return $this->hasMany(Hutang::class,'DiBuatOleh','Kode');
    }
    public function satuan()
    {
        return $this->hasMany(Satuan::class,'DiBuatOleh','Kode');
    }
    public function StokLimit()
    {
        return $this->hasMany(StokLimit::class,'DiBuatOleh','Kode');
    }
    public function HargaBeli(){
        return $this->hasMany(HargaBeli::class,'DiBuatOleh','Kode');
    }
    public function HargaJual(){
        return $this->hasMany(HargaJual::class,'DiBuatOleh','Kode');
    }
    public function JenisPekerjaan(){
        return $this->hasMany(JenisPekerjaan::class,'DiBuatOleh','Kode');
    }
    public function Repairers(){
        return $this->hasMany(Repairer::class,'DiBuatOleh','Kode');
    }
    public function nomorRangka(){
        return $this->hasMany(NomorRangka::class,'DiBuatOleh','Kode');
    }
    public function kendaraan(){
        return $this->hasMany(Kendaraan::class,'DiBuatOleh','Kode');
    }
    public function WorkOrder(){
        return $this->hasMany(WorkOrder::class,'DiBuatOleh','Kode');
    }
    public function estimasi(){
        return $this->hasMany(Estimasi::class,'DiBuatOleh','Kode');
    }
    public function invoice(){
        return $this->hasMany(Invoice::class,'DiBuatOleh','Kode');
    }
    public function penagihan(){
        return $this->hasMany(Penagihan::class,'DiBuatOleh','Kode');
    }
    public function pv(){
        return $this->hasMany(PaymentVoucher::class,'DiBuatOleh','Kode');
    }
    public function jurnal(){
        return $this->hasMany(Jurnal::class,'DiBuatOleh','Kode');
    }
}
