<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EditorialRequest;
use App\Http\Resources\EditorialResource;
use App\Models\Editorial;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EditorialController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['index', 'show']),
        ];
    }

    public function index()
    {
        $editoriales = Editorial::getOrPaginate();
        return  EditorialResource::collection($editoriales);
    }

    public function store(EditorialRequest $request)
    {
        try {
            $editorial = Editorial::create($request->all());
            if (!$editorial) {
                return response()->json([
                    'message' => 'No se pudo crear la editorial.'
                ], 400);
            }
            return (new EditorialResource($editorial))->additional([
                'message' => 'success',
            ])->setStatusCode(201);
        } catch (\Throwable $th) {
            Log::error("Error EditorialController - store", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar crear la editorial.'
            ], 500);
        }
    }

    public function show(Editorial $editorial)
    {
        $editorial = $editorial->getShow();
        return new EditorialResource($editorial);
    }

    public function update(EditorialRequest $request, Editorial $editorial)
    {
        try {
            $data = $request->all();
            if (!$editorial->isDirty($data)) {
                return response()->json([
                    'message' => 'No hubo cambios para actualizar.'
                ], 200);
            }
            $editorial->update($data);
            return (new EditorialResource($editorial))->additional([
                'message' => 'success',
            ])->setStatusCode(200);
        } catch (\Throwable $th) {
            Log::error("Error EditorialController - update", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar actualizar la editorial.'
            ], 500);
        }
    }

    public function destroy(Editorial $editorial)
    {
        try {
            // Verificar si la editorial tiene catálogos asociados
            if ($editorial->catalogos()->exists()) {
                return response()->json([
                    'message' =>  'No se puede eliminar la Editorial porque tiene un documento asociado.',
                ], 409); // 409 Conflict
            }

            // Si no tiene catálogo se procede a eliminar
            $editorial->delete();
            return response()->noContent(); // 204 sin cuerpo
        } catch (\Throwable $th) {
            Log::error("Error EditorialController - destroy", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar la editorial.'
            ], 500);
        }
    }
}
