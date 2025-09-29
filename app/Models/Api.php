<?php

namespace App\Models;

use App\Models\Scopes\FilterScope;
use App\Models\Scopes\IncludeScope;
use App\Models\Scopes\SelectScope;
use App\Models\Scopes\SortScope;
use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    /**
     * Scopes globales configurables por el modelo hijo.
     */
    protected static $globalScopes = [
        FilterScope::class,
        SelectScope::class,
        SortScope::class,
        IncludeScope::class,
    ];

    protected static function booted(): void
    {
        foreach (static::$globalScopes as $scope) {
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