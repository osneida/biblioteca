<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogoRequest;
use App\Http\Resources\CatalogoResource;
use App\Models\Catalogo;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CatalogoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'), //, except: ['index', 'show']
        ];
    }

    /**
     * Listado de Catálogos
     *
     * Muestra el listado de catálogos registrados en el sistema.
     * Se pueden incluir relaciones y aplicar filtros a través de parámetros en la consulta. <br>
     * <b>Filtros disponibles:</b>
     * - <b>select</b>: Permite seleccionar campos específicos. Ejemplo: `select=id,nombre` <br>
     * - <b>include</b>: Permite incluir relaciones del Modelo. Ejemplo: `include=catalogos` <br>
     * - <b>sort</b>: Permite ordenar los resultados. Ejemplo: `sort=-fecha_nacimiento` | `sort=-campo` ordena descendente `sort=campo` ordena ascendente<br>
     * - <b>filter</b>: Permite filtrar los resultados por campos específicos. Ejemplo: `filter[nacionalidad]=E` <br>
     * - <b>page & per_page</b>: Permite paginar los resultados. Ejemplo: `page=2&per_page=10` <br>
     * <b>Ejemplos de uso:</b>
     * - <b>index</b><br>
     * -  api/v1/catalogos?include=editorial,ejemplares,autores<br>
     * -  api/v1/catalogos?include=editorial,ejemplares,autores&sort=-id&select=id,titulo,isbn<br>
     * -  api/v1/catalogos?sort=-id<br>
     * -  api/v1/catalogos?include=autores,editorial&filters[isbn]=Libro isbn<br>
     * -  api/v1/catalogos?include=autores,editorial&filters[id][in][]=5&filters[id][in][]=6<br>
     * -  api/v1/catalogos?include=autores,editorial&filters[id][in]=5,6,7,8<br>
     * -  api/v1/catalogos?include=autores,editorial&filters[id][>=]=8<br>
     * -  api/v1/catalogos?include=ejemplares&filters[titulo][like]=libro<br>
     * -  api/v1/catalogos?filters[titulo][not_like]=libro<br>
     * -  <b>show</b><br>
     * -  api/v1/catalogos/16?include=ejemplares,autores<br>
     * - api/v1/catalogos/16?include=ejemplares,autores&select=id,titulo,tipo_documento<br>
     * -  api/v1/catalogos/11?include=editorial,ejemplares,autores<br>
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        Gate::authorize('catalogo.index');
        $catalogos = Catalogo::query()
            ->applyApiFeatures()
            ->getOrPaginate();

        return CatalogoResource::collection($catalogos);
    }

    /**
     * Crear Catálogo
     *
     * Permite crear un nuevo documento en el sistema.
     *
     * @param  \App\Http\Requests\CatalogoRequest  $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(CatalogoRequest $request)
    {
        Gate::authorize('catalogo.store');

        try {
            DB::beginTransaction();

            //$catalogo = Catalogo::updateOrCreate($request->all());

            // Construir la clave de búsqueda para updateOrCreate. No incluir
            // isbn en la búsqueda si viene null para evitar emparejar por null.
            $searchData = [
                'tipo_documento' => $request['tipo_documento'],
                'editorial_id' => $request['editorial_id'],
                'titulo' => $request['titulo'],
                'subtitulo' => $request['subtitulo'],
                'ano_publicacion' => $request['ano_publicacion'],
                'descripcion_fisica' => $request['descripcion_fisica'],
                'notas' => $request['notas'],
                'user_id' => Auth::id()   //usuario autenticado
            ];

            if ($request->filled('isbn')) {
                $searchData['isbn'] = $request['isbn'];
            }

            $catalogo = Catalogo::updateOrCreate(
                $searchData
            );

            if (!$catalogo) {
                return response()->json([
                    'message' => 'No se pudo crear el documento.'
                ], 400);
            }

            // Lógica para crear el ejemplar
            $catalogo_id = $catalogo->id;
            $año = date('Y');
            $mes = date('m');

            // Buscar el último ejemplar creado este mes
            $ultimoEjemplarMes = \App\Models\Ejemplar::whereYear('created_at', $año)
                ->whereMonth('created_at', $mes)
                ->orderByDesc('id')
                ->first();
            $correlativo = $ultimoEjemplarMes ? ((int)substr($ultimoEjemplarMes->codigo, -4)) + 1 : 1;

            // Buscar el último nro_ejemplar para este catálogo
            $ultimoEjemplarCatalogo = $catalogo->ejemplares()->orderByDesc('nro_ejemplar')->first();
            $nro_ejemplar = $ultimoEjemplarCatalogo ? $ultimoEjemplarCatalogo->nro_ejemplar + 1 : 1;

            // Código: año + mes + correlativo de 4 dígitos
            $codigo = $año . $mes . str_pad($correlativo, 4, '0', STR_PAD_LEFT);

            if ($request['cantidad_de_ejemplares']) {
                $cantidad_de_ejemplares =  $request['cantidad_de_ejemplares'];
                for ($i = 1; $i <= $cantidad_de_ejemplares; $i++) {
                    \App\Models\Ejemplar::create([
                        'fecha_ingreso' => $request['fecha_ingreso'],
                        'catalogo_id' => $catalogo_id,
                        'nro_ejemplar' => $nro_ejemplar,
                        'codigo' => $codigo,
                    ]);
                    $nro_ejemplar++;
                    $correlativo++;
                    $codigo = $año . $mes . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
                }
            } else {
                \App\Models\Ejemplar::create([
                    'fecha_ingreso' => $request['fecha_ingreso'],
                    'catalogo_id' => $catalogo_id,
                    'nro_ejemplar' => $nro_ejemplar,
                    'codigo' => $codigo,
                ]);
            }


            // Sincronizar autores
            $catalogo->autores()->sync($request->input('autores', []));
            DB::commit();

            return (new CatalogoResource($catalogo))->additional([
                'message' => 'success',
            ])->response()->setStatusCode(201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error CatalogoController - store", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar crear el documento.'
            ], 500);
        }
    }

    /**
     * Detalles del Catálogo:
     *
     * Muestra los detalles de un catálogo específico identificado por su ID.
     *
     * Se pueden incluir relaciones y aplicar filtros a través de parámetros en la consulta. <br>
     * <b>Filtros disponibles:</b>
     * - <b>select</b>: Permite seleccionar campos específicos del Modelo. Ejemplo: `select=id,nombre` <br>
     * - <b>include</b>: Permite incluir relaciones del Modelo. Ejemplo: `include=ejemplares` <br>
     * <b>Ejemplos de uso:</b>
     * - api/v1/catalogos/1?select=id,nombre <br>
     * - api/v1/catalogos/1?select=id,nombre&include=ejemplares <br>
     *
     * @param  \App\Models\Catalogo $catalogo
     * @return JsonResponse
     */
    public function show(Catalogo $catalogo)
    {
        Gate::authorize('catalogo.show');

        $query = Catalogo::query();
        // 2. Aplicamos la restricción WHERE al ID que ya fue encontrado por el Route Model Binding.
        $query->where($catalogo->getKeyName(), $catalogo->getKey());
        // Aplicar los scopes para show (select, include)
        $query->showApiFeatures();

        // Obtener directamente el primer resultado o lanzar ModelNotFoundException
        $catalogo = $query->firstOrFail();
        return new CatalogoResource($catalogo);
    }

    /**
     * Actualizar Catálogo
     *
     * Permite actualizar los datos de un catálogo existente en el sistema.
     * @param  \App\Http\Requests\CatalogoRequest  $request
     * @param  \App\Models\Catalogo $catalogo
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(CatalogoRequest $request, Catalogo $catalogo)
    {
        Gate::authorize('catalogo.update');

        try {
            $data = $request->all();
            $catalogo->update($data);
            return (new CatalogoResource($catalogo))->additional([
                'message' => 'success',
            ]);
        } catch (\Throwable $th) {
            Log::error("Error CatalogoController - update", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar actualizar el documento.'
            ], 500);
        }
    }

    /**
     * Eliminar Catálogo
     *
     * Permite eliminar un catálogo, sus autores y ejemplares en el sistema.
     * @param  \App\Models\Catalogo $catalogo
     * @return JsonResponse
     */
    public function destroy(Catalogo $catalogo)
    {
        Gate::authorize('catalogo.destroy');
        try {
            $catalogo->delete();
            return response()->noContent(); // 204 sin cuerpo
        } catch (\Throwable $th) {
            Log::error("Error CatalogoController - destroy", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar el documento.'
            ], 500);
        }
    }
}