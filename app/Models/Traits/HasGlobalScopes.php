<?php

namespace App\Models\Traits;

use App\Models\Scopes\FilterScope;
use App\Models\Scopes\IncludeScope;
use App\Models\Scopes\SelectScope;
use App\Models\Scopes\SortScope;

trait HasGlobalScopes
{
    /**
     * Scopes globales configurables por el modelo.
     */
    protected static $customGlobalScopes = [
        FilterScope::class,
        SelectScope::class,
        SortScope::class,
        IncludeScope::class,
    ];

    protected static function booted(): void
    {
        foreach (static::$customGlobalScopes as $scope) {
            static::addGlobalScope(new $scope);
        }
    }

    /**
     * Obtiene todos los registros o los pagina según el parámetro.
     */
    public function scopeGetOrPaginate($query, $perPage = null)
    {
        $perPage = $perPage ?? request('perPage');
        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Obtiene un registro según los scope SelectScope y/o IncludeScope.
     */
    public function scopeGetShow($query)
    {
        // Filtra por el id del modelo actual
        $query = $query->where($this->getKeyName(), $this->getKey());

        // Aplica SelectScope (selección de campos)
        if (method_exists($this, 'applySelectScope')) {
            $query = $this->applySelectScope($query);
        }

        // Aplica IncludeScope (inclusión de relaciones)
        if (method_exists($this, 'applyIncludeScope')) {
            $query = $this->applyIncludeScope($query);
        }

        return $query->firstOrFail();
    }
}
