<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogo;
use App\Models\Traits\HasGlobalScopes;

class Autor extends Model
{
    use HasGlobalScopes;

    protected $fillable = [
        'nombre',
        'nacionalidad',
        'fecha_nacimiento',
        'fecha_fallecimiento',
        'biografia'
    ];

    public function catalogos()
    {
        return $this->belongsToMany(Catalogo::class);
        //return $this->belongsToMany(Catalogo::class, 'autor_catalogo', 'autor_id', 'catalogo_id');
    }
}
