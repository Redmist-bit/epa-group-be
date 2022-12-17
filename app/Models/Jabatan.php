<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Jabatan extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'jabatan','perusahaan'
    ];

    public function Menus()
    {
        return $this->belongsToMany(Menu::class)->withPivot('permission','context_menu','print_out')->withTimestamps();
    }
}
