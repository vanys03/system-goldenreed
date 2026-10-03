<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anydesk extends Model
{
    /** Contraseña sugerida al registrar un acceso nuevo; el usuario puede cambiarla. */
    public const CONTRASENA_POR_DEFECTO = 'goldenred312';

    protected $fillable = [
        'codigo',
        'contrasena',
        'torre',
    ];

    public function imagenes(): HasMany
    {
        return $this->hasMany(AnydeskImagen::class);
    }
}
