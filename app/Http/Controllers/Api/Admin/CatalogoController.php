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
        //
    }

    public function update(Request $request, Catalogo $catalogo)
    {
        //
    }

    public function destroy(Catalogo $catalogo)
    {
        //
    }
}
