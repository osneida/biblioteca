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
        $builder->select($selectArray);
    }
}
