<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutorRequest;
use App\Http\Resources\AutorResource;
use App\Models\Autor;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AutorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['index', 'show']),
        ];
    }

    public function index()
    {
        $autores = Autor::getOrPaginate();
        return  AutorResource::collection($autores);
    }

    public function store(AutorRequest $request)
    {
        try {
            $autor = Autor::create($request->all());
            return (new AutorResource($autor))->additional([
                'message' => 'success',
            ])->setStatusCode(201);
        } catch (\Throwable $th) {
            Log::error("Error AutorController - store", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar crear el Autor.'
            ], 500);
        }
    }

    public function show(Autor $autor)
    {
        $autor = $autor->getShow();
        return new AutorResource($autor);
    }

    public function update(AutorRequest $request, Autor $autor)
    {
        try {
            $autor->update($request->all());
            return (new AutorResource($autor))->additional([
                'message' => 'success',
            ]);
        } catch (\Throwable $th) {
            Log::error("Error AutorController - update", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar actualizar el Autor.'
            ], 500);
        }
    }

    public function destroy(Autor $autor)
    {
        try {
            // Verificar el autor tiene catalogos asociados
            if ($autor->catalogos()->exists()) {
                return response()->json([
                    'message' =>  'No se puede eliminar el autor porque tiene un documento asociado.',
                ], 409);
            }

            // Si no tiene catalogo se procede a eliminar
            $autor->delete();
            return response()->noContent(); // 204 sin cuerpo
        } catch (\Throwable $th) {
            Log::error("Error AutorController - destroy", ['data' => $th]);
            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar el Autor.'
            ], 500);
        }
    }
}
