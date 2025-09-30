<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogoRequest;
use App\Http\Resources\CatalogoResource;
use App\Models\Catalogo;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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
        $catalogos = Catalogo::getOrPaginate();
        return  CatalogoResource::collection($catalogos);
    }

    public function store(CatalogoRequest $request)
    {
        try {
            $catalogo = Catalogo::create($request->all());
            if (!$catalogo) {
                return response()->json([
                    'message' => 'No se pudo crear el documento.'
                ], 400);
            }
            return (new CatalogoResource($catalogo))->additional([
                'message' => 'success',
            ])->setStatusCode(201);
        } catch (\Throwable $th) {
            Log::error("Error CatalogoController - store", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar crear el documento.'
            ], 500);
        }
    }

    public function show(Catalogo $catalogo)
    {
        $catalogo = $catalogo->getShow();
        return new CatalogoResource($catalogo);
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
            ])->setStatusCode(200);
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
