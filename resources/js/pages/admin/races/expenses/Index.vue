<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    Check,
    CalendarDays,
    CheckCircle2,
    Clock,
    Edit,
    Plus,
    Receipt,
    Search,
    Trash2,
    Wallet,
    X,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

interface Race {
    id: number;
    name: string;
    event_date: string | null;
}

interface ExpenseCreator {
    id: number;
    name: string;
}

interface Expense {
    id: number;
    race_id: number;
    created_by: number | null;
    title: string;
    category: string | null;
    supplier: string | null;
    description: string | null;
    amount: string | number;
    expense_date: string;
    status: 'pending' | 'paid' | 'cancelled';
    payment_method: string | null;
    reference: string | null;
    paid_at: string | null;
    notes: string | null;
    creator: ExpenseCreator | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface ExpensesPagination {
    data: Expense[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface ExpenseSummary {
    total: number;
    paid: number;
    pending: number;
    cancelled: number;
    count: number;
}

const props = defineProps<{
    race: Race;
    expenses: ExpensesPagination;
    summary: ExpenseSummary;
}>();

const showModal = ref(false);
const editingExpense = ref<Expense | null>(null);
const search = ref('');

const form = useForm({
    title: '',
    category: '',
    supplier: '',
    description: '',
    amount: '',
    expense_date: new Date().toISOString().substring(0, 10),
    status: 'pending' as 'pending' | 'paid' | 'cancelled',
    payment_method: '',
    reference: '',
    notes: '',
});

const currencyFormatter = new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
});

const formatCurrency = (amount: number | string): string => {
    return currencyFormatter.format(Number(amount) || 0);
};

const formatDate = (date: string | null): string => {
    if (!date) {
        return 'Sin fecha';
    }

    const value = String(date).substring(0, 10);
    const parts = value.split('-');

    if (parts.length !== 3) {
        return 'Sin fecha';
    }

    const [year, month, day] = parts;

    if (!year || !month || !day) {
        return 'Sin fecha';
    }

    return `${day}/${month}/${year}`;
};

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        paid: 'Pagado',
        cancelled: 'Cancelado',
    };

    return labels[status] ?? status;
};

const getStatusClass = (status: string): string => {
    const classes: Record<string, string> = {
        pending: 'status-pending',
        paid: 'status-paid',
        cancelled: 'status-cancelled',
    };

    return classes[status] ?? 'status-pending';
};

const filteredExpenses = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.expenses.data;
    }

    return props.expenses.data.filter((expense) => {
        return [
            expense.title,
            expense.category,
            expense.supplier,
            expense.reference,
            getStatusLabel(expense.status),
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term));
    });
});

const resetForm = (): void => {
    form.reset();
    form.clearErrors();
    form.expense_date = new Date().toISOString().substring(0, 10);
    form.status = 'pending';
};

const openCreateModal = (): void => {
    editingExpense.value = null;
    resetForm();
    showModal.value = true;
};

const openEditModal = (expense: Expense): void => {
    editingExpense.value = expense;
    form.clearErrors();

    form.title = expense.title;
    form.category = expense.category ?? '';
    form.supplier = expense.supplier ?? '';
    form.description = expense.description ?? '';
    form.amount = String(expense.amount);
    form.expense_date = String(expense.expense_date).substring(0, 10);
    form.status = expense.status;
    form.payment_method = expense.payment_method ?? '';
    form.reference = expense.reference ?? '';
    form.notes = expense.notes ?? '';

    showModal.value = true;
};

const closeModal = (): void => {
    if (form.processing) {
        return;
    }

    showModal.value = false;
    editingExpense.value = null;
    resetForm();
};

const submitForm = (): void => {
    if (editingExpense.value) {
        form.put(
            admin.races.expenses.update({
                race: props.race.id,
                expense: editingExpense.value.id,
            }).url,
            {
                preserveScroll: true,
                onSuccess: () => {
                    showModal.value = false;
                    editingExpense.value = null;
                    resetForm();
                },
            },
        );

        return;
    }

    form.post(
        admin.races.expenses.store({
            race: props.race.id,
        }).url,
        {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                resetForm();
            },
        },
    );
};

const deleteExpense = async (expense: Expense): Promise<void> => {
    const result = await Swal.fire({
        title: '¿Eliminar gasto?',
        text: `Se eliminará el gasto "${expense.title}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    router.delete(
        admin.races.expenses.destroy({
            race: props.race.id,
            expense: expense.id,
        }).url,
        {
            preserveScroll: true,
        },
    );
};

const goToPage = (url: string | null): void => {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Carreras',
                href: admin.races.index(),
            },
            {
                title: 'Gastos',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Gastos - ${race.name}`" />

    <div class="admin-page">
        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Control financiero
                </p>

                <h1 class="admin-page-title">
                    Gastos de la carrera
                </h1>

                <p class="admin-page-subtitle">
                    Administra los egresos y pagos de
                    <strong>{{ race.name }}</strong>.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.races.index().url"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft
                        :size="14"
                        :stroke-width="2"
                    />

                    Regresar
                </Link>

                <button
                    type="button"
                    class="admin-btn admin-btn-primary"
                    @click="openCreateModal"
                >
                    <Plus
                        :size="15"
                        :stroke-width="2.2"
                    />

                    Registrar gasto
                </button>
            </div>
        </header>

        <!-- Resumen financiero -->
        <section class="expense-summary-grid">
            <article class="expense-summary-card summary-total">
                <div class="summary-card-top">
                    <span class="summary-icon">
                        <Wallet :size="20" />
                    </span>

                    <span class="summary-label">
                        Total de gastos
                    </span>
                </div>

                <strong class="summary-amount">
                    {{ formatCurrency(summary.total) }}
                </strong>

                <span class="summary-description">
                    {{ summary.count }} gastos registrados
                </span>
            </article>

            <article class="expense-summary-card summary-paid">
                <div class="summary-card-top">
                    <span class="summary-icon">
                        <CheckCircle2 :size="20" />
                    </span>

                    <span class="summary-label">
                        Pagados
                    </span>
                </div>

                <strong class="summary-amount">
                    {{ formatCurrency(summary.paid) }}
                </strong>

                <span class="summary-description">
                    Gastos liquidados
                </span>
            </article>

            <article class="expense-summary-card summary-pending">
                <div class="summary-card-top">
                    <span class="summary-icon">
                        <Clock :size="20" />
                    </span>

                    <span class="summary-label">
                        Pendientes
                    </span>
                </div>

                <strong class="summary-amount">
                    {{ formatCurrency(summary.pending) }}
                </strong>

                <span class="summary-description">
                    Por pagar
                </span>
            </article>

            <article class="expense-summary-card summary-cancelled">
                <div class="summary-card-top">
                    <span class="summary-icon">
                        <X :size="20" />
                    </span>

                    <span class="summary-label">
                        Cancelados
                    </span>
                </div>

                <strong class="summary-amount">
                    {{ formatCurrency(summary.cancelled) }}
                </strong>

                <span class="summary-description">
                    Gastos cancelados
                </span>
            </article>
        </section>

        <!-- Tabla de gastos -->
        <section class="admin-table-card">
            <div class="admin-table-toolbar">
                <div class="expense-toolbar-title">
                    <h2>
                        Registro de gastos
                    </h2>

                    <p>
                        Consulta y administra los egresos de esta carrera.
                    </p>
                </div>

                <div class="admin-search-wrapper expense-search">
                    <Search
                        class="admin-search-icon"
                        :size="16"
                        :stroke-width="2"
                    />

                    <input
                        v-model="search"
                        type="search"
                        placeholder="Buscar gasto..."
                        class="admin-search-input"
                    />
                </div>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table expense-table">
                    <thead>
                        <tr>
                            <th>
                                Concepto
                            </th>

                            <th>
                                Categoría
                            </th>

                            <th>
                                Proveedor
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th class="text-right">
                                Importe
                            </th>

                            <th>
                                Estado
                            </th>

                            <th class="text-right">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="expense in filteredExpenses"
                            :key="expense.id"
                        >
                            <td>
                                <div class="expense-title-cell">
                                    <strong>
                                        {{ expense.title }}
                                    </strong>

                                    <span v-if="expense.reference">
                                        Ref. {{ expense.reference }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="expense-category">
                                    {{ expense.category || 'Sin categoría' }}
                                </span>
                            </td>

                            <td>
                                <span class="expense-supplier">
                                    {{ expense.supplier || 'Sin proveedor' }}
                                </span>
                            </td>

                            <td>
                                <div class="expense-date-cell">
                                    <CalendarDays
                                        :size="14"
                                        :stroke-width="2"
                                    />

                                    {{ formatDate(expense.expense_date) }}
                                </div>
                            </td>

                            <td class="text-right">
                                <strong class="expense-amount">
                                    {{ formatCurrency(expense.amount) }}
                                </strong>
                            </td>

                            <td>
                                <span
                                    class="status-badge"
                                    :class="getStatusClass(expense.status)"
                                >
                                    <span class="status-dot"></span>

                                    {{ getStatusLabel(expense.status) }}
                                </span>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <button
                                        type="button"
                                        class="action-btn action-btn-edit"
                                        title="Editar gasto"
                                        aria-label="Editar gasto"
                                        @click="openEditModal(expense)"
                                    >
                                        <Edit
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Editar
                                    </button>

                                    <button
                                        type="button"
                                        class="action-btn action-btn-delete"
                                        title="Eliminar gasto"
                                        aria-label="Eliminar gasto"
                                        @click="deleteExpense(expense)"
                                    >
                                        <Trash2
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="filteredExpenses.length === 0">
                            <td
                                colspan="7"
                                class="admin-table-empty"
                            >
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <Receipt
                                            :size="20"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <strong>
                                        {{
                                            search
                                                ? 'No se encontraron gastos'
                                                : 'No hay gastos registrados'
                                        }}
                                    </strong>

                                    <span>
                                        {{
                                            search
                                                ? 'Intenta cambiar el término de búsqueda.'
                                                : 'Registra el primer gasto de esta carrera.'
                                        }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div
                v-if="expenses.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando

                    <strong>
                        {{ expenses.from ?? 0 }}
                    </strong>

                    a

                    <strong>
                        {{ expenses.to ?? 0 }}
                    </strong>

                    de

                    <strong>
                        {{ expenses.total }}
                    </strong>
                </div>

                <nav class="admin-pagination">
                    <template
                        v-for="(link, index) in expenses.links"
                        :key="index"
                    >
                        <button
                            v-if="link.url"
                            type="button"
                            class="pagination-btn"
                            :class="{ active: link.active }"
                            @click="goToPage(link.url)"
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="pagination-btn disabled"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </section>

        <!-- Modal de registro y edición -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="expense-modal-overlay"
                @click.self="closeModal"
            >
                <section
                    class="expense-modal"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="expense-modal-title"
                >
                    <header class="expense-modal-header">
                        <div>
                            <p class="admin-page-eyebrow">
                                Control financiero
                            </p>

                            <h2 id="expense-modal-title">
                                {{
                                    editingExpense
                                        ? 'Editar gasto'
                                        : 'Registrar gasto'
                                }}
                            </h2>

                            <p>
                                {{ race.name }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="expense-modal-close"
                            aria-label="Cerrar modal"
                            :disabled="form.processing"
                            @click="closeModal"
                        >
                            <X :size="20" />
                        </button>
                    </header>

                    <form
                        class="expense-form"
                        @submit.prevent="submitForm"
                    >
                        <div class="expense-form-grid">
                            <div class="expense-form-field field-full">
                                <label for="expense-title">
                                    Concepto *
                                </label>

                                <input
                                    id="expense-title"
                                    v-model="form.title"
                                    type="text"
                                    placeholder="Ej. Renta de sonido"
                                    maxlength="255"
                                    required
                                />

                                <span
                                    v-if="form.errors.title"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.title }}
                                </span>
                            </div>

                            <div class="expense-form-field">
                                <label for="expense-category">
                                    Categoría
                                </label>

                                <input
                                    id="expense-category"
                                    v-model="form.category"
                                    type="text"
                                    placeholder="Ej. Logística"
                                    maxlength="100"
                                />

                                <span
                                    v-if="form.errors.category"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.category }}
                                </span>
                            </div>

                            <div class="expense-form-field">
                                <label for="expense-supplier">
                                    Proveedor
                                </label>

                                <input
                                    id="expense-supplier"
                                    v-model="form.supplier"
                                    type="text"
                                    placeholder="Nombre del proveedor"
                                    maxlength="255"
                                />

                                <span
                                    v-if="form.errors.supplier"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.supplier }}
                                </span>
                            </div>

                            <div class="expense-form-field">
                                <label for="expense-amount">
                                    Importe (MXN) *
                                </label>

                                <input
                                    id="expense-amount"
                                    v-model="form.amount"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="0.00"
                                    required
                                />

                                <span
                                    v-if="form.errors.amount"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.amount }}
                                </span>
                            </div>

                            <div class="expense-form-field">
                                <label for="expense-date">
                                    Fecha del gasto *
                                </label>

                                <input
                                    id="expense-date"
                                    v-model="form.expense_date"
                                    type="date"
                                    required
                                />

                                <span
                                    v-if="form.errors.expense_date"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.expense_date }}
                                </span>
                            </div>

                            <div class="expense-form-field">
                                <label for="expense-status">
                                    Estado *
                                </label>

                                <select
                                    id="expense-status"
                                    v-model="form.status"
                                    required
                                >
                                    <option value="pending">
                                        Pendiente
                                    </option>

                                    <option value="paid">
                                        Pagado
                                    </option>

                                    <option value="cancelled">
                                        Cancelado
                                    </option>
                                </select>

                                <span
                                    v-if="form.errors.status"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.status }}
                                </span>
                            </div>

                            <div class="expense-form-field">
                                <label for="expense-payment-method">
                                    Método de pago
                                </label>

                                <select
                                    id="expense-payment-method"
                                    v-model="form.payment_method"
                                >
                                    <option value="">
                                        Seleccionar método
                                    </option>

                                    <option value="Efectivo">
                                        Efectivo
                                    </option>

                                    <option value="Transferencia">
                                        Transferencia
                                    </option>

                                    <option value="Tarjeta">
                                        Tarjeta
                                    </option>

                                    <option value="Cheque">
                                        Cheque
                                    </option>

                                    <option value="Otro">
                                        Otro
                                    </option>
                                </select>

                                <span
                                    v-if="form.errors.payment_method"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.payment_method }}
                                </span>
                            </div>

                            <div class="expense-form-field field-full">
                                <label for="expense-reference">
                                    Referencia
                                </label>

                                <input
                                    id="expense-reference"
                                    v-model="form.reference"
                                    type="text"
                                    placeholder="Número de transferencia o referencia"
                                    maxlength="255"
                                />

                                <span
                                    v-if="form.errors.reference"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.reference }}
                                </span>
                            </div>

                            <div class="expense-form-field field-full">
                                <label for="expense-description">
                                    Descripción
                                </label>

                                <textarea
                                    id="expense-description"
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="Detalles adicionales del gasto..."
                                />

                                <span
                                    v-if="form.errors.description"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.description }}
                                </span>
                            </div>

                            <div class="expense-form-field field-full">
                                <label for="expense-notes">
                                    Observaciones
                                </label>

                                <textarea
                                    id="expense-notes"
                                    v-model="form.notes"
                                    rows="3"
                                    placeholder="Notas internas..."
                                />

                                <span
                                    v-if="form.errors.notes"
                                    class="expense-field-error"
                                >
                                    {{ form.errors.notes }}
                                </span>
                            </div>
                        </div>

                        <footer class="expense-modal-footer">
                            <button
                                type="button"
                                class="admin-btn admin-btn-secondary"
                                :disabled="form.processing"
                                @click="closeModal"
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="admin-btn admin-btn-primary"
                                :disabled="form.processing"
                            >
                                <Check
                                    v-if="!form.processing"
                                    :size="14"
                                    :stroke-width="2"
                                />

                                {{
                                    form.processing
                                        ? 'Guardando...'
                                        : editingExpense
                                            ? 'Guardar cambios'
                                            : 'Registrar gasto'
                                }}
                            </button>
                        </footer>
                    </form>
                </section>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
/* Resumen financiero */
.expense-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.expense-summary-card {
    min-width: 0;
    padding: 21px;
    border: 1px solid #e5edf4;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 4px 16px rgb(23 43 77 / 4%);
}

.summary-card-top {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 18px;
}

.summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    border-radius: 12px;
}

.summary-label {
    color: #718096;
    font-size: 12px;
    font-weight: 600;
}

.summary-amount {
    display: block;
    overflow: hidden;
    color: var(--sc-text, #172b4d);
    font-size: clamp(18px, 1.7vw, 25px);
    font-weight: 750;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.summary-description {
    display: block;
    margin-top: 8px;
    color: #91a0ad;
    font-size: 11px;
}

.summary-total .summary-icon {
    background: #eaf6fc;
    color: #249edb;
}

.summary-paid .summary-icon {
    background: #e8f8f1;
    color: #18a67e;
}

.summary-pending .summary-icon {
    background: #fff5df;
    color: #c58b23;
}

.summary-cancelled .summary-icon {
    background: #fff0f0;
    color: #d75b66;
}

/* Encabezado de tabla */
.expense-toolbar-title h2 {
    margin: 0;
    color: var(--sc-text, #172b4d);
    font-size: 16px;
    font-weight: 700;
}

.expense-toolbar-title p {
    margin: 6px 0 0;
    color: #8291a0;
    font-size: 12px;
}

.expense-search {
    width: 270px;
    max-width: 100%;
}

/* Tabla */
.expense-table {
    min-width: 1000px;
}

.expense-title-cell {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 150px;
    max-width: 240px;
}

.expense-title-cell strong {
    overflow: hidden;
    color: var(--sc-text, #172b4d);
    font-size: 13px;
    font-weight: 650;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.expense-title-cell span {
    color: #8b9aa8;
    font-size: 11px;
}

.expense-category,
.expense-supplier {
    color: #64778a;
    font-size: 12px;
}

.expense-date-cell {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #64778a;
    font-size: 12px;
    white-space: nowrap;
}

.expense-date-cell svg {
    color: #8aa7b9;
}

.expense-amount {
    color: var(--sc-text, #172b4d);
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.status-pending {
    border-color: #f0dfb9;
    background: #fff8e9;
    color: #a77a23;
}

.status-paid {
    border-color: #cce8da;
    background: #effaf4;
    color: #32845e;
}

.status-cancelled {
    border-color: #f0d5d8;
    background: #fff4f5;
    color: #a6656d;
}

/* Modal */
.expense-modal-overlay {
    position: fixed;
    z-index: 1000;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    padding: 24px;
    background: rgb(18 35 55 / 55%);
    backdrop-filter: blur(3px);
}

.expense-modal {
    width: 100%;
    max-width: 760px;
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    border: 1px solid #e5edf4;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 20px 60px rgb(0 0 0 / 18%);
}

.expense-modal-header {
    position: sticky;
    z-index: 2;
    top: 0;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 24px 28px;
    border-bottom: 1px solid #edf1f5;
    background: #fff;
}

.expense-modal-header h2 {
    margin: 6px 0 0;
    color: var(--sc-text, #172b4d);
    font-size: 21px;
    font-weight: 750;
}

.expense-modal-header p:last-child {
    margin: 7px 0 0;
    color: #8291a0;
    font-size: 12px;
}

.expense-modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    border: 1px solid #e3eaf0;
    border-radius: 10px;
    background: #f8fafc;
    color: #718096;
    cursor: pointer;
    transition: 0.2s ease;
}

.expense-modal-close:hover {
    border-color: #d5e4ee;
    background: #eaf6fc;
    color: #249edb;
}

.expense-modal-close:disabled {
    cursor: wait;
    opacity: 0.6;
}

.expense-form {
    padding: 24px 28px 28px;
}

.expense-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.expense-form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
}

.field-full {
    grid-column: 1 / -1;
}

.expense-form-field label {
    color: #40536a;
    font-size: 12px;
    font-weight: 650;
}

.expense-form-field input,
.expense-form-field select,
.expense-form-field textarea {
    width: 100%;
    min-height: 42px;
    padding: 10px 12px;
    border: 1px solid #dce5ed;
    border-radius: 9px;
    outline: none;
    background: #fff;
    color: var(--sc-text, #172b4d);
    font: inherit;
    font-size: 13px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.expense-form-field textarea {
    min-height: 90px;
    resize: vertical;
}

.expense-form-field input:focus,
.expense-form-field select:focus,
.expense-form-field textarea:focus {
    border-color: #249edb;
    box-shadow: 0 0 0 3px rgb(36 158 219 / 10%);
}

.expense-field-error {
    color: #e83e4d;
    font-size: 11px;
    line-height: 1.4;
}

.expense-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid #edf1f5;
}

.expense-modal-footer button:disabled {
    cursor: wait;
    opacity: 0.7;
}

/* Responsive */
@media (max-width: 1100px) {
    .expense-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .expense-summary-grid {
        gap: 12px;
    }

    .expense-summary-card {
        padding: 16px;
    }

    .summary-card-top {
        align-items: flex-start;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 14px;
    }

    .summary-amount {
        font-size: 19px;
    }

    .expense-toolbar-title {
        width: 100%;
    }

    .expense-search {
        width: 100%;
    }

    .expense-modal-overlay {
        align-items: flex-start;
        padding: 12px;
    }

    .expense-modal {
        max-height: calc(100vh - 24px);
        border-radius: 14px;
    }

    .expense-modal-header,
    .expense-form {
        padding: 20px;
    }

    .expense-form-grid {
        grid-template-columns: 1fr;
        gap: 17px;
    }

    .field-full {
        grid-column: auto;
    }

    .expense-modal-footer {
        flex-wrap: wrap;
    }
}

@media (max-width: 420px) {
    .expense-summary-grid {
        grid-template-columns: 1fr;
    }
}
</style>