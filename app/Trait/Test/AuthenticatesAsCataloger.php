<?php

namespace App\Trait\Test;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

trait AuthenticatesAsCataloger
{
    //se debe agregar en .env.testing
    /** printf "JWT_SECRET=%s\n" "$(php -r 'echo bin2hex(random_bytes(32));')" >> .env.testing */
    /** @test */

    protected User $catalogador;
    protected string $jwtToken;

    protected function createCatalogerUser()
    {
        // Crear el Rol si no existe
        $role = Role::firstOrCreate([
            'name' => 'catalogador',
            'guard_name' => 'api',
        ]);

        // Crear el Usuario y asignar el rol
        $this->catalogador = User::factory()->create();
        $this->catalogador->assignRole($role);

        // Generar el Token JWT
        $this->jwtToken = JWTAuth::fromUser($this->catalogador);

        return $this->catalogador;
    }

    protected function authenticateAsCataloger()
    {
        // Llamar el setup si no se ha hecho, o re-generar el token
        if (!isset($this->catalogador)) {
            $this->createCatalogerUser();
        }

        // Configurar la cabecera para todas las llamadas subsiguientes
        $this->withHeader('Authorization', "Bearer {$this->jwtToken}");
        $this->withHeader('Accept', 'application/json');
    }
}
