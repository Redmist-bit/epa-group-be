<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahasaSistem extends Model
{
    use HasFactory;
    protected $table = 'bahasa_sistem';
    const CREATED_AT = 'DibuatTgl';
    const UPDATED_AT = 'DiubahTgl';
}
