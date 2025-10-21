<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Trait\Test\AuthenticatesAsCataloger;
use Tests\TestCase;

class AutorTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticatesAsCataloger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticateAsCataloger();
    }

    public function test_status(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** TEST CRUD*/
    public function test_creates_an_autor()
    {
        $data = [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'V',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/autores', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nombre' => $data['nombre'],
                'nacionalidad' => $data['nacionalidad'],
            ]);

        $this->assertDatabaseHas('autors', $data);
    }

    public function test_returns_a_list_of_autores()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/autores');

        $response->assertStatus(200);
    }

    public function test_updates_an_autor()
    {
        $autor = \App\Models\Autor::factory()->create();

        $data = [
            'nombre' => 'Nuevo Nombre',
            'nacionalidad' => 'E',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->putJson("/api/v1/autores/{$autor->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('autors', array_merge(['id' => $autor->id], $data));
    }

    public function test_deletes_an_autor()
    {
        $autor = \App\Models\Autor::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/autores/{$autor->id}");

        $response->assertStatus(204);
    }

    /** TEST PERMISOS*/
    //test para verificar que un usuario sin el rol de catalogador no puede crear un autor
    public function test_non_cataloger_cannot_create_autor()
    {
        // Crear un usuario sin el rol de catalogador
        $user = \App\Models\User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $data = [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'V',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/autores', $data);

        $response->assertStatus(403); // Forbidden
    }

    //test para verificar que un usuario sin el rol de catalogador no puede eliminar un autor
    public function test_non_cataloger_cannot_delete_autor()
    {
        // Crear un usuario sin el rol de catalogador
        $user = \App\Models\User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $autor = \App\Models\Autor::factory()->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/autores/{$autor->id}");

        $response->assertStatus(403); // Forbidden

    }

    //test para verificar que un usuario sin el rol de catalogador no puede actualizar un autor
    public function test_non_cataloger_cannot_update_autor()
    {
        // Crear un usuario sin el rol de catalogador
        $user = \App\Models\User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $autor = \App\Models\Autor::factory()->create();

        $data = [
            'nombre' => 'Nombre No Permitido',
            'nacionalidad' => 'E',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->putJson("/api/v1/autores/{$autor->id}", $data);

        $response->assertStatus(403); // Forbidden
    }

    //test para verificar que un usuario con permiso autor.store
    //puede crear un autor, sin importar el rol
    public function test_user_with_permission_can_create_autor()
    {
        $bibliotecario_rol =  \Spatie\Permission\Models\Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'autor.store',
            'guard_name' => 'api',
        ]);

        $bibliotecario_rol->syncPermissions([
            'autor.store',
        ]);

        $user = \App\Models\User::factory()->create();
        $user->assignRole($bibliotecario_rol);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $data = [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'V',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/autores', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nombre' => $data['nombre'],
                'nacionalidad' => $data['nacionalidad'],
            ]);

        $this->assertDatabaseHas('autors', $data);
    }

    //test para verificar que un usuario con permiso autor.destroy
    //puede eliminar un autor, sin importar el rol
    public function test_user_with_permission_can_delete_autor()
    {
        $bibliotecario_rol =  \Spatie\Permission\Models\Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'autor.destroy',
            'guard_name' => 'api',
        ]);

        $bibliotecario_rol->syncPermissions([
            'autor.destroy',
        ]);

        $user = \App\Models\User::factory()->create();
        $user->assignRole($bibliotecario_rol);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $autor = \App\Models\Autor::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/autores/{$autor->id}");

        $response->assertStatus(204);
    }

    //test para verificar que un usuario con permiso autor.update
    //puede actualizar un autor, sin importar el rol
    public function test_user_with_permission_can_update_autor()
    {
        $bibliotecario_rol =  \Spatie\Permission\Models\Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'autor.update',
            'guard_name' => 'api',
        ]);

        $bibliotecario_rol->syncPermissions([
            'autor.update',
        ]);

        $user = \App\Models\User::factory()->create();
        $user->assignRole($bibliotecario_rol);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $autor = \App\Models\Autor::factory()->create();

        $data = [
            'nombre' => 'Nombre Permitido',
            'nacionalidad' => 'E',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->putJson("/api/v1/autores/{$autor->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('autors', array_merge(['id' => $autor->id], $data));
    }

    /** TEST VALIDACIONES*/
    //Test para verificar datos correctos al crear un autor
    public function test_create_autor_validation_errors()
    {
        $data = [
            'nombre' => '', //nombre vacio
            'nacionalidad' => 'X', //nacionalidad no valida
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/v1/autores', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'nacionalidad']);
    }

    public function test_create_autor_with_max_length_exceeded_fails()
    {
        $longName = $this->faker->text(200); // 200 caracteres,  100 es límite
        $direccion_laraga = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, quis gravida magna mi a libero. Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus. Nullam accumsan lorem in dui. Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum. Sed aliquam ultrices mauris. Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing. Phasellus ullamcorper ipsum rutrum nunc. Nunc nonummy metus. Vestibulum volutpat pretium libero. Cras id dui. Aenean ut";
        $data = [
            'nombre' => $direccion_laraga,
            'nacionalidad' => 'V',
        ];

        $response = $this->postJson('/api/v1/autores', $data);

        // Debe fallar con 422 (Unprocessable Entity)
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    //test para verificar que el nombre no tenga menos de 3 caracteres
    public function test_create_autor_name_too_short_fails()
    {
        $data = [
            'nombre' => 'Al', // Nombre con menos de 3 caracteres
            'nacionalidad' => 'V',
        ];

        $response = $this->postJson('/api/v1/autores', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_create_autor_with_non_string_data_fails()
    {
        $data = [
            'nombre' => 12345, // Intento de enviar un número
            'nacionalidad' => 'V',
        ];

        $response = $this->postJson('/api/v1/autores', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_create_autor_with_invalid_nacionalidad_code_fails()
    {
        $data = [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'VENEZUELA', // Nombre completo en lugar de código
        ];

        $response = $this->postJson('/api/v1/autores', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nacionalidad']);
    }


    /** @test para crear autor solo con los campos requeridos */
    public function test_creates_autor_with_only_required_fields()
    {
        $data = [
            'nombre' => $this->faker->unique()->name, // Usar unique() para pasar la validación
            'nacionalidad' => 'V',
        ];

        // Omitir 'fecha_nacimiento', 'fecha_fallecimiento', y 'biografia'
        $response = $this->postJson('/api/v1/autores', $data);

        $response->assertStatus(201);

        // Confirma que los campos opcionales se guardaron como null en la base de datos
        $this->assertDatabaseHas('autors', array_merge($data, [
            'fecha_nacimiento' => null,
            'fecha_fallecimiento' => null,
            'biografia' => null
        ]));
    }

    // test para verificar que un campo opcional, si ya tiene valor,
    //pueda ser "limpiado" o actualizado a null.
    public function test_updates_optional_fields_to_null()
    {
        // Autor creado con todos los campos llenos
        $autor = \App\Models\Autor::factory()->create([
            'fecha_nacimiento' => '1950-01-01',
            'biografia' => 'Biografía inicial.',
        ]);

        $data = [
            'nombre' => $autor->nombre, // Mantener el nombre
            'nacionalidad' => $autor->nacionalidad, // Mantener nacionalidad
            'fecha_nacimiento' => null, // Establecer a null
            'biografia' => null,        // Establecer a null
        ];

        $response = $this->putJson("/api/v1/autores/{$autor->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('autors', [
            'id' => $autor->id,
            'fecha_nacimiento' => null,
            'biografia' => null,
        ]);
    }

    //test para verificar la regla unique en la creacion de un autor
    public function test_create_autor_unique_name_validation()
    {
        $existingAutor = \App\Models\Autor::factory()->create([
            'nombre' => 'Autor Existente',
        ]);

        $data = [
            'nombre' => 'Autor Existente', // Mismo nombre que el autor existente
            'nacionalidad' => 'V',
        ];

        $response = $this->postJson('/api/v1/autores', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    //test para verificar la regla unique en la actualizacion de un autor
    public function test_update_autor_unique_name_validation()
    {
        $autor1 = \App\Models\Autor::factory()->create([
            'nombre' => 'Autor Uno',
        ]);

        $autor2 = \App\Models\Autor::factory()->create([
            'nombre' => 'Autor Dos',
        ]);

        $data = [
            'nombre' => 'Autor Uno', // Intentar actualizar al nombre del autor1
            'nacionalidad' => 'E',
        ];

        $response = $this->putJson("/api/v1/autores/{$autor2->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    //test para verificar que la fecha de fallecimiento no pueda ser anterior a la fecha de nacimiento.
    public function test_create_autor_fecha_fallecimiento_before_fecha_nacimiento_fails()
    {
        $data = [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'V',
            'fecha_nacimiento' => '2000-01-01',
            'fecha_fallecimiento' => '1990-01-01', // Fecha de fallecimiento antes de nacimiento
        ];

        $response = $this->postJson('/api/v1/autores', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_fallecimiento']);
    }

    //test para verificar que la fecha de fallecimiento
    //NO pueda ser igual a la fecha de nacimiento debe ser mayor.
    public function test_create_autor_fecha_fallecimiento_equal_fecha_nacimiento_fails()
    {
        $data = [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'V',
            'fecha_nacimiento' => '2000-01-01',
            'fecha_fallecimiento' => '2000-01-01', // Fecha de fallecimiento igual a nacimiento
        ];

        $response = $this->postJson('/api/v1/autores', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_fallecimiento']);
    }

    //test para verificar que la fecha de fallecimiento
    //no pueda ser anterior a la fecha de nacimiento en la actualizacion.
    public function test_update_autor_fecha_fallecimiento_before_fecha_nacimiento_fails()
    {
        $autor = \App\Models\Autor::factory()->create([
            'fecha_nacimiento' => '2000-01-01',
        ]);

        $data = [
            'nombre' => $autor->nombre,
            'nacionalidad' => $autor->nacionalidad,
            'fecha_nacimiento' => '2000-01-01',
            'fecha_fallecimiento' => '1990-01-01', // Fecha de fallecimiento antes de nacimiento
        ];

        $response = $this->putJson("/api/v1/autores/{$autor->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fecha_fallecimiento']);
    }

    //test para verificar que la nacionalidad acepte
    //solo los valores definidos en el enum NacionalidadEnum
    public function test_create_autor_invalid_nacionalidad_fails()
    {
        $data = [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'X', // Valor no definido en el enum
        ];

        $response = $this->postJson('/api/v1/autores', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nacionalidad']);
    }

    /** TEST FILTROS scope*/
    //Prueba de Paginación y Contenido
    public function test_autor_index_pagination_and_content()
    {
        // Crear 15 autores para probar la paginación
        \App\Models\Autor::factory()->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/autores?perPage=5&page=2');

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

    //Prueba de búsqueda de autores por nombre, /api/v1/autores?filters[nombre]=nombreAutor
    public function test_autor_index_search_by_name()
    {
        // Crear autores con nombres específicos
        \App\Models\Autor::factory()->create(['nombre' => 'Gabriel García Márquez']);
        \App\Models\Autor::factory()->create(['nombre' => 'Isabel Allende']);
        \App\Models\Autor::factory()->create(['nombre' => 'Mario Vargas Llosa']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/autores?filters[nombre]=Gabriel García Márquez');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // Debe devolver solo 1 autor
            ->assertJsonFragment(['nombre' => 'Gabriel García Márquez']);
    }

    public function test_autor_index_search_by_name_like()
    {
        // Crear autores con nombres específicos
        \App\Models\Autor::factory()->create(['nombre' => 'Gabriel García Márquez']);
        \App\Models\Autor::factory()->create(['nombre' => 'Isabel Allende']);
        \App\Models\Autor::factory()->create(['nombre' => 'Mario Vargas Llosa']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/autores?filters[nombre][like]=Isabel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // Debe devolver solo 1 autor
            ->assertJsonFragment(['nombre' => 'Isabel Allende']);
    }

    //test para verificar api/v1/autores?select=id,nombre devuelve solo los campos id y nombre
    public function test_autor_index_select_specific_fields()
    {
        // Crear un autor
        \App\Models\Autor::factory()->create(['nombre' => 'Gabriel García Márquez', 'nacionalidad' => 'V']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/autores?select=id,nombre');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nombre'], // Solo debe tener id y nombre
                ],
            ])
            ->assertJsonMissing(['nacionalidad' => 'V']); // No debe contener nacionalidad
    }

    //test para verificar api/v1/autores?sort=-nombre
    //devuelve los autores ordenados por nombre descendente
    public function test_autor_index_sort_by_name_descending()
    {
        // Crear autores con nombres específicos
        \App\Models\Autor::factory()->create(['nombre' => 'Gabriel García Márquez']);
        \App\Models\Autor::factory()->create(['nombre' => 'Isabel Allende']);
        \App\Models\Autor::factory()->create(['nombre' => 'Mario Vargas Llosa']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}",
            'Accept' => 'application/json',
        ])->getJson('/api/v1/autores?sort=-nombre');

        $response->assertStatus(200);

        $nombres = array_column($response->json('data'), 'nombre');
        $sortedNombres = $nombres;
        rsort($sortedNombres);

        $this->assertEquals($sortedNombres, $nombres);
    }
}
