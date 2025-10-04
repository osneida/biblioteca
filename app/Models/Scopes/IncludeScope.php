<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IncludeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // -------------------------------------------------------------------
        // Autodefensa: Asegurar que solo se aplica al Builder de la tabla principal
        // Esto evita que se aplique si, por error, se usara en una subconsulta.
        $modelTableName = $model->getTable();
        $currentQueryTable = $builder->getQuery()->from;

        if ($currentQueryTable !== $modelTableName) {
            return;
        }
        // -------------------------------------------------------------------

        $include = request('include');

        if (empty($include)) {
            return;
        }

        $includeArray = explode(',', $include);

        foreach ($includeArray as $includeItem) {

            // Seguridad: Solo aplicar 'with' si la relación existe en el modelo.
            // Esto previene errores si el usuario solicita una relación mal escrita.
            if (method_exists($model, $includeItem)) {
                $builder->with($includeItem);
            }
        }
    }
}
