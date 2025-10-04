<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\HasApiFeatures;

class Catalogo extends Model
{
    use HasApiFeatures;

    public $fillable = [
        'fecha_ingreso',
        'tipo_documento',
        'isbn',
        'titulo',
        'subtitulo',
        'fecha_publicacion',
        'descripcion_fisica',
        'notas',
        'editorial_id'
    ];


    public function autores(): BelongsToMany
    {
        return $this->belongsToMany(Autor::class); //, 'autor_catalogo', 'catalogo_id', 'autor_id');
    }

    public function editorial(): BelongsTo
    {
        return $this->belongsTo(Editorial::class);
    }

    public function ejemplares(): HasMany
    {
        return $this->hasMany(Ejemplar::class);
    }
}
