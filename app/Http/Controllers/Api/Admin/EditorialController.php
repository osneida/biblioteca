<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EditorialRequest;
use App\Http\Resources\EditorialResource;
use App\Models\Editorial;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class EditorialController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api') //, except: ['index', 'show']),
        ];
    }

    /**
     * Listado de Editoriales
     *
     * Muestra el listado de editoriales registrados en el sistema.
     * Se pueden incluir relaciones y aplicar filtros a través de parámetros en la consulta. <br>
     * <b>Filtros disponibles:</b>
     * - <b>select</b>: Permite seleccionar campos específicos. Ejemplo: `select=id,nombre` <br>
     * - <b>include</b>: Permite incluir relaciones del Modelo. Ejemplo: `include=catalogos` <br>
     * - <b>sort</b>: Permite ordenar los resultados. Ejemplo: `sort=-nombre` | `sort=-campo` ordena descendente `sort=campo` ordena ascendente<br>
     * - <b>filter</b>: Permite filtrar los resultados por campos específicos. Ejemplo: `filter[nombre]=Editorial` <br>
     * - <b>page & per_page</b>: Permite paginar los resultados. Ejemplo: `page=2&per_page=10` <br>
     * <b>Ejemplos de uso:</b>
     * - <b>index</b><br>
     * -  api/v1/editoriales?include=catalogos<br>
     * -  api/v1/editoriales?include=catalogos&sort=-id&select=id,nombre<br>
     * -  api/v1/editoriales?sort=-id<br>
     * -  api/v1/editoriales?include=catalogos&filters[nombre]=Editorial<br>
     * -  api/v1/editoriales?include=catalogos&filters[id][in][]=5&filters[id][in][]=6<br>
     * -  api/v1/editoriales?include=catalogos&filters[id][in]=5,6,7,8<br>
     * -  api/v1/editoriales?include=catalogos&filters[id][>=]=8<br>
     * -  api/v1/editoriales?include=ecatalogos&filters[nombre][like]=libro<br>
     * -  api/v1/editoriales?filters[nombre][not_like]=nombre_editorial<br>
     * -  <b>show</b><br>
     * -  api/v1/editoriales/16?include=catalogos<br>
     * -  api/v1/editoriales/16?include=catalogos&select=id,nombre<br>
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        Gate::authorize('editorial.index');

        $editoriales = Editorial::query()
            ->applyApiFeatures()
            ->getOrPaginate();

        return  EditorialResource::collection($editoriales);
    }

    /**
     * Crear Editorial
     *
     * Permite crear una nueva Editorial en el sistema.
     *
     * @param  \App\Http\Requests\EditorialRequest  $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(EditorialRequest $request)
    {
        Gate::authorize('editorial.store');
        try {
            $editorial = Editorial::create($request->all());
            if (!$editorial) {
                return response()->json([
                    'message' => 'No se pudo crear la editorial.'
                ], 400);
            }
            return (new EditorialResource($editorial))->additional([
                'message' => 'success',
            ])->response()->setStatusCode(201);
        } catch (\Throwable $th) {
            Log::error("Error EditorialController - store", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar crear la editorial.'
            ], 500);
        }
    }

    /**
     * Detalles de la Editorial:
     *
     * Muestra los detalles de una Editorial específico identificado por su ID.
     *
     * Se pueden incluir relaciones y aplicar filtros a través de parámetros en la consulta. <br>
     * <b>Filtros disponibles:</b>
     * - <b>select</b>: Permite seleccionar campos específicos del Modelo. Ejemplo: `select=id,nombre` <br>
     * - <b>include</b>: Permite incluir relaciones del Modelo. Ejemplo: `include=catalogos` <br>
     * <b>Ejemplos de uso:</b>
     * - api/v1/editorial/1?select=id,nombre <br>
     * - api/v1/editorial/1?select=id,nombre&include=catalogos <br>
     *
     * @param  \App\Models\Editorial $editorial
     * @return JsonResponse
     */
    public function show(Editorial $editoriale)
    {
        Gate::authorize('editorial.show');

        $query = Editorial::query();
        // 2. Aplicamos la restricción WHERE al ID que ya fue encontrado por el Route Model Binding.
        $query->where($editoriale->getKeyName(), $editoriale->getKey());
        // Aplicar los scopes para show (select, include)
        $query->showApiFeatures();

        // Obtener directamente el primer resultado o lanzar ModelNotFoundException
        $editorial = $query->firstOrFail();

        return new EditorialResource($editorial);
    }

    /**
     * Actualizar Editorial
     *
     * Permite actualizar los datos de una Editorial existente en el sistema.
     * @param  \App\Http\Requests\EditorialRequest  $request
     * @param  \App\Models\Editorial $editoriale
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(EditorialRequest $request, Editorial $editoriale)
    {
        Gate::authorize('editorial.update');

        try {
            $data = $request->all();
            $editoriale->update($data);
            return (new EditorialResource($editoriale))->additional([
                'message' => 'success',
            ]);
        } catch (\Throwable $th) {
            Log::error("Error EditorialController - update", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar actualizar la editorial.'
            ], 500);
        }
    }

    /**
     * Eliminar Editorial
     *
     * Permite eliminar una Editorial, no se puede eliminar si está relacionado con un catalogo.
     * @param  \App\Models\Editorial $editoriale
     * @return JsonResponse
     */
    public function destroy(Editorial $editoriale)
    {
        Gate::authorize('editorial.destroy');
        try {
            // Verificar si la editorial tiene catálogos asociados
            if ($editoriale->catalogos()->exists()) {
                return response()->json([
                    'message' =>  'No se puede eliminar la Editorial porque tiene un documento asociado.',
                ], 409); // 409 Conflict
            }

            // Si no tiene catálogo se procede a eliminar
            $editoriale->delete();
            return response()->noContent(); // 204 sin cuerpo
        } catch (\Throwable $th) {
            Log::error("Error EditorialController - destroy", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar la editorial.'
            ], 500);
        }
    }
}
