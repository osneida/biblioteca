<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogo;

class Autor extends Api
{
    protected $fillable = [
        'nombre',
        'nacionalidad',
        'fecha_nacimiento',
        'fecha_fallecimiento',
        'biografia'
    ];

    public function catalogos()
    {
        return $this->belongsToMany(Catalogo::class, 'autor_catalogo', 'autor_id', 'catalogo_id');
    }
}
