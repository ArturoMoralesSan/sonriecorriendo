<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ClubController extends Controller
{
    /**
     * Display a listing of the clubs.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $clubs = Club::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('responsible', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/clubs/Index', [
            'clubs' => $clubs,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new club.
     */
    public function create(): Response
    {
        return Inertia::render('admin/clubs/Create');
    }

    /**
     * Store a newly created club.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:clubs,slug',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'responsible' => [
                'nullable',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'city' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | SLUG
        |--------------------------------------------------------------------------
        */

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug(
                $validated['name']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            $directory = storage_path(
                'app/public/clubs'
            );

            if (! is_dir($directory)) {
                mkdir(
                    $directory,
                    0755,
                    true
                );
            }

            $logo = $request->file('logo');

            $filename =
                uniqid() .
                '.' .
                $logo->getClientOriginalExtension();

            $logo->move(
                $directory,
                $filename
            );

            $validated['logo'] =
                "clubs/{$filename}";
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        Club::create($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Club creado correctamente.',
        ]);

        return redirect()
            ->route('admin.clubs.index');
    }

    /**
     * Display the specified club.
     */
    public function show(Club $club): Response
    {
        return Inertia::render('admin/clubs/Show', [
            'club' => $club,
        ]);
    }

    /**
     * Show the form for editing the specified club.
     */
    public function edit(Club $club): Response
    {
        return Inertia::render('admin/clubs/Edit', [
            'club' => $club,
        ]);
    }

    /**
     * Update the specified club.
     */
    public function update(
        Request $request,
        Club $club
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:clubs,slug,' . $club->id,
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'responsible' => [
                'nullable',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'city' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'boolean',
            ],
            'remove_logo' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | SLUG
        |--------------------------------------------------------------------------
        */

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug(
                $validated['name']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE CURRENT LOGO
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean('remove_logo') &&
            $club->logo
        ) {
            $path = storage_path(
                'app/public/' . $club->logo
            );

            if (is_file($path)) {
                unlink($path);
            }

            $validated['logo'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | NEW LOGO
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            /*
            |--------------------------------------------------------------------------
            | Delete previous logo
            |--------------------------------------------------------------------------
            */

            if ($club->logo) {
                $oldPath = storage_path(
                    'app/public/' . $club->logo
                );

                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Create directory
            |--------------------------------------------------------------------------
            */

            $directory = storage_path(
                'app/public/clubs'
            );

            if (! is_dir($directory)) {
                mkdir(
                    $directory,
                    0755,
                    true
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Save new logo
            |--------------------------------------------------------------------------
            */

            $logo = $request->file('logo');

            $filename =
                uniqid() .
                '.' .
                $logo->getClientOriginalExtension();

            $logo->move(
                $directory,
                $filename
            );

            $validated['logo'] =
                "clubs/{$filename}";
        } else {
            /*
            |--------------------------------------------------------------------------
            | No new logo
            |--------------------------------------------------------------------------
            */

            unset($validated['logo']);
        }

        /*
        |--------------------------------------------------------------------------
        | Do not save helper field
        |--------------------------------------------------------------------------
        */

        unset($validated['remove_logo']);

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $club->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Club actualizado correctamente.',
        ]);

        return redirect()
            ->route('admin.clubs.index');
    }

    /**
     * Remove the specified club.
     */
    public function destroy(
        Club $club
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Delete logo
        |--------------------------------------------------------------------------
        */

        if ($club->logo) {
            $logoPath = storage_path(
                'app/public/' . $club->logo
            );

            if (is_file($logoPath)) {
                unlink($logoPath);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Delete club
        |--------------------------------------------------------------------------
        */

        $club->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Club eliminado correctamente.',
        ]);

        return redirect()
            ->route('admin.clubs.index');
    }
}
