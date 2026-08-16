<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anydesk extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'contrasena',
        'torre',
    ];
}
