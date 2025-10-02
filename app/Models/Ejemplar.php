<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstatusDisponibilidadEnum;
use App\Models\Traits\HasGlobalScopes;

class Ejemplar extends Model
{
    use HasGlobalScopes;

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
