<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SelectScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // -------------------------------------------------------------------
        // 🔥 FIX CRÍTICO: Autodefensa para evitar la fuga a subconsultas (Eager Loading).
        // Si la tabla actual NO es la tabla del modelo base, ignoramos la aplicación.
        $modelTableName = $model->getTable();
        $currentQueryTable = $builder->getQuery()->from;

        if ($currentQueryTable !== $modelTableName) {
            return;
        }
        // -------------------------------------------------------------------

        if (empty(request('select'))) {
            return;
        }

        $select = request('select');
        $requestedFields = explode(',', $select);

        // 1. Definir campos válidos base
        $validFields = method_exists($model, 'getFillable') ? $model->getFillable() : [];

        // 2. Incluir siempre la clave primaria
        $validFields[] = $model->getKeyName();

        // 3. Incluir las columnas de timestamps si existen
        if ($model->usesTimestamps()) {
            $validFields[] = $model->getCreatedAtColumn();
            $validFields[] = $model->getUpdatedAtColumn();
        }

        // 4. Filtrar campos solicitados contra la lista de campos válidos
        $finalFields = array_intersect($requestedFields, $validFields);

        // Si después del filtrado no queda nada, volvemos a seleccionar solo la clave primaria
        if (empty($finalFields)) {
            // Esto asegura que la consulta no falle y al menos se pueda relacionar con otros modelos.
            $finalFields[] = $model->getKeyName();
        }

        // Aplicar la selección
        $builder->select(array_unique($finalFields));
    }
}
