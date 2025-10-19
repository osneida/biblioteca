<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    //test para verificar Token Ausente
    public function test_access_without_token_fails()
    {
        $data = [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'V',
        ];

        // Forzar ausencia de Authorization sobrescribiendo el header (será tratado como inexistente)
        $response = $this->withHeaders([
            'Authorization' => '', // sobrescribe cualquier Authorization seteado en setUp()
            'Accept' => 'application/json',
        ])->postJson('/api/v1/autores', $data);

        // No se adjunta ningún header de autorización
        // $response = $this->postJson('/api/v1/autores', $data);
        $response->assertStatus(401); // Unauthorized
    }

    //test para verificar Token Inválido
    public function test_access_with_invalid_token_fails()
    {
        $data = [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'V',
        ];

        $invalidToken = 'Bearer invalid.token.here';
        $response = $this->withHeaders([
            'Authorization' => $invalidToken,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/autores', $data);
        $response->assertStatus(401); // Unauthorized
    }
}
