<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutorRequest;
use App\Http\Resources\AutorResource;
use App\Models\Autor;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class AutorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api') //, except: ['index', 'show']),
        ];
    }
    /**
     * Listado de Autores
     *
     * Muestra el listado de autores registrados en el sistema.
     * Se pueden incluir relaciones y aplicar filtros a través de parámetros en la consulta. <br>
     * <b>Filtros disponibles:</b>
     * - <b>select</b>: Permite seleccionar campos específicos del autor. Ejemplo: `select=id,nombre` <br>
     * - <b>include</b>: Permite incluir relaciones del autor. Ejemplo: `include=catalogos` <br>
     * - <b>sort</b>: Permite ordenar los resultados. Ejemplo: `sort=-fecha_nacimiento` | `sort=-campo` ordena descendente `sort=campo` ordena ascendente<br>
     * - <b>filter</b>: Permite filtrar los resultados por campos específicos. Ejemplo: `filter[nacionalidad]=E` <br>
     * - <b>page & per_page</b>: Permite paginar los resultados. Ejemplo: `page=2&per_page=10` <br>
     * <b>Ejemplos de uso:</b>
     * - api/v1/autores?select=id,nombre&include=catalogos <br>
     * - api/v1/autores?include=catalogos <br>
     * - api/v1/autores?sort=-nombre  <br>
     * - api/v1/autores?filters[nombre]=Nombre_Completo
     * - api/v1/autores?filters[nombre][like]=parte_del_nombre
     * - api/v1/autores?filters[nacionalidad]=E
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        Gate::authorize('autor.index');
        $autores = Autor::query()
            ->applyApiFeatures()
            ->getOrPaginate();

        return  AutorResource::collection($autores);
    }

    /**
     * Crear Autor
     *
     * Permite crear un nuevo autor en el sistema.
     *
     * @param  \App\Http\Requests\AutorRequest  $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(AutorRequest $request)
    {
        Gate::authorize('autor.store');
        try {
            $autor = Autor::create($request->all());
            return (new AutorResource($autor))->additional([
                'message' => 'success',
            ])->response()->setStatusCode(201);
        } catch (\Throwable $th) {
            Log::error("Error AutorController - store", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar crear el Autor.'
            ], 500);
        }
    }
    //@unauthenticated para cuando no se requiere autenticacion
    /**
     * Detalles del Autor:
     *
     * Muestra los detalles de un autor específico identificado por su ID.
     *
     * Se pueden incluir relaciones y aplicar filtros a través de parámetros en la consulta. <br>
     * <b>Filtros disponibles:</b>
     * - <b>select</b>: Permite seleccionar campos específicos del autor. Ejemplo: `select=id,nombre` <br>
     * - <b>include</b>: Permite incluir relaciones del autor. Ejemplo: `include=catalogos` <br>
     * <b>Ejemplos de uso:</b>
     * - api/v1/autores/1?select=id,nombre <br>
     * - api/v1/autores/1?select=id,nombre&include=catalogos <br>
     *
     * @param  \App\Models\Autor $autore
     * @return JsonResponse
     */
    public function show(Autor $autore)
    {
        Gate::authorize('autor.show');
        $query = Autor::query();
        // 2. Aplicamos la restricción WHERE al ID que ya fue encontrado por el Route Model Binding.
        $query->where($autore->getKeyName(), $autore->getKey());
        // Aplicar los mismos scopes que en index (select, include, filters, sort)
        $query->applyApiFeatures();

        // Obtener directamente el primer resultado o lanzar ModelNotFoundException
        $autor = $query->firstOrFail();

        return new AutorResource($autor);
    }

    /**
     * Actualizar Autor
     *
     * Permite actualizar los datos de un autor existente en el sistema.
     * @param  \App\Http\Requests\AutorRequest  $request
     * @param  \App\Models\Autor $autore
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(AutorRequest $request, Autor $autore)
    {
        Gate::authorize('autor.update');
        try {
            $autore->update($request->all());
            return (new AutorResource($autore))->additional([
                'message' => 'success',
            ]);
        } catch (\Throwable $th) {
            Log::error("Error AutorController - update", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar actualizar el Autor.'
            ], 500);
        }
    }

    /**
     * Eliminar Autor
     *
     * Permite eliminar un autor del sistema.
     * No se puede eliminar un autor si tiene documentos asociados.
     * @param  \App\Models\Autor $autore
     * @return JsonResponse
     */
    public function destroy(Autor $autore)
    {
        Gate::authorize('autor.destroy');
        try {
            // Verificar el autor tiene catalogos asociados
            if ($autore->catalogos()->exists()) {
                return response()->json([
                    'message' =>  'No se puede eliminar el autor porque tiene un documento asociado.',
                ], 409);
            }

            // Si no tiene catalogo se procede a eliminar
            $autore->delete();
            return response()->noContent(); // 204 sin cuerpo
        } catch (\Throwable $th) {
            Log::error("Error AutorController - destroy", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar el Autor.'
            ], 500);
        }
    }
}