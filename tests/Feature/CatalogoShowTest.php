<?php

namespace Tests\Feature;

use App\Models\Catalogo;
use App\Models\Editorial;
use App\Models\Autor;
use App\Models\Ejemplar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function show_includes_relations_when_select_excludes_fk()
    {
        // Crear editorial
        $editorial = Editorial::create([
            'nombre' => 'Nueva Editorial',
            'direccion' => 'Esta es la direccion de la Nueva Editorial',
        ]);

        // Crear autores
        $autor = Autor::create([
            'nombre' => 'Autor Ejemplo',
        ]);

        // Crear catalogo ligado a editorial
        $catalogo = Catalogo::create([
            'fecha_ingreso' => now(),
            'tipo_documento' => 1,
            'isbn' => '1234567890',
            'titulo' => 'Titulo Test',
            'subtitulo' => null,
            'ano_publicacion' => '2020', //now()->year(), // now()->toDateString(),
            'descripcion_fisica' => null,
            'notas' => null,
            'editorial_id' => $editorial->id,
        ]);

        // Vincular autor
        $catalogo->autores()->attach($autor->id);

        // Crear ejemplar
        Ejemplar::create([
            'catalogo_id' => $catalogo->id,
            'nro_ejemplar' => 1,
            'codigo' => '202500010001',
        ]);

        // Llamada al endpoint sin editorial_id en select
        $response = $this->getJson('/api/v1/catalogos/' . $catalogo->id . '?include=editorial,ejemplares,autores&select=id,titulo,tipo_documento');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'titulo',
                'tipo_documento',
                'editorial' => [
                    'id',
                    'nombre',
                    'direccion',
                ],
                'autores',
                'ejemplares',
            ]
        ]);
    }

    /** @test */
    public function show_returns_404_for_missing_catalogo()
    {
        $response = $this->getJson('/api/v1/catalogos/999999');
        $response->assertStatus(404);
    }
}