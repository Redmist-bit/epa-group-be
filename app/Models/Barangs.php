<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Barangs extends Model implements Auditable
{
   use HasFactory;
   use \OwenIt\Auditing\Auditable;
   // use \Staudenmeir\EloquentParamLimitFix\ParamLimitFix;
   const CREATED_AT = 'DiBuatTgl';
   const UPDATED_AT = 'DiUbahTgl';
   protected $table = 'Barang';
   protected $casts = [
      'Aktif' => 'boolean',
   ];
      public function satuan(){
         return $this->hasMany(Satuan::class,'Barang','Kode');
      }
      public function hrgjual(){
         return $this->hasMany(HargaJual::class,'Barang','Kode');
      }
      public function hrgbeli(){
         return $this->hasMany(HargaBeli::class,'Barang','Kode');
      }
      public function stoklimit(){
         return $this->hasOne(StokLimit::class,'Barang','Kode');
      }
      public function gudang(){
         return $this->belongsTo(Gudangs::class,'Kode','Gudang');
      }
      public function author()
      {
         return $this->belongsTo(Gudangs::class,'created_by');
      }
      public function modifier()
      {
         return $this->belongsTo(User::class,'updated_by');
      }
      public function stok(){
         return $this->belongsTo(Stok::class,'Kode','Barang')->where('Periode', function($q){
            $q->select('Kode')
            ->from('Periode')
            ->orderByDesc('id')
            ->limit(1);
         })->with('gudang:Kode,Nama')->withDefault(['StokAkhir'=>0]);
      }
}
