<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'permission.index',
            'permission.store',
            'permission.show',
            'permission.update',
            'permission.destroy',
            'role.index',
            'role.store',
            'role.show',
            'role.update',
            'role.destroy',
            'busqueda.index',
            'prestamo.index',
            'prestamo.store',
            'prestamo.show',
            'prestamo.update',
            'prestamo.destroy',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }
    }
}