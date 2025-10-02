<?php

namespace App\Models;

use App\Models\Traits\HasGlobalScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Catalogo extends Model
{
    use HasGlobalScopes;

    public $fillable = [
        'fecha_registro',
        'tipo_documento',
        'isbn',
        'titulo',
        'sub_titulo',
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
