<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $permissions = Permission::where('guard_name', 'web')
            ->when($request->search, function ($query, $search) {
                $query->where(
                    'name',
                    'like',
                    "%{$search}%"
                );
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/permissions/Index', [
            'permissions' => $permissions,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/permissions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name',
            ],
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Permiso creado correctamente.',
        ]);

        return to_route(
            'admin.permissions.index'
        );
    }

    public function edit(Permission $permission)
    {
        return Inertia::render(
            'admin/permissions/Edit',
            [
                'permission' => $permission,
            ]
        );
    }

    public function update(
        Request $request,
        Permission $permission
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name,'.$permission->id,
            ],
        ]);

        $permission->update([
            'name' => $validated['name'],
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Permiso actualizado correctamente.',
        ]);

        return to_route(
            'admin.permissions.index'
        );
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Permiso eliminado correctamente.',
        ]);

        return to_route(
            'admin.permissions.index'
        );
    }
}
