<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'username',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        );
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render(
            'admin/users/Create',
            [
                'roles' => Role::where(
                    'guard_name',
                    'web'
                )
                    ->orderBy('name')
                    ->get(),
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'role' => [
                'required',
                'exists:roles,name',
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        $user->assignRole(
            $validated['role']
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Usuario creado correctamente.',
        ]);

        return to_route(
            'admin.users.index'
        );
    }

    public function edit(User $user)
    {
        $user->load('roles');

        return Inertia::render(
            'admin/users/Edit',
            [
                'user' => $user,
                'roles' => Role::where(
                    'guard_name',
                    'web'
                )
                    ->orderBy('name')
                    ->get(),
            ]
        );
    }

    public function update(
        Request $request,
        User $user
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username,'.$user->id,
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,'.$user->id,
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'role' => [
                'required',
                'exists:roles,name',
            ],
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make(
                $validated['password']
            );
        }

        $user->update($data);

        $user->syncRoles([
            $validated['role'],
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Usuario actualizado correctamente.',
        ]);

        return to_route(
            'admin.users.index'
        );
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'No puedes eliminar tu propio usuario.',
            ]);

            return back();
        }

        $user->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Usuario eliminado correctamente.',
        ]);

        return to_route(
            'admin.users.index'
        );
    }
}
