<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Trait\Test\AuthenticatesAsCataloger;
use Tests\TestCase;

class EditorialTest extends TestCase
{
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * 1.- Test para el CRUD de Editoriales
     * 2.- Test para las validaciones de Editoriales
     * 3.- Test para los permisos de Editoriales
     */

    use RefreshDatabase, WithFaker, AuthenticatesAsCataloger;

    protected function setUp(): void
    {
        parent::setUp();
        // Ejecutar el método del trait antes de cada test
        $this->authenticateAsCataloger();
    }

    //TEST CRUD EDITORIALES
    //test para crear editorial
    public function test_create_editorial()
    {
        $data = [
            'nombre' => 'Editorial Test',
            'direccion' => 'Calle Falsa 123',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/editoriales', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nombre',
                    'direccion',
                ],
                'message',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nombre' => $data['nombre'],
                'direccion' => $data['direccion'],
            ]);

        $this->assertDatabaseHas('editorials', $data);
    }

    public function test_returns_a_list_of_editoriales()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/editoriales');

        $response->assertStatus(200);
    }

    //test para update editorial
    public function test_update_editorial()
    {
        // Crear una editorial primero
        $editorial = \App\Models\Editorial::factory()->create();

        $data = [
            'nombre' => 'Editorial Actualizada',
            'direccion' => 'Nueva Dirección 456',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->putJson("/api/v1/editoriales/{$editorial->id}", $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nombre',
                    'direccion',
                ],
                'message',
            ]);
    }

    //test para verificar que se puede eliminar una editorial
    public function test_deletes_an_editoriales()
    {
        $editorial = \App\Models\Editorial::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/editoriales/{$editorial->id}");

        $response->assertStatus(204);
    }

    /**
     * Test para verificar que no se puede eliminar
     * una editorial si tiene catálogos asociados.
     * Espera un status 409 Conflict.
     */
    public function test_cannot_delete_editorial_with_associated_catalogos()
    {
        // 1. Setup: Crear una editorial y asociarle al menos un catálogo
        $editorial = \App\Models\Editorial::factory()->create();
        $autor = \App\Models\Autor::factory()->create();

        $catalogo = [
            "fecha_ingreso" => '2025-10-29',
            "ano_publicacion" => '2010',
            "tipo_documento" => 1,
            "titulo" => 'Libro sobre Laravel',
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "cantidad_de_ejemplares" => 1,
            "autores" => [$autor->id],
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/catalogos', $catalogo);

        // 2. Ejecutar la petición DELETE
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/editoriales/{$editorial->id}");

        // 3. Afirmaciones
        // Verificar el código de estado 409 Conflict
        $response->assertStatus(409)
            // Verificar el mensaje de error específico
            ->assertJson([
                'message' => 'No se puede eliminar la Editorial porque tiene un documento asociado.',
            ]);

        // Verificar que la editorial sigue existiendo en la base de datos
        $this->assertDatabaseHas('editorials', ['id' => $editorial->id]);
    }

    /** TEST PERMISOS*/
    //test para verificar que un usuario sin el rol de catalogador
    //no puede crear un editorial
    public function test_non_cataloger_cannot_create_editorial()
    {
        // Crear un usuario sin el rol de catalogador
        $user = \App\Models\User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $data = [
            'nombre'    => $this->faker->name,
            'direccion' => 'Dirección de la editorial',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/editoriales', $data);

        $response->assertStatus(403); // Forbidden
    }

    //test para verificar que un usuario sin el rol de catalogador
    //no puede eliminar una editorial
    public function test_non_cataloger_cannot_delete_editorial()
    {
        // Crear un usuario sin el rol de catalogador
        $user       = \App\Models\User::factory()->create();
        $token      = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $editorial  = \App\Models\Editorial::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/editoriales/{$editorial->id}");

        $response->assertStatus(403); // Forbidden

    }

    //test para verificar que un usuario sin el rol de catalogador
    //no puede actualizar una editorial
    public function test_non_cataloger_cannot_update_editorial()
    {
        // Crear un usuario sin el rol de catalogador
        $user = \App\Models\User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $editorial = \App\Models\Editorial::factory()->create();

        $data = [
            'nombre'    => 'Nombre No Permitido',
            'direccion' => 'Direccion',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->putJson("/api/v1/editoriales/{$editorial->id}", $data);

        $response->assertStatus(403); // Forbidden
    }

    //test para verificar que un usuario con permiso editorial.store
    //puede crear un editorial, sin importar el rol
    public function test_user_with_permission_can_create_editorial()
    {
        $bibliotecario_rol =  \Spatie\Permission\Models\Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'editorial.store',
            'guard_name' => 'api',
        ]);

        $bibliotecario_rol->syncPermissions([
            'editorial.store',
        ]);

        $user = \App\Models\User::factory()->create();
        $user->assignRole($bibliotecario_rol);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $data = [
            'nombre'    => $this->faker->name,
            'direccion' => 'direccion',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/editoriales', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nombre' => $data['nombre'],
                'direccion' => $data['direccion'],
            ]);

        $this->assertDatabaseHas('editorials', $data);
    }

    //test para verificar que un usuario con permiso editorial.destroy
    //puede eliminar un editorial, sin importar el rol
    public function test_user_with_permission_can_delete_editorial()
    {
        $bibliotecario_rol =  \Spatie\Permission\Models\Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'editorial.destroy',
            'guard_name' => 'api',
        ]);

        $bibliotecario_rol->syncPermissions([
            'editorial.destroy',
        ]);

        $user = \App\Models\User::factory()->create();
        $user->assignRole($bibliotecario_rol);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $editorial = \App\Models\Editorial::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/editoriales/{$editorial->id}");

        $response->assertStatus(204);
    }

    //test para verificar que un usuario con permiso editorial.update
    //puede actualizar una editorial, sin importar el rol
    public function test_user_with_permission_can_update_editorial()
    {
        $bibliotecario_rol =  \Spatie\Permission\Models\Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'editorial.update',
            'guard_name' => 'api',
        ]);

        $bibliotecario_rol->syncPermissions([
            'editorial.update',
        ]);

        $user = \App\Models\User::factory()->create();
        $user->assignRole($bibliotecario_rol);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $editorial = \App\Models\Editorial::factory()->create();

        $data = [
            'nombre'    => 'Nombre Permitido',
            'direccion' => 'Dirección',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->putJson("/api/v1/editoriales/{$editorial->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('editorials', array_merge(['id' => $editorial->id], $data));
    }

    /** TEST VALIDACIONES*/
    //Test para verificar datos correctos al crear una editorial
    public function test_create_editorial_validation_errors()
    {
        $data = [
            'nombre'    => '', //nombre vacio
            'direccion' => 'x', //min:3
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/editoriales', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'direccion']);
    }

    public function test_create_editorial_with_max_length_exceeded_fails()
    {
        $longName = $this->faker->text(200); // 200 caracteres,  100 es límite

        $data = [
            'nombre' => $longName,
            'direccion' => 'Direccion',
        ];

        $response = $this->postJson('/api/v1/editoriales', $data);

        // Debe fallar con 422 (Unprocessable Entity)
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_create_editorial_with_max_length_direccion_exceeded_fails()
    {
        $longName = $this->faker->text(256); // 300 caracteres,  255 es límite
        $direccion_laraga = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, quis gravida magna mi a libero. Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus. Nullam accumsan lorem in dui. Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum. Sed aliquam ultrices mauris. Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing. Phasellus ullamcorper ipsum rutrum nunc. Nunc nonummy metus. Vestibulum volutpat pretium libero. Cras id dui. Aenean ut";

        $data = [
            'nombre'    => 'Editorial',
            'direccion' => $direccion_laraga,
        ];

        $response = $this->postJson('/api/v1/editoriales', $data);

        // Debe fallar con 422 (Unprocessable Entity)
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['direccion']);
    }

    //test para verificar que el nombre no tenga menos de 3 caracteres
    public function test_create_editorial_name_too_short_fails()
    {
        $data = [
            'nombre'    => 'Al', // Nombre con menos de 3 caracteres
            'direecion' => '',
        ];

        $response = $this->postJson('/api/v1/editoriales', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_create_editorial_with_non_string_data_fails()
    {
        $data = [
            'nombre' => 12345, // Intento de enviar un número
            'direccion' => 'direccion',
        ];

        $response = $this->postJson('/api/v1/editoriales', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    /** @test para crear editorial solo con los campos requeridos */
    public function test_creates_editorial_with_only_required_fields()
    {
        $data = [
            'nombre' => $this->faker->unique()->name, // Usar unique() para pasar la validación
        ];

        // Omitir direccion
        $response = $this->postJson('/api/v1/editoriales', $data);

        $response->assertStatus(201);

        // Confirma que los campos opcionales se guardaron como null en la base de datos
        $this->assertDatabaseHas('editorials', array_merge($data, [
            'direccion' => null,
        ]));
    }

    //test para verificar que un campo opcional, si ya tiene valor,
    //pueda ser "limpiado" o actualizado a null.
    public function test_updates_optional_fields_to_null()
    {
        $editorial = \App\Models\Editorial::factory()->create();

        $data = [
            'nombre'    => $editorial->nombre, // Mantener el nombre
            'direccion' => null,            // Establecer a null
        ];

        $response = $this->putJson("/api/v1/editoriales/{$editorial->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('editorials', [
            'id' => $editorial->id,
            'direccion' => null,
        ]);
    }

    //test para verificar la regla unique en la creacion de una editorial
    public function test_create_editorial_unique_name_validation()
    {
        $existingEditorial = \App\Models\Editorial::factory()->create([
            'nombre' => 'Editorial Existente',
        ]);

        $data = [
            'nombre'    => 'Editorial Existente', // Mismo nombre que el editorial existente
        ];

        $response = $this->postJson('/api/v1/editoriales', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    //test para verificar la regla unique en la actualizacion de una editorial
    public function test_update_editorial_unique_name_validation()
    {
        $editorial1 = \App\Models\Editorial::factory()->create([
            'nombre' => 'Editorial Uno',
        ]);

        $editorial2 = \App\Models\Editorial::factory()->create([
            'nombre' => 'Editorial Dos',
        ]);

        $data = [
            'nombre' => 'Editorial Uno', // Intentar actualizar al nombre del Editorial1
        ];

        $response = $this->putJson("/api/v1/editoriales/{$editorial2->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    /** TEST FILTROS scope*/
    //Prueba de Paginación y Contenido
    public function test_editorial_index_pagination_and_content()
    {
        // Crear 15 editoriales para probar la paginación
        \App\Models\Editorial::factory()->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/editoriales?perPage=5&page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);

        // Verificar que la página actual es la 2
        $this->assertEquals(2, $response->json('meta.current_page'));
        // Verificar que hay 5 items en la página
        $this->assertCount(5, $response->json('data'));
    }

    //Prueba de búsqueda de editorial por nombre,
    //  /api/v1/editoriales?filters[nombre]=nombreEditorial
    public function test_editorial_index_search_by_name()
    {
        // Crear editoriales con nombres específicos
        \App\Models\Editorial::factory()->create(['nombre' => 'Monte Ávila Editores']);
        \App\Models\Editorial::factory()->create(['nombre' => 'Biblioteca Ayacucho']);
        \App\Models\Editorial::factory()->create(['nombre' => 'Fundación Polar']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/editoriales?filters[nombre]=Monte Ávila Editores');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // Debe devolver solo 1 editorial
            ->assertJsonFragment(['nombre' => 'Monte Ávila Editores']);
    }

    public function test_editorial_index_search_by_name_like()
    {
        // Crear editoriales con nombres específicos
        \App\Models\Editorial::factory()->create(['nombre' => 'Monte Ávila Editores']);
        \App\Models\Editorial::factory()->create(['nombre' => 'Biblioteca Ayacucho']);
        \App\Models\Editorial::factory()->create(['nombre' => 'Fundación Polar']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/editoriales?filters[nombre][like]=Ayacucho');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // Debe devolver solo 1 editorial
            ->assertJsonFragment(['nombre' => 'Biblioteca Ayacucho']);
    }

    //test para verificar api/v1/editoriales?select=id,nombre devuelve solo los campos id y nombre
    public function test_editorial_index_select_specific_fields()
    {
        // Crear un editorial
        \App\Models\Editorial::factory()->create(['nombre' => 'Biblioteca Ayacucho', 'direccion' => 'Dirección']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/editoriales?select=id,nombre');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nombre'], // Solo debe tener id y nombre
                ],
            ])
            ->assertJsonMissing(['direccion' => 'Dirección']); // No debe contener Dirección
    }

    //test para verificar api/v1/editoriales?sort=-nombre
    //devuelve las editoriales ordenados por nombre descendente
    public function test_autor_index_sort_by_name_descending()
    {
        // Crear editoriales con nombres específicos
        \App\Models\Editorial::factory()->create(['nombre' => 'Monte Ávila Editores']);
        \App\Models\Editorial::factory()->create(['nombre' => 'Biblioteca Ayacucho']);
        \App\Models\Editorial::factory()->create(['nombre' => 'Fundación Polar']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/editoriales?sort=-nombre');

        $response->assertStatus(200);

        $nombres = array_column($response->json('data'), 'nombre');
        $sortedNombres = $nombres;
        rsort($sortedNombres);

        $this->assertEquals($sortedNombres, $nombres);
    }
}
