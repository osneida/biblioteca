<?php

namespace App\Http\Controllers\Api\Permisos;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class PermissionControles extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api')
        ];
    }

    public function index()
    {
        Gate::authorize('permission.index');
        $permissions = Permission::all();
        return PermissionResource::collection($permissions);
    }

    public function store(PermissionRequest $request)
    {
        Gate::authorize('permission.store');

        $data = $request->all();
        $data['guard_name'] = 'api';
        $permission = Permission::create($data);
        return PermissionResource::make($permission);
    }

    public function show(Permission $permission)
    {
        Gate::authorize('permission.show');
        return PermissionResource::make($permission);
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        Gate::authorize('permission.update');

        $permission->update($request->all());
        return PermissionResource::make($permission);
    }

    public function destroy(Permission $permission)
    {
        Gate::authorize('permission.destroy');
        $permission->delete();
        return response()->noContent();
    }
}