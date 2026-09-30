<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\RaceExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RaceExpenseController extends Controller
{
    /**
     * Mostrar los gastos de una carrera.
     */
    public function index(Race $race): Response
    {
        $expenses = $race->expenses()
            ->with('creator:id,name')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => (float) $race->expenses()
                ->where('status', '!=', 'cancelled')
                ->sum('amount'),

            'paid' => (float) $race->expenses()
                ->where('status', 'paid')
                ->sum('amount'),

            'pending' => (float) $race->expenses()
                ->where('status', 'pending')
                ->sum('amount'),

            'cancelled' => (float) $race->expenses()
                ->where('status', 'cancelled')
                ->sum('amount'),

            'count' => $race->expenses()
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];

        return Inertia::render('admin/races/expenses/Index', [
            'race' => $race,
            'expenses' => $expenses,
            'summary' => $summary,
        ]);
    }

    /**
     * Registrar un gasto.
     */
    public function store(Request $request, Race $race): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'expense_date' => ['required', 'date'],
            'status' => [
                'required',
                'in:pending,paid,cancelled',
            ],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
            'receipt' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'notes' => ['nullable', 'string'],
        ]);

        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store('race-expenses', 'public');
        }

        $race->expenses()->create([
            'created_by' => $request->user()?->id,
            'title' => $validated['title'],
            'category' => $validated['category'] ?? null,
            'supplier' => $validated['supplier'] ?? null,
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'status' => $validated['status'],
            'payment_method' => $validated['payment_method'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'paid_at' => $validated['status'] === 'paid'
                ? now()
                : null,
            'receipt_path' => $receiptPath,
            'notes' => $validated['notes'] ?? null,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Gasto registrado correctamente.',
        ]);

        return back();
    }

    /**
     * Actualizar un gasto.
     */
    public function update(
        Request $request,
        Race $race,
        RaceExpense $expense
    ): RedirectResponse {
        abort_unless($expense->race_id === $race->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'expense_date' => ['required', 'date'],
            'status' => [
                'required',
                'in:pending,paid,cancelled',
            ],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
            'receipt' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'notes' => ['nullable', 'string'],
        ]);

        $receiptPath = $expense->receipt_path;

        if ($request->hasFile('receipt')) {
            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
            }

            $receiptPath = $request->file('receipt')
                ->store('race-expenses', 'public');
        }

        $expense->update([
            'title' => $validated['title'],
            'category' => $validated['category'] ?? null,
            'supplier' => $validated['supplier'] ?? null,
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'status' => $validated['status'],
            'payment_method' => $validated['payment_method'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'paid_at' => $validated['status'] === 'paid'
                ? ($expense->paid_at ?? now())
                : null,
            'receipt_path' => $receiptPath,
            'notes' => $validated['notes'] ?? null,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Gasto actualizado correctamente.',
        ]);

        return back();
    }

    /**
     * Eliminar un gasto.
     */
    public function destroy(
        Race $race,
        RaceExpense $expense
    ): RedirectResponse {
        abort_unless($expense->race_id === $race->id, 404);

        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Gasto eliminado correctamente.',
        ]);

        return back();
    }
}
