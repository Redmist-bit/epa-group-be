<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Menu extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'Parent','Nama'
    ];
    protected $casts = [
        'crudAction' => 'boolean'
    ];

    public function jabatans()
    {
        return $this->belongsToMany(Jabatan::class);
    }
    public function child()
    {
        return $this->hasMany(Menu::class,'parent','id');
    }
    public function children()
    {
        return $this->child()->with('children:id,parent,nama');
    }
}
