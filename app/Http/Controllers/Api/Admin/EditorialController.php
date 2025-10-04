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
        $editoriales = Editorial::query()
            // Llama explícitamente al scope compuesto.
            // Esto garantiza que solo se aplique al query principal (Editorial),
            // y no a las subconsultas de 'catalogos'.
            ->applyApiFeatures()
            ->getOrPaginate();

        return response()->json($editoriales);
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
            ])->response()->setStatusCode(201);
        } catch (\Throwable $th) {
            Log::error("Error EditorialController - store", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar crear la editorial.'
            ], 500);
        }
    }

    public function show(Editorial $editoriale)
    {
        $editorial = $editoriale->query()
            ->applyApiFeatures()
            ->getShow(); // Aunque getShow() ya aplica las características de API.

        return response()->json($editorial);
    }

    public function update(EditorialRequest $request, Editorial $editoriale)
    {
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

    public function destroy(Editorial $editoriale)
    {
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
