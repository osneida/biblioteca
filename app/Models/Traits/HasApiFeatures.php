<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

// Importar todos los scopes que definen la lógica de la Request
use App\Models\Scopes\FilterScope;
use App\Models\Scopes\IncludeScope;
use App\Models\Scopes\SelectScope;
use App\Models\Scopes\SortScope;

trait HasApiFeatures
{
    /**
     * Lista de clases de scopes de la API. Ya no son Global Scopes.
     */
    protected static array $apiScopes = [
        IncludeScope::class,
        FilterScope::class,
        SelectScope::class,
        SortScope::class,
    ];

    protected static array $showScopes = [
        IncludeScope::class,
        SelectScope::class,
    ];

    /**
     * Scope LOCAL compuesto que aplica todos los scopes de API.
     * Solo se aplica cuando se llama explícitamente en el Controller.
     */
    public function scopeApplyApiFeatures(Builder $query): Builder
    {
        // Itera sobre la lista de scopes de la API
        foreach (static::$apiScopes as $scopeClass) {
            $scopeInstance = new $scopeClass;

            // Aplica el scope SÓLO a la consulta $query actual.
            // Esto previene que se filtren a las consultas de Eager Loading.
            $scopeInstance->apply($query, $query->getModel());
        }

        return $query;
    }

    /**
     * Obtiene todos los registros o los pagina según el parámetro.
     */
    public function scopeGetOrPaginate(Builder $query, ?int $perPage = null): Collection|LengthAwarePaginator
    {
        $perPage = $perPage ?? (int) request('perPage');

        return ($perPage > 0)
            ? $query->paginate($perPage)
            : $query->get();
    }

    /**
     * Obtiene un registro único aplicando los scopes de API.
     */
    public function scopeShowApiFeatures(Builder $query): Builder
    {
        // Itera sobre la lista de scopes de la API
        foreach (static::$showScopes as $scopeClass) {
            $scopeInstance = new $scopeClass;

            // Aplica el scope SÓLO a la consulta $query actual.
            // Esto previene que se filtren a las consultas de Eager Loading.
            $scopeInstance->apply($query, $query->getModel());
        }

        return $query;
    }

    public function scopeGetShow(Builder $query): Builder
    {
        // Itera sobre la lista de scopes de la API
        foreach (static::$showScopes as $scopeClass) {
            $scopeInstance = new $scopeClass;

            // Aplica el scope SÓLO a la consulta $query actual.
            // Esto previene que se filtren a las consultas de Eager Loading.
            $scopeInstance->apply($query, $query->getModel());
        }

        return $query;
    }
}
