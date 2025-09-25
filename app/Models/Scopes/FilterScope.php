<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FilterScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */

    /**
     *
     * *array_keys($conditions) !== range(0, count($conditions) - 1)
     *   La expresión range(0, count($conditions) - 1) en PHP genera un array de números enteros consecutivos, comenzando desde 0 hasta el valor de count($conditions) - 1. Aquí, count($conditions) devuelve la cantidad de elementos en el array $conditions. Al restar 1, se obtiene el índice del último elemento (ya que los arrays en PHP son indexados desde 0).

     * Por ejemplo, si $conditions tiene 4 elementos, count($conditions) será 4,
     * y range(0, 3) generará el array [0, 1, 2, 3]. Esto es útil cuando necesitas
     * iterar sobre los índices de un array, en vez de los valores,
     * o cuando necesitas realizar operaciones que dependen de la posición de cada
     * elemento dentro del array.

     * Un posible "gotcha" es que si $conditions está vacío, el resultado será
     * range(0, -1), lo que devuelve un array vacío. Por lo tanto, es importante
     * asegurarse de que $conditions tenga al menos un elemento si esperas obtener
     * un rango válido de índices.

     */
    public function apply(Builder $builder, Model $model): void
    {
        if (empty(request('filters'))) {
            return;
        }

        $filters = request('filters');
        foreach ($filters as $field => $conditions) {
            // Si el filtro es un array asociativo (operadores avanzados)
            if (is_array($conditions) && array_keys($conditions) !== range(0, count($conditions) - 1)) {
                foreach ($conditions as $operator => $value) {
                    if (in_array($operator, ['=', '!=', '>', '<', '>=', '<='])) {
                        $builder->where($field, $operator, $value);
                    } elseif ($operator == 'like') {
                        $builder->where($field, 'LIKE', "%$value%");
                    } elseif ($operator == 'not_like') {
                        $builder->where($field, 'NOT LIKE', "%$value%");
                    } elseif ($operator == 'in') {
                        // Permitir string separado por comas o array
                        $values = is_array($value) ? $value : explode(',', $value);
                        $builder->whereIn($field, $values);
                    } elseif ($operator == 'not_in') {
                        $values = is_array($value) ? $value : explode(',', $value);
                        $builder->whereNotIn($field, $values);
                    }
                }
            }
            // Si el filtro es un array indexado (whereIn)
            elseif (is_array($conditions)) {
                $builder->whereIn($field, $conditions);
            }
            // Si el filtro es un valor simple (igualdad)
            else {
                $builder->where($field, '=', $conditions);
            }
        }
    }
}
