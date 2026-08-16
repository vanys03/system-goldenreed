<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folio extends Model
{
    protected $fillable = [
        'numero',
        'cliente_id',
        'impreso_at',
    ];

    protected $casts = [
        'impreso_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
