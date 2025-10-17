<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'estudiante',
            'profesor',
            'personal-administrativo',
        ];

        foreach ($roles as $roleName) {
            $rol = Role::create([
                'name' => $roleName,
                'guard_name' => 'api',
            ]);

            $rol->syncPermissions([
                'busqueda.index',
            ]);
        }

        $bibliotecario =  Role::create([
            'name' => 'bibliotecario',
            'guard_name' => 'api',
        ]);

        $bibliotecario->syncPermissions([
            'busqueda.index',
            'prestamo.index',
            'prestamo.store',
            'prestamo.show',
            'prestamo.update',
            'prestamo.destroy',
        ]);

        //usuario admin
        $catalogador =  Role::create([
            'name' => 'catalogador',
            'guard_name' => 'api',
        ]);

        $permissions = Permission::all();
        $catalogador->syncPermissions($permissions);
    }
}