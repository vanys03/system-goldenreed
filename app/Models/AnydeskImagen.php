<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnydeskImagen extends Model
{
    protected $table = 'anydesk_imagenes';

    protected $fillable = [
        'anydesk_id',
        'ruta',
    ];

    public function anydesk(): BelongsTo
    {
        return $this->belongsTo(Anydesk::class);
    }
}
