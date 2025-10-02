<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SortScope implements Scope
{

    public function apply(Builder $builder, Model $model): void
    {
        if (empty(request('sort'))) {
            // $builder->orderBy('id', 'desc');
            return;
        }

        $sort = request('sort');
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
