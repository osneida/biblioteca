<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CatalogoResource;
use App\Models\Catalogo;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{

    public function index()
    {
        $catalogos = Catalogo::getOrPaginate();
        return  CatalogoResource::collection($catalogos);
    }


    public function store(Request $request)
    {
        //
    }


    public function show(Catalogo $catalogo)
    {
        $catalogo = $catalogo->getShow();
        return new CatalogoResource($catalogo);
    }

    public function update(Request $request, Catalogo $catalogo)
    {
        return (new CatalogoResource($catalogo))->additional([
            'message' => 'success',
        ]);
    }

    public function destroy(Catalogo $catalogo)
    {
        //
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
