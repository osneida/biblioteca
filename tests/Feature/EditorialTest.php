<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
