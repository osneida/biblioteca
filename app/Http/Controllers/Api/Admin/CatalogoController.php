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
use PhpParser\Node\Stmt\TryCatch;

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
        //$catalogos = Catalogo::getOrPaginate();
        $catalogos = Catalogo::query()
            ->applyApiFeatures()
            ->getOrPaginate();

        return response()->json($catalogos);
        //return $catalogos; //CatalogoResource::collection($catalogos);
    }

    public function store(CatalogoRequest $request)
    {
        try {
            DB::beginTransaction();

            $catalogo = Catalogo::create($request->all());
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
            $query->applyApiFeatures();
            $catalogo = $query->get()->firstOrFail(); //para lanzar los autores porque es una relacion muchos a muchos
            //return new CatalogoResource($catalogo);


            //TODO modificar CatalogoResource para que muestre autoes cuando se solicite

            //quiero validar que cuando el codigo de Status sea 404 me muestre el mensaje personalizado
            if ($catalogo) {
                return response()->json($catalogo);
            } else {
                return response()->json([
                    'message' => 'No se encontró el documento solicitado.'
                ], 404);
            }
        } catch (\Throwable $th) {
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

/**
 * Para una API RESTful en Laravel (y en general):

 *index y show: 200 OK
 *store: 201 Created
 *update: 200 OK (si devuelves el recurso actualizado) o 204 No Content (si no devuelves contenido)
 *delete: 204 No Content (si no devuelves contenido) o 200 OK (si devuelves algún mensaje o el recurso eliminado)
 *Lo más común es:
 *
 *update → 200 OK
 *delete → 204 No Content
 *Usa 204 si la respuesta no tiene body, solo cabeceras.
 */
