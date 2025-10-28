<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Trait\Test\AuthenticatesAsCataloger;
use Carbon\Carbon;
use Tests\TestCase;

class CatalogoTest extends TestCase
{
    /**
     * 1.- Test para el CRUD de Catalogos
     * 2.- Test para los permisos de Catalogos
     * 3.- Test para las validaciones de Catalogos
     * 4.- Test para los filtros
     */

    use RefreshDatabase, WithFaker, AuthenticatesAsCataloger;

    protected function setUp(): void
    {
        parent::setUp();
        // Ejecutar el método del trait antes de cada test
        $this->authenticateAsCataloger();
    }

    //1.- Test para el CRUD
    public function test_create_catalogo()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();
        $autor2 = \App\Models\Autor::factory()->create();

        $ano_publicacion = $this->faker->year();
        $titulo = "OsneidaBordones"; //$this->faker->name();
        $subtitulo = $this->faker->text();
        $fecha_actual_bd = Carbon::now()->format('Y-m-d');
        $cantidad_ejemplares_creados = 2;
        $año = date('Y');
        $mes = date('m');
        $correlativo = 1;

        $data = [
            "fecha_ingreso" => $fecha_actual_bd,
            "ano_publicacion" => $ano_publicacion,
            "tipo_documento" => 1,
            "isbn" => "1234567890123", //$this->faker->unique()->name(12),
            "titulo" => $titulo,
            "subtitulo" =>  $subtitulo,
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "cantidad_de_ejemplares" => $cantidad_ejemplares_creados,
            "autores" => [$autor1->id, $autor2->id],
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tipo_documento',
                    'tipo_documento_label',
                    'editorial_id',
                    'titulo',
                    'subtitulo',
                    'ano_publicacion',
                    'descripcion_fisica',
                    'notas',
                    'user_id',
                    'ingresado_por:',
                    'isbn'
                ],
                'message',
            ]);

        $response->assertStatus(201);

        $dataCatalogo = [
            "ano_publicacion" => $ano_publicacion,
            "tipo_documento" => 1,
            "isbn" => '1234567890123',
            "titulo" => $titulo,
            "subtitulo" =>  $subtitulo,
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "user_id" => $this->user->id ?? 1
        ];

        $this->assertDatabaseHas('catalogos', $dataCatalogo);

        // Aserción en la tabla 'ejemplares'
        // Obtener el ID del catálogo recién creado para la aserción de ejemplares
        $catalogoId = $response->json('data.id');

        // Verificar que se crearon la cantidad correcta de ejemplares
        $this->assertDatabaseCount('ejemplars', $cantidad_ejemplares_creados);

        $codigo = $año . $mes . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
        // Verificar que los ejemplares tienen la fecha de ingreso y el catalog_id correctos
        $dataEjemplar = [
            'catalogo_id' => $catalogoId,
            'fecha_ingreso' => $fecha_actual_bd,
            'nro_ejemplar' => 1,
            'codigo' => $codigo,
            'estatus' => 'D',
        ];

        $this->assertDatabaseHas('ejemplars', $dataEjemplar);

        $correlativo++;
        $codigo = $año . $mes . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
        $dataEjemplar = [
            'catalogo_id' => $catalogoId,
            'fecha_ingreso' => $fecha_actual_bd,
            'nro_ejemplar' => 2,
            'codigo' => $codigo,
            'estatus' => 'D',
        ];
        $this->assertDatabaseHas('ejemplars', $dataEjemplar);

        //verificar que se crearon los autores en la tabla autor_catalogo
        $dataAutor = [
            'catalogo_id' => $catalogoId,
            'autor_id' => $autor1->id,
        ];
        $this->assertDatabaseHas('autor_catalogo', $dataAutor);

        $dataAutor = [
            'catalogo_id' => $catalogoId,
            'autor_id' => $autor2->id,
        ];
        $this->assertDatabaseHas('autor_catalogo', $dataAutor);
    }

    public function test_returns_a_list_of_catalogos()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/catalogos');

        $response->assertStatus(200);
    }

    public function test_updates_an_catalogo()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $catalogo  = \App\Models\Catalogo::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();
        $autor2 = \App\Models\Autor::factory()->create();

        $catalogoID =  $catalogo->id;
        $ano_publicacion = $this->faker->year();

        //datos para modificar
        $dataUpdate = [
            "fecha_ingreso" => Carbon::now()->format('Y-m-d'),
            "ano_publicacion" => $ano_publicacion,
            "tipo_documento" => 2,
            "titulo" => 'Modificado',
            "subtitulo" =>  'subtitulo Modificado',
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "autores" => [$autor1->id, $autor2->id],
        ];

        $dataUpdateFinal = [
            "ano_publicacion" => $ano_publicacion,
            "tipo_documento" => 2,
            "isbn" => null,
            "titulo" => 'Modificado',
            "subtitulo" =>  'subtitulo Modificado',
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->putJson("/api/v1/catalogos/{$catalogoID}", $dataUpdate);

        $response->assertStatus(200)
            ->assertJsonFragment($dataUpdateFinal);

        $this->assertDatabaseHas('catalogos', array_merge(['id' => $catalogoID], $dataUpdateFinal));
    }

    public function test_deletes_an_catalogo()
    {
        $catalogo = \App\Models\Catalogo::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/catalogos/{$catalogo->id}");

        $response->assertStatus(204);
    }

    //<=== 2.- Test para los permisos de Catalogos ==>
    //test para verificar que un usuario sin el rol de catalogador
    //no puede crear un catalogo
    public function test_non_cataloger_cannot_create_catalogo()
    {
        // Crear un usuario sin el rol de catalogador
        $user = \App\Models\User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();
        $autor2 = \App\Models\Autor::factory()->create();

        $ano_publicacion = $this->faker->year();
        $titulo = $this->faker->name();
        $subtitulo = $this->faker->text();
        $fecha_actual_bd = Carbon::now()->format('Y-m-d');

        $data = [
            "fecha_ingreso" => $fecha_actual_bd,
            "ano_publicacion" => $ano_publicacion,
            "tipo_documento" => 1,
            "isbn" => '1234567890123', //$this->faker->unique()->name(12),
            "titulo" => $titulo,
            "subtitulo" =>  $subtitulo,
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "autores" => [$autor1->id, $autor2->id],
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(403); // Forbidden
    }

    //test para verificar que un usuario sin el rol de catalogador
    //no puede eliminar un catalogo
    public function test_non_cataloger_cannot_delete_catalogo()
    {
        // Crear un usuario sin el rol de catalogador
        $user = \App\Models\User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $catalogo = \App\Models\Catalogo::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/catalogos/{$catalogo->id}");

        $response->assertStatus(403); // Forbidden

    }

    //test para verificar que un usuario sin el rol de catalogador
    //no puede actualizar un catalogo
    public function test_non_cataloger_cannot_update_catalogo()
    {
        // Crear un usuario sin el rol de catalogador
        $user = \App\Models\User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $catalogo = \App\Models\Catalogo::factory()->create();
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "fecha_ingreso" => "2025-10-10",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "titulo" => 'titulo',
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "autores" => [$autor1->id],
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->putJson("/api/v1/catalogos/{$catalogo->id}", $data);

        $response->assertStatus(403); // Forbidden
    }

    //test para verificar que un usuario con permiso catalogo.store
    //puede crear un catalogo, sin importar el rol
    public function test_user_with_permission_can_create_catalogo()
    {
        $bibliotecario_rol =  \Spatie\Permission\Models\Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'catalogo.store',
            'guard_name' => 'api',
        ]);

        $bibliotecario_rol->syncPermissions([
            'catalogo.store',
        ]);

        $user = \App\Models\User::factory()->create();
        $user->assignRole($bibliotecario_rol);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();
        $autor2 = \App\Models\Autor::factory()->create();

        $ano_publicacion = $this->faker->year();
        $titulo = $this->faker->name();
        $subtitulo = $this->faker->text();
        $fecha_actual_bd = Carbon::now()->format('Y-m-d');
        $cantidad_ejemplares_creados = 2;
        $año = date('Y');
        $mes = date('m');
        $correlativo = 1;

        $data = [
            "fecha_ingreso" => $fecha_actual_bd,
            "ano_publicacion" => $ano_publicacion,
            "tipo_documento" => 1,
            "isbn" => '1234567890123', //$this->faker->unique()->name(12),
            "titulo" => $titulo,
            "subtitulo" =>  $subtitulo,
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "autores" => [$autor1->id, $autor2->id],
            "cantidad_de_ejemplares" => $cantidad_ejemplares_creados
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tipo_documento',
                    'tipo_documento_label',
                    'editorial_id',
                    'titulo',
                    'subtitulo',
                    'ano_publicacion',
                    'descripcion_fisica',
                    'notas',
                    'user_id',
                    'ingresado_por:',
                    'isbn'
                ],
                'message',
            ]);

        $response->assertStatus(201);

        $dataCatalogo = [
            "ano_publicacion" => $ano_publicacion,
            "tipo_documento" => 1,
            "titulo" => $titulo,
            "subtitulo" =>  $subtitulo,
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "user_id" => $user->id ?? 1
        ];

        $this->assertDatabaseHas('catalogos', $dataCatalogo);

        $catalogoId = $response->json('data.id');

        // Verificar que se crearon la cantidad correcta de ejemplares
        $this->assertDatabaseCount('ejemplars', $cantidad_ejemplares_creados);

        $codigo = $año . $mes . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
        // Verificar que los ejemplares tienen la fecha de ingreso y el catalog_id correctos
        $dataEjemplar = [
            'catalogo_id' => $catalogoId,
            'fecha_ingreso' => $fecha_actual_bd,
            'nro_ejemplar' => 1,
            'codigo' => $codigo,
            'estatus' => 'D',
        ];

        $this->assertDatabaseHas('ejemplars', $dataEjemplar);

        $correlativo++;
        $codigo = $año . $mes . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
        $dataEjemplar = [
            'catalogo_id' => $catalogoId,
            'fecha_ingreso' => $fecha_actual_bd,
            'nro_ejemplar' => 2,
            'codigo' => $codigo,
            'estatus' => 'D',
        ];
        $this->assertDatabaseHas('ejemplars', $dataEjemplar);

        //verificar que se crearon los autores en la tabla autor_catalogo
        $dataAutor = [
            'catalogo_id' => $catalogoId,
            'autor_id' => $autor1->id,
        ];
        $this->assertDatabaseHas('autor_catalogo', $dataAutor);

        $dataAutor = [
            'catalogo_id' => $catalogoId,
            'autor_id' => $autor2->id,
        ];
        $this->assertDatabaseHas('autor_catalogo', $dataAutor);
    }

    //test para verificar que un usuario con permiso catalogo.destroy
    //puede eliminar un catalogo, sin importar el rol
    public function test_user_with_permission_can_delete_catalogo()
    {
        $bibliotecario_rol =  \Spatie\Permission\Models\Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'catalogo.destroy',
            'guard_name' => 'api',
        ]);

        $bibliotecario_rol->syncPermissions([
            'catalogo.destroy',
        ]);

        $user = \App\Models\User::factory()->create();
        $user->assignRole($bibliotecario_rol);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $catalogo = \App\Models\Catalogo::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/catalogos/{$catalogo->id}");

        $response->assertStatus(204);
    }

    //test para verificar que un usuario con permiso catalogo.update
    //puede actualizar un catalogo, sin importar el rol
    public function test_user_with_permission_can_update_catalogo()
    {
        $bibliotecario_rol =  \Spatie\Permission\Models\Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'catalogo.update',
            'guard_name' => 'api',
        ]);

        $bibliotecario_rol->syncPermissions([
            'catalogo.update',
        ]);

        $user = \App\Models\User::factory()->create();
        $user->assignRole($bibliotecario_rol);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $editorial = \App\Models\Editorial::factory()->create();
        $catalogo  = \App\Models\Catalogo::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();
        $autor2 = \App\Models\Autor::factory()->create();

        $catalogoID =  $catalogo->id;
        $ano_publicacion = $this->faker->year();

        //datos para modificar
        $dataUpdate = [
            "fecha_ingreso" => Carbon::now()->format('Y-m-d'),
            "ano_publicacion" => $ano_publicacion,
            "tipo_documento" => 2,
            "titulo" => 'Modificado',
            "subtitulo" =>  'subtitulo Modificado',
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "autores" => [$autor1->id, $autor2->id],
        ];

        $dataUpdateFinal = [
            "ano_publicacion" => $ano_publicacion,
            "tipo_documento" => 2,
            "isbn" => null,
            "titulo" => 'Modificado',
            "subtitulo" =>  'subtitulo Modificado',
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->putJson("/api/v1/catalogos/{$catalogoID}", $dataUpdate);

        $response->assertStatus(200)
            ->assertJsonFragment($dataUpdateFinal);

        $this->assertDatabaseHas('catalogos', array_merge(['id' => $catalogoID], $dataUpdateFinal));
    }

    //  3.- Test para las validaciones de Catalogos
    //  4.- Test para los filtros

}
