<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogoRequest;
use App\Http\Resources\CatalogoResource;
use App\Models\Catalogo;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CatalogoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['index', 'show']),
        ];
    }

    public function index()
    {
        $catalogos = Catalogo::query()
            ->applyApiFeatures()
            ->getOrPaginate();

        return CatalogoResource::collection($catalogos);
    }

    public function store(CatalogoRequest $request)
    {
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
                'fecha_publicacion' => $request['fecha_publicacion'],
                'descripcion_fisica' => $request['descripcion_fisica'],
                'notas' => $request['notas']
            ];

            if ($request->filled('isbn')) {
                $searchData['isbn'] = $request['isbn'];
            }

            $catalogo = Catalogo::updateOrCreate(
                $searchData,
                [
                    'fecha_ingreso' => $request['fecha_ingreso'],
                ]
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

            \App\Models\Ejemplar::create([
                'catalogo_id' => $catalogo_id,
                'nro_ejemplar' => $nro_ejemplar,
                'codigo' => $codigo,
            ]);

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

    public function show(Catalogo $catalogo)
    {
        try {
            $query = Catalogo::query();

            // 2. Aplicamos la restricción WHERE al ID que ya fue encontrado por el Route Model Binding.
            $query->where($catalogo->getKeyName(), $catalogo->getKey());
            // Aplicar los mismos scopes que en index (select, include, filters, sort)
            $query->applyApiFeatures();

            // Obtener directamente el primer resultado o lanzar ModelNotFoundException
            $catalogo = $query->firstOrFail();

            return new CatalogoResource($catalogo);
        } catch (\Throwable $th) {
            // Si es una excepción de tipo ModelNotFound, devolver 404 con mensaje personalizado.
            // Para cualquier otra excepción, también devolvemos 404 aquí para no filtrar detalles internos.
            return response()->json([
                'message' => 'No se encontró el documento solicitado.'
            ], 404);
        }
    }

    public function update(CatalogoRequest $request, Catalogo $catalogo)
    {
        try {
            $data = $request->all();
            if (!$catalogo->isDirty($data)) {
                return response()->json([
                    'message' => 'No hubo cambios para actualizar.'
                ], 200);
            }
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

    public function destroy(Catalogo $catalogo)
    {
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
