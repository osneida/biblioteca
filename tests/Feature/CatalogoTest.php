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
     * 3.- Test para las validaciones de los campos del Catalogo
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
    //'titulo' => 'required|string|max:255|min:3',
    // test para verificar la validación del campo titulo
    public function test_catalogo_title_is_required()
    {
        $data = [
            // "titulo" => 'Some Title', // Título omitido para probar la validación
            "ano_publicacion" => '2023',
            "tipo_documento" => 1,
            "editorial_id" => 1,
            "autores" => [1],
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['titulo']);
    }

    //test para verificar que el campo titulo no exceda el maximo de caracteres
    public function test_catalogo_title_max_length()
    {
        $longTitle = str_repeat('a', 256); // 256 caracteres
        $data = [
            "titulo" => $longTitle,
            "ano_publicacion" => '2023',
            "tipo_documento" => 1,
            "editorial_id" => 1,
            "autores" => [1],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['titulo']);
    }

    //test para verificar que el campo titulo tenga al menos 3 caracteres
    public function test_catalogo_title_min_length()
    {
        $shortTitle = 'ab'; // 2 caracteres
        $data = [
            "titulo" => $shortTitle,
            "ano_publicacion" => '2023',
            "tipo_documento" => 1,
            "editorial_id" => 1,
            "autores" => [1],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['titulo']);
    }

    //'subtitulo' => 'nullable|string|max:255|min:3',

    //test para verificar que el campo subtitulo es opcional
    public function test_catalogo_subtitle_is_optional()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();
        $data = [
            "fecha_ingreso" => "2024-06-15",
            "titulo" => 'Some Title',
            "subtitulo" => '', // Subtítulo omitido para probar que es opcional
            "ano_publicacion" => '2023',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(201);
    }

    //test para verificar que el campo subtitulo no exceda el maximo de caracteres
    public function test_catalogo_subtitle_max_length()
    {
        $longSubtitle = str_repeat('a', 256); // 256 caracteres
        $data = [
            "titulo" => 'Some Title',
            "subtitulo" => $longSubtitle,
            "ano_publicacion" => '2023',
            "tipo_documento" => 1,
            "editorial_id" => 1,
            "autores" => [1],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subtitulo']);
    }

    //test para verificar que el campo subtitulo tenga al menos 3 caracteres
    public function test_catalogo_subtitle_min_length()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $shortTitle = 'ab'; // 2 caracteres
        $data = [
            "titulo" => "titulo valido",
            "subtitulo" => $shortTitle,
            "ano_publicacion" => '2023',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subtitulo']);
    }

    //'tipo_documento' => 'required|integer|min:1|' .
    // Rule::in(TipoDocumentoEnum::values()),

    //test para verificar que el campo tipo_documento es obligatorio
    public function test_catalogo_tipo_documento_is_required()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "ano_publicacion" => '2023',
            // "tipo_documento" => 1, // Omitido para probar la validación
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tipo_documento']);
    }

    public function test_catalogo_tipo_documento_is_integer()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "ano_publicacion" => '2023',
            "tipo_documento" => 'a', // Valor no entero para probar la validación
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tipo_documento']);
    }

    //test para verificar que el campo tipo_documento esté dentro de los valores permitidos
    public function test_catalogo_tipo_documento_in_allowed_values()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "ano_publicacion" => '2023',
            "tipo_documento" => 99, // Valor fuera de los permitidos para probar la validación
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tipo_documento']);
    }

    //'ano_publicacion'   => 'nullable|string|size:4',
    //test para verificar que el campo ano_publicacion tenga tamaño 4
    public function test_catalogo_ano_publicacion_size()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "ano_publicacion" => '202', // Tamaño incorrecto para probar la validación
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ano_publicacion']);
    }

    //test para verificar que el campo ano_publicacion sea string
    public function test_catalogo_ano_publicacion_string()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "ano_publicacion" => 2025, // tipo incorrecto para probar la validación
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ano_publicacion']);
    }

    //test para verificar que el campo ano_publicacion es requerido
    public function test_catalogo_ano_publicacion_required()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            //"ano_publicacion" => '2025', // Omitido para probar que es requerido
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ano_publicacion']);
    }

    //'descripcion_fisica' => 'nullable|string|min:3',

    //test para verificar que el campo descripcion_fisica
    //no tenga el minimo de caracteres
    public function test_catalogo_descripcion_fisica_min_length()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "descripcion_fisica" => '12',
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['descripcion_fisica']);
    }

    //test para verificar que el campo descripcion_fisica
    //es nullable
    public function test_catalogo_descripcion_fisica_nullable()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "descripcion_fisica" => '',
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(201);
    }


    // 'notas'  => 'nullable|string',
    //test para verificar que el campo notas
    //no tenga el minimo de caracteres
    public function test_catalogo_notas_min_length()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "notas" => '12',
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notas']);
    }

    //test para verificar que el campo notas es nullable
    public function test_catalogo_notas_nullable()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "notas" => '',
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(201);
    }

    //'isbn' => 'nullable|string|max:13|unique:catalogos,isbn,' . $this->catalogo?->id,
    //test para verificar que el campo isbn no exceda el maximo de caracteres
    public function test_catalogo_isbn_max_length()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "isbn" => str_repeat('a', 14),
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    //test para verificar que el campo isbn es nullable
    public function test_catalogo_isbn_nullable()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "isbn" => '',
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(201);
    }

    //test para verificar que el campo isbn sea unico
    public function test_catalogo_isbn_unique()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        // Crear un catálogo con un ISBN específico
        $existingIsbn = '1234567890123';
        \App\Models\Catalogo::factory()->create([
            'isbn' => $existingIsbn,
        ]);

        $data = [
            "isbn" => $existingIsbn, // Mismo ISBN para probar la unicidad
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }


    //'editorial_id' => 'required|exists:editorials,id',
    //test para verificar que el campo editorial_id es obligatorio
    public function test_catalogo_editorial_id_is_required()
    {
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            // "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['editorial_id']);
    }

    //test para verificar que el campo editorial_id exista en la tabla editorials
    public function test_catalogo_editorial_id_exists()
    {
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => 9999, // ID inexistente para probar la validación
            "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['editorial_id']);
    }


    //'autores'           => 'required|array|min:1',
    //'autores.*'         => 'exists:autors,id',

    //test para verificar que el campo autores es obligatorio
    public function test_catalogo_autores_is_required()
    {
        $editorial = \App\Models\Editorial::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            // "autores" => [$autor1->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['autores']);
    }

    //test para verificar que el campo autores sea un array
    public function test_catalogo_autores_is_array()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => 'not-an-array', // Valor no array para probar la validación
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['autores']);
    }

    //test para verificar que el campo autores tenga al menos un autor
    public function test_catalogo_autores_min_length()
    {
        $editorial = \App\Models\Editorial::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [], // Array vacío para probar la validación
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['autores']);
    }

    //test para verificar que cada autor en el campo autores exista en la tabla autors
    public function test_catalogo_autores_exists()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id, 9999], // 9999 no existe
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['autores.1']);
    }

    //'fecha_ingreso'  => 'required|date|before_or_equal:today',
    //test para verificar que el campo fecha_ingreso es obligatorio
    public function test_catalogo_fecha_ingreso_is_required()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            // "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_ingreso']);
    }

    //test para verificar que el campo fecha_ingreso sea una fecha valida
    public function test_catalogo_fecha_ingreso_is_date()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "invalid-date",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_ingreso']);
    }

    //test para verificar que el campo fecha_ingreso no sea una fecha futura
    public function test_catalogo_fecha_ingreso_not_future_date()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor = \App\Models\Autor::factory()->create();

        $futureDate = Carbon::now()->addDays(10)->format('Y-m-d');

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => $futureDate,
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_ingreso']);
    }

    //test para verificar que la fecha_ingreso sea la fecha actual al crear un catalogo
    public function test_catalogo_fecha_ingreso_is_current_date_on_create()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor = \App\Models\Autor::factory()->create();
        $actualDate = Carbon::now()->format('Y-m-d');

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => $actualDate,
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor->id],
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(201);
    }

    //'cantidad_de_ejemplares' => 'nullable|integer',
    //test para verificar que el campo cantidad_de_ejemplares sea un entero
    public function test_catalogo_cantidad_de_ejemplares_is_integer()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
            "cantidad_de_ejemplares" => 'not-an-integer', // Valor no entero para probar la validación
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cantidad_de_ejemplares']);
    }


    //test para verificar que el campo cantidad_de_ejemplares sea mayor o igual a 1
    public function test_catalogo_cantidad_de_ejemplares_min_value()
    {
        $editorial = \App\Models\Editorial::factory()->create();
        $autor1 = \App\Models\Autor::factory()->create();

        $data = [
            "titulo" => "titulo valido",
            "fecha_ingreso" => "2024-06-15",
            "ano_publicacion" => '2025',
            "tipo_documento" => 1,
            "editorial_id" => $editorial->id,
            "autores" => [$autor1->id],
            "cantidad_de_ejemplares" => 0, // Valor menor a 1 para probar la validación
        ];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cantidad_de_ejemplares']);
    }



    //  4.- Test para los filtros
    //test para el index de catalogos

    //test para verificar el filtro por titulo en el index de catalogos
    public function test_catalogo_index_filter_like_by_title()
    {
        $catalogo1 = \App\Models\Catalogo::factory()->create([
            'titulo' => 'El Quijote de la Mancha',
        ]);

        $catalogo2 = \App\Models\Catalogo::factory()->create([
            'titulo' => 'Cien Años de Soledad',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/catalogos?filters[titulo][like]=Quijote');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['titulo' => 'El Quijote de la Mancha']);
    }

    //test para verificar el filtro por ano_publicacion en el index de catalogos
    public function test_catalogo_index_filter_by_ano_publicacion()
    {
        $catalogo1 = \App\Models\Catalogo::factory()->create([
            'ano_publicacion' => '2000',
        ]);

        $catalogo2 = \App\Models\Catalogo::factory()->create([
            'ano_publicacion' => '1961',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/catalogos?filters[ano_publicacion]=1961');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['ano_publicacion' => '1961']);
    }

    ///api/v1/catalogos?sort=-id
    public function test_catalogo_index_sort_by_id_descending()
    {
        $catalogo1 = \App\Models\Catalogo::factory()->create();
        $catalogo2 = \App\Models\Catalogo::factory()->create();
        $catalogo3 = \App\Models\Catalogo::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/catalogos?sort=-id');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals($catalogo3->id, $data[0]['id']);
        $this->assertEquals($catalogo2->id, $data[1]['id']);
        $this->assertEquals($catalogo1->id, $data[2]['id']);
    }

    ///api/v1/catalogos?select=titulo,isbn
    //test para verificar el select de campos en el index de catalogos
    public function test_catalogo_index_select_specific_fields()
    {
        $catalogo = \App\Models\Catalogo::factory()->create([
            'titulo' => 'Titulo de Prueba',
            'isbn' => '1234567890123',
            'ano_publicacion' => '2020',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/catalogos?select=titulo,isbn');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'titulo' => 'Titulo de Prueba',
                'isbn' => '1234567890123',
            ])
            ->assertJsonMissing([
                'ano_publicacion' => '2020',
            ]);
    }

    ///api/v1/catalogos?include=autores,editorial
    //test para verificar el include de las relaciones en el index de catalogos
    public function test_catalogo_index_include_relationships()
    {
        $editorial = \App\Models\Editorial::factory()->create([
            'nombre' => 'Editorial de Prueba',
        ]);
        $autor = \App\Models\Autor::factory()->create([
            'nombre' => 'Autor de Prueba',
        ]);
        $catalogo = \App\Models\Catalogo::factory()->create([
            'titulo' => 'Titulo de Prueba',
            'editorial_id' => $editorial->id,
        ]);
        $catalogo->autores()->attach($autor->id);
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/catalogos?include=autores,editorial');
        $response->assertStatus(200)
            ->assertJsonFragment([
                'titulo' => 'Titulo de Prueba',
                'nombre' => 'Editorial de Prueba',
            ])
            ->assertJsonFragment([
                'nombre' => 'Autor de Prueba',
            ]);
    }

    ///api/v1/catalogos?filters[id][in]=2,3
    //test para verificar el filtro por in en el index de catalogos
    public function test_catalogo_index_filter_in_by_id()
    {
        $catalogo1 = \App\Models\Catalogo::factory()->create();
        $catalogo2 = \App\Models\Catalogo::factory()->create();
        $catalogo3 = \App\Models\Catalogo::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/catalogos?filters[id][in]=' . $catalogo2->id . ',' . $catalogo3->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $catalogo2->id])
            ->assertJsonFragment(['id' => $catalogo3->id]);
    }

    ///api/v1/catalogos?filters[id][>=]=3
    //test para verificar el filtro por mayor o igual en el index de catalogos
    public function test_catalogo_index_filter_greater_equal_by_id()
    {
        $catalogo1 = \App\Models\Catalogo::factory()->create();
        $catalogo2 = \App\Models\Catalogo::factory()->create();
        $catalogo3 = \App\Models\Catalogo::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/catalogos?filters[id][>%3D]=' . $catalogo2->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $catalogo2->id])
            ->assertJsonFragment(['id' => $catalogo3->id]);
    }

    ///api/v1/catalogos?filters[titulo][not_like]=libro
    //test para verificar el filtro por not like en el index de catalogos
    public function test_catalogo_index_filter_not_like_by_title()
    {
        $catalogo1 = \App\Models\Catalogo::factory()->create([
            'titulo' => 'El libro de la selva',
        ]);
        $catalogo2 = \App\Models\Catalogo::factory()->create([
            'titulo' => 'Cien Años de Soledad',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/catalogos?filters[titulo][not_like]=libro');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['titulo' => 'Cien Años de Soledad']);
    }

    ///api/v1/catalogos/4
    //test para verificar el show de un catalogo trae el id correcto
    public function test_catalogo_show_returns_correct_id()
    {
        $catalogo = \App\Models\Catalogo::factory()->create();
        $catalogo2 = \App\Models\Catalogo::factory()->create();
        $catalogo3 = \App\Models\Catalogo::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson("/api/v1/catalogos/{$catalogo2->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $catalogo2->id]);
    }

    ///api/v1/catalogos/4?include=ejemplares,autores
    //test para verificar el show de un catalogo con include de relaciones
    public function test_catalogo_show_include_relationships()
    {
        $editorial = \App\Models\Editorial::factory()->create([
            'nombre' => 'Editorial de Prueba',
        ]);
        $autor = \App\Models\Autor::factory()->create([
            'nombre' => 'Autor de Prueba',
        ]);
        $catalogo = \App\Models\Catalogo::factory()->create([
            'titulo' => 'Titulo de Prueba',
            'editorial_id' => $editorial->id,
        ]);
        $catalogo->autores()->attach($autor->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson("/api/v1/catalogos/{$catalogo->id}?include=autores,editorial");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'titulo' => 'Titulo de Prueba',
                'nombre' => 'Editorial de Prueba',
            ])
            ->assertJsonFragment([
                'nombre' => 'Autor de Prueba',
            ]);
    }

    //api/v1/catalogos/4?select=id,titulo,tipo_documento
    //test para verificar el show de un catalogo con select de campos
    public function test_catalogo_show_select_specific_fields()
    {
        $catalogo = \App\Models\Catalogo::factory()->create([
            'titulo' => 'Titulo de Prueba',
            'tipo_documento' => 2,
            'ano_publicacion' => '2020',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson("/api/v1/catalogos/{$catalogo->id}?select=id,titulo,tipo_documento");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $catalogo->id,
                'titulo' => 'Titulo de Prueba',
                'tipo_documento' => 2,
            ])
            ->assertJsonMissing([
                'ano_publicacion' => '2020',
            ]);
    }

    ///api/v1/catalogos/4?include=editorial,ejemplares,autores&select=id,titulo,tipo_documento
    //test para verificar el show de un catalogo con include y select de campos
    public function test_catalogo_show_include_and_select()
    {
        $editorial = \App\Models\Editorial::factory()->create([
            'nombre' => 'Editorial de Prueba',
        ]);
        $autor = \App\Models\Autor::factory()->create([
            'nombre' => 'Autor de Prueba',
        ]);
        $catalogo = \App\Models\Catalogo::factory()->create([
            'titulo' => 'Titulo de Prueba',
            'tipo_documento' => 2,
            'editorial_id' => $editorial->id,
        ]);
        $catalogo->autores()->attach($autor->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson("/api/v1/catalogos/{$catalogo->id}?include=autores,editorial&select=id,titulo,tipo_documento");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $catalogo->id,
                'titulo' => 'Titulo de Prueba',
                'tipo_documento' => 2,
                'nombre' => 'Editorial de Prueba',
            ])
            ->assertJsonFragment([
                'nombre' => 'Autor de Prueba',
            ])
            ->assertJsonMissing([
                'ano_publicacion' => $catalogo->ano_publicacion,
            ]);
    }
}
