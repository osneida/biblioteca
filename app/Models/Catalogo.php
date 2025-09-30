<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Catalogo extends Api
{
    public function ejemplares()
    {
        return $this->hasMany(Ejemplar::class);
    }
    /**
     * use App\Models\Scopes\FilterScope;
     * use App\Models\Scopes\SortScope;
     *  * Scopes globales configurables por el modelo hijo.
     *  protected static $globalScopes = [
     *       FilterScope::class,
     *       SortScope::class,
     *   ];
     */


    public $fillable = [
        'fecha_registro',
        'tipo_documento',
        'isbn',
        'titulo',
        'sub_titulo',

        'editorial_id'
    ];


    public function autores()
    {
        return $this->belongsToMany(Autor::class, 'autor_catalogo', 'catalogo_id', 'autor_id');
    }

    public function editorial(): BelongsTo
    {
        return $this->belongsTo(Editorial::class);
    }
}
