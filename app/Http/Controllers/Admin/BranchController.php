<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($subquery) use ($search) {
                    $subquery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('neighborhood', 'like', "%{$search}%")
                        ->orWhere('postal_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/branches/Index', [
            'branches' => $branches,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/branches/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'exterior_number' => ['required', 'string', 'max:50'],
            'interior_number' => ['nullable', 'string', 'max:50'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'references' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i', 'after:opening_time'],
            'opening_hours' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'El nombre de la sucursal es obligatorio.',
            'street.required' => 'La calle es obligatoria.',
            'exterior_number.required' => 'El número exterior es obligatorio.',
            'neighborhood.required' => 'La colonia es obligatoria.',
            'postal_code.required' => 'El código postal es obligatorio.',
            'city.required' => 'La ciudad es obligatoria.',
            'state.required' => 'El estado es obligatorio.',
            'closing_time.after' => 'La hora de cierre debe ser posterior a la apertura.',
        ]);

        $validated['slug'] = $this->generateUniqueSlug($validated['name']);

        Branch::create($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sucursal creada correctamente.',
        ]);

        return to_route('admin.branches.index');
    }

    public function show(Branch $branch)
    {
        $branch->loadCount('deliveryAddresses');

        return Inertia::render('admin/branches/Show', [
            'branch' => $branch,
        ]);
    }

    public function edit(Branch $branch)
    {
        return Inertia::render('admin/branches/Edit', [
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'exterior_number' => ['required', 'string', 'max:50'],
            'interior_number' => ['nullable', 'string', 'max:50'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'references' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i', 'after:opening_time'],
            'opening_hours' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'El nombre de la sucursal es obligatorio.',
            'street.required' => 'La calle es obligatoria.',
            'exterior_number.required' => 'El número exterior es obligatorio.',
            'neighborhood.required' => 'La colonia es obligatoria.',
            'postal_code.required' => 'El código postal es obligatorio.',
            'city.required' => 'La ciudad es obligatoria.',
            'state.required' => 'El estado es obligatorio.',
            'closing_time.after' => 'La hora de cierre debe ser posterior a la apertura.',
        ]);

        if ($branch->name !== $validated['name']) {
            $validated['slug'] = $this->generateUniqueSlug(
                $validated['name'],
                $branch->id
            );
        }

        $branch->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sucursal actualizada correctamente.',
        ]);

        return to_route('admin.branches.index');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'sucursal';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Branch::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}