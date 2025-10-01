<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstatusDisponibilidadEnum;

class Ejemplar extends Model
{
    protected $casts = [
        'estatus' => EstatusDisponibilidadEnum::class,
    ];

    protected $fillable = [
        'catalogo_id',
        'nro_ejemplar',
        'codigo',
        'estatus',
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class);
    }
}
