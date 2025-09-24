<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Catalogo extends Api
{
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
        'autor_id',
        'editorial_id'
    ];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Autor::class);
    }

    public function editorial(): BelongsTo
    {
        return $this->belongsTo(Editorial::class);
    }
}
