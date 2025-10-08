<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstatusDisponibilidadEnum;
use App\Models\Traits\HasApiFeatures;

class Ejemplar extends Model
{
    use HasApiFeatures;

    protected $casts = [
        'estatus' => EstatusDisponibilidadEnum::class,
    ];

    protected $fillable = [
        'catalogo_id',
        'nro_ejemplar',
        'codigo',
        'estatus',
        'fecha_ingreso',
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class);
    }
}
