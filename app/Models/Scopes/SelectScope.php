<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelectScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (empty(request('select'))) {
            return;
        }

        $select = request('select');
        $requestedFields = explode(',', $select);

        // 1. Definir campos válidos base definidos en el modelo
        $validFields = method_exists($model, 'getFillable') ? $model->getFillable() : [];

        // 2. Incluir siempre la clave primaria, 'id' aunque no esté en fillable
        $validFields[] = $model->getKeyName();
        $requestedFields[] = $model->getKeyName();

        // 3. Filtrar campos solicitados contra la lista de campos válidos
        $requestedFields = array_intersect($requestedFields, $validFields);

        // 4.  Si el cliente solicitó includes, asegurarnos de agregar las FKs necesarias
        // para relaciones BelongsTo para que load() o los recursos puedan resolverlas
        // sin necesidad de reconsultar la base.
        $include = request('include');
        if (!empty($include)) {
            $includes = array_filter(array_map('trim', explode(',', $include)));
            foreach ($includes as $relation) {
                // Sólo procesar si el método de relación existe en el modelo
                if (! method_exists($model, $relation)) continue;

                try {
                    $relationObj = $model->{$relation}();
                } catch (\Throwable $e) {
                    continue;
                }

                // Sólo añadir claves foráneas para relaciones BelongsTo (p. ej. editorial)
                if ($relationObj instanceof BelongsTo && method_exists($relationObj, 'getForeignKeyName')) {
                    $fk = $relationObj->getForeignKeyName();
                    if ($fk && ! in_array($fk, $requestedFields, true)) {
                        $requestedFields[] = $fk;
                    }
                }
            }
        }

        // Si después del filtrado no queda nada, volvemos a seleccionar solo la clave primaria
        if (empty($requestedFields)) {
            // Esto asegura que la consulta no falle y al menos se pueda relacionar con otros modelos.
            $requestedFields[] = $model->getKeyName();
        }

        // Aplicar la selección
        if (!empty($requestedFields)) {
            $builder->select(array_unique($requestedFields));
        }
    }
}
