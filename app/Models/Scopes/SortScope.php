<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SortScope implements Scope
{

    public function apply(Builder $builder, Model $model): void
    {
        $sort = request('sort');

        if (empty($sort)) {
            return;
        }

        // -------------------------------------------------------------------
        // FIX CRÍTICO: Evitar fuga del Sort a las subconsultas de relaciones.
        // -------------------------------------------------------------------
        // 1. Obtener la tabla del modelo base (ej: 'editoriales').
        $modelTableName = $model->getTable();

        // 2. Obtener la tabla que se está consultando actualmente.
        // Usamos el from de la consulta interna para saber a qué tabla aplica el builder.
        $currentQueryTable = $builder->getQuery()->from;

        // 3. Si la tabla actual NO es la tabla del modelo base,
        // (es decir, es una consulta de eager loading como 'catalogos'),
        // NO aplicamos el sort de la Request.
        if ($currentQueryTable !== $modelTableName) {
            return;
        }

        $sortArray = explode(',', $sort);
        foreach ($sortArray as $sortItem) {
            $direction = 'asc';
            if (str_starts_with($sortItem, '-')) {
                $direction = 'desc';
                $sortItem = substr($sortItem, 1);
            }
            $builder->orderBy($sortItem, $direction);
        }
    }
}
