<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use App\Models\Race;
use App\Models\RaceChecklistItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RaceChecklistController extends Controller
{
    /**
     * Mostrar checklist de una carrera.
     */
    public function index(Race $race): Response
    {
        $race->load([
            'checklistItems' => function ($query) {
                $query
                    ->with(['assignedUser:id,name'])
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
        ]);

        $templates = ChecklistTemplate::query()
            ->where('is_active', true)
            ->with([
                'items' => function ($query) {
                    $query
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/races/checklist/Index', [
            'race' => $race,
            'checklistItems' => $race->checklistItems,
            'templates' => $templates,
            'users' => $users,
        ]);
    }

    /**
     * Crear una tarea manualmente.
     */
    public function store(
        Request $request,
        Race $race
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'category' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'required',
                'in:pending,in_progress,completed,not_applicable',
            ],
            'due_date' => [
                'nullable',
                'date',
            ],
            'assigned_to' => [
                'nullable',
                'exists:users,id',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        RaceChecklistItem::create([
            'race_id' => $race->id,
            'checklist_template_item_id' => null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'completed_at' => $validated['completed_at'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Elemento agregado al checklist correctamente.',
        ]);

        return back();
    }

    /**
     * Actualizar una tarea.
     */
    public function update(
        Request $request,
        Race $race,
        RaceChecklistItem $checklistItem
    ): RedirectResponse {
        abort_unless(
            $checklistItem->race_id === $race->id,
            404
        );

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'category' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'required',
                'in:pending,in_progress,completed,not_applicable',
            ],
            'due_date' => [
                'nullable',
                'date',
            ],
            'assigned_to' => [
                'nullable',
                'exists:users,id',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $validated['completed_at'] =
            $validated['status'] === 'completed'
                ? ($checklistItem->completed_at ?? now())
                : null;

        $checklistItem->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'completed_at' => $validated['completed_at'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Elemento del checklist actualizado correctamente.',
        ]);

        return back();
    }

    /**
     * Cambiar solamente el estado.
     */
    public function updateStatus(
        Request $request,
        Race $race,
        RaceChecklistItem $checklistItem
    ): RedirectResponse {
        abort_unless(
            $checklistItem->race_id === $race->id,
            404
        );

        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,in_progress,completed,not_applicable',
            ],
        ]);

        $checklistItem->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed'
                ? ($checklistItem->completed_at ?? now())
                : null,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Estado actualizado correctamente.',
        ]);

        return back();
    }

    /**
     * Eliminar un elemento del checklist.
     */
    public function destroy(
        Race $race,
        RaceChecklistItem $checklistItem
    ): RedirectResponse {
        abort_unless(
            $checklistItem->race_id === $race->id,
            404
        );

        $checklistItem->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Elemento eliminado del checklist.',
        ]);

        return back();
    }

    /**
     * Crear el checklist de una carrera a partir de una plantilla.
     */
    public function applyTemplate(
        Request $request,
        Race $race
    ): RedirectResponse {
        $validated = $request->validate([
            'template_id' => [
                'required',
                'exists:checklist_templates,id',
            ],
            'replace_existing' => [
                'nullable',
                'boolean',
            ],
        ]);

        $template = ChecklistTemplate::query()
            ->where('is_active', true)
            ->with([
                'items' => function ($query) {
                    $query
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->findOrFail($validated['template_id']);

        DB::transaction(function () use (
            $race,
            $template,
            $validated
        ) {
            if ($validated['replace_existing'] ?? false) {
                $race->checklistItems()->delete();
            }

            $existingTitles = $race->checklistItems()
                ->pluck('title')
                ->map(fn ($title) => mb_strtolower($title))
                ->toArray();

            foreach ($template->items as $item) {
                /**
                 * Evitamos duplicar tareas si ya existen.
                 */
                if (
                    in_array(
                        mb_strtolower($item->title),
                        $existingTitles,
                        true
                    )
                ) {
                    continue;
                }

                $race->checklistItems()->create([
                    'checklist_template_item_id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'category' => $item->category,
                    'status' => 'pending',
                    'sort_order' => $item->sort_order,
                ]);
            }
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Plantilla aplicada al checklist de la carrera.',
        ]);

        return back();
    }
}
