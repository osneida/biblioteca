<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FilterScope implements Scope
{
    /**
     * Aplica el scope para filtrar si se pasa en la request (parámetro 'filters').
     */
    public function apply(Builder $builder, Model $model): void
    {
        // -------------------------------------------------------------------
        // Autodefensa para evitar la fuga a subconsultas (Eager Loading).
        // Si la tabla actual NO es la tabla del modelo base, ignoramos la aplicación.
        $modelTableName = $model->getTable();
        $currentQueryTable = $builder->getQuery()->from;

        if ($currentQueryTable !== $modelTableName) {
            return;
        }
        // -------------------------------------------------------------------

        if (empty(request('filters'))) {
            return;
        }

        $filters = request('filters');

        foreach ($filters as $field => $conditions) {

            // Seguridad extra: Si el campo no existe en el modelo, lo saltamos.
            // Aunque esto es difícil de verificar 100% en Eloquent sin Reflectión,
            // podemos asegurar que el campo es al menos una columna válida.
            // Por simplicidad, asumiremos que si es un filtro, es una columna.

            // Si el filtro es un array asociativo (operadores avanzados)
            if (is_array($conditions) && array_keys($conditions) !== range(0, count($conditions) - 1)) {

                foreach ($conditions as $operator => $value) {

                    // Convertir el operador a minúsculas para seguridad
                    $operator = strtolower($operator);

                    if (in_array($operator, ['=', '!=', '>', '<', '>=', '<='])) {
                        $builder->where($field, $operator, $value);
                    } elseif ($operator === 'like' || $operator === 'not_like') {
                        $dbOperator = ($operator === 'like') ? 'LIKE' : 'NOT LIKE';
                        $builder->where($field, $dbOperator, "%$value%");
                    } elseif ($operator === 'in' || $operator === 'not_in') {
                        $values = is_array($value) ? $value : explode(',', $value);

                        if ($operator === 'in') {
                            $builder->whereIn($field, $values);
                        } else {
                            $builder->whereNotIn($field, $values);
                        }
                    }
                }
            }
            // Si el filtro es un array indexado (whereIn simple, ej: ?filters[id][]=1&filters[id][]=2)
            elseif (is_array($conditions)) {
                $builder->whereIn($field, $conditions);
            }
            // Si el filtro es un valor simple (igualdad, ej: ?filters[estado]=activo)
            else {
                $builder->where($field, '=', $conditions);
            }
        }
    }
}