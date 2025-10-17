<?php

namespace App\Http\Controllers\Api\Permisos;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('role.index');
        $roles = Role::all();
        return  RoleResource::collection($roles);
    }

    public function store(RoleRequest $request)
    {
        Gate::authorize('role.store');
        $permissions = $request->permissions ?? [];
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'api',
        ]);
        $role->permissions()->attach($permissions);

        return RoleResource::make($role);
    }

    public function show(Role $role)
    {
        Gate::authorize('role.show');
        return RoleResource::make($role);
    }

    public function update(RoleRequest $request, Role $role)
    {
        Gate::authorize('role.update');

        $role->update([
            'name' => $request->name,
        ]);

        $permissions = $request->permissions ?? [];
        $role->permissions()->sync($permissions);

        return RoleResource::make($role);
    }

    public function destroy(Role $role)
    {
        Gate::authorize('role.destroy');
        $role->delete();
        return response()->noContent();
    }
}