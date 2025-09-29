<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SelectScope implements Scope
{

    public function apply(Builder $builder, Model $model): void
    {
        if (empty(request('select'))) {
            return;
        }

        $select = request('select');
        $selectArray = explode(',', $select);

        // Filtrar solo los campos válidos definidos en el modelo
        $validFields = method_exists($model, 'getFillable') ? $model->getFillable() : [];
        // Siempre permitir 'id' aunque no esté en fillable
        $validFields[] = $model->getKeyName();
        $selectArray = array_intersect($selectArray, $validFields);

        if (!empty($selectArray)) {
            $builder->select($selectArray);
        }
    }
}
