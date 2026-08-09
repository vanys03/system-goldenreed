<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contador extends Model
{
    protected $table = 'contadores';
    protected $primaryKey = 'nombre';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['nombre', 'valor'];

    public static function siguienteFolioCliente(): int
    {
        return DB::transaction(function () {
            $contador = self::lockForUpdate()->findOrFail('folio_cliente');
            $contador->valor++;
            $contador->save();

            return $contador->valor;
        });
    }

    public static function previewSiguienteFolioCliente(): int
    {
        return (int) self::where('nombre', 'folio_cliente')->value('valor') + 1;
    }
}
