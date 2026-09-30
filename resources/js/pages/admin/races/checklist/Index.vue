<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    Check,
    CheckCircle2,
    ClipboardCheck,
    Clock3,
    Edit,
    FileText,
    ListChecks,
    Plus,
    RefreshCw,
    Search,
    Tag,
    Trash2,
    UserRound,
    Users,
    X,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';

import admin from '@/routes/admin';

interface Race {
    id: number;
    name: string;
    slug: string;
    event_date: string | null;
    location: string | null;
    status: string;
}

interface AssignedUser {
    id: number;
    name: string;
}

interface ChecklistItem {
    id: number;
    race_id: number;
    title: string;
    description: string | null;
    category: string | null;
    status: string;
    due_date: string | null;
    assigned_to: number | null;
    notes: string | null;
    sort_order: number;
    completed_at: string | null;
    assignedUser?: AssignedUser | null;
}

interface ChecklistTemplateItem {
    id: number;
    title: string;
    description: string | null;
    category: string | null;
    sort_order: number;
    default_status?: string | null;
}

interface ChecklistTemplate {
    id: number;
    name: string;
    description: string | null;
    items?: ChecklistTemplateItem[];
}

interface User {
    id: number;
    name: string;
}

const props = defineProps<{
    race: Race;
    checklistItems: ChecklistItem[];
    templates: ChecklistTemplate[];
    users: User[];
}>();

const showForm = ref(false);
const showTemplateForm = ref(false);
const editingItem = ref<ChecklistItem | null>(null);
const search = ref('');
const statusFilter = ref('');
const categoryFilter = ref('');

const form = useForm({
    title: '',
    description: '',
    category: '',
    status: 'pending',
    due_date: '',
    assigned_to: '',
    notes: '',
    sort_order: 0,
});

const statusForm = useForm({
    status: '',
});

const templateForm = useForm({
    template_id: '',
    replace_existing: false,
});

const statusLabels: Record<string, string> = {
    pending: 'Pendiente',
    in_progress: 'En proceso',
    completed: 'Completada',
    not_applicable: 'No aplica',
};

const statusClasses: Record<string, string> = {
    pending: 'status-pending',
    in_progress: 'status-progress',
    completed: 'status-completed',
    not_applicable: 'status-na',
};

const categories = computed(() => {
    const values = props.checklistItems
        .map((item) => item.category)
        .filter(
            (category): category is string =>
                Boolean(category && category.trim()),
        );

    return [...new Set(values)].sort((a, b) =>
        a.localeCompare(b),
    );
});

const filteredItems = computed(() => {
    const query = search.value.trim().toLowerCase();

    return [...props.checklistItems]
        .filter((item) => {
            if (
                query &&
                ![
                    item.title,
                    item.description,
                    item.category,
                    item.notes,
                    item.assignedUser?.name,
                ]
                    .filter(Boolean)
                    .some((value) =>
                        String(value)
                            .toLowerCase()
                            .includes(query),
                    )
            ) {
                return false;
            }

            if (
                statusFilter.value &&
                item.status !== statusFilter.value
            ) {
                return false;
            }

            if (
                categoryFilter.value &&
                item.category !== categoryFilter.value
            ) {
                return false;
            }

            return true;
        })
        .sort((a, b) => {
            if (a.sort_order !== b.sort_order) {
                return a.sort_order - b.sort_order;
            }

            return a.id - b.id;
        });
});

const totalItems = computed(
    () => props.checklistItems.length,
);

const pendingItems = computed(
    () =>
        props.checklistItems.filter(
            (item) => item.status === 'pending',
        ).length,
);

const progressItems = computed(
    () =>
        props.checklistItems.filter(
            (item) => item.status === 'in_progress',
        ).length,
);

const completedItems = computed(
    () =>
        props.checklistItems.filter(
            (item) => item.status === 'completed',
        ).length,
);

const notApplicableItems = computed(
    () =>
        props.checklistItems.filter(
            (item) => item.status === 'not_applicable',
        ).length,
);

const completionPercentage = computed(() => {
    if (totalItems.value === 0) {
        return 0;
    }

    return Math.round(
        (completedItems.value / totalItems.value) * 100,
    );
});

const formatDate = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return 'Sin fecha';
    }

    const normalized = String(value).substring(0, 10);
    const parts = normalized.split('-');

    if (parts.length !== 3) {
        return 'Sin fecha';
    }

    const [year, month, day] = parts;

    if (!year || !month || !day) {
        return 'Sin fecha';
    }

    return `${day}/${month}/${year}`;
};

const formatDateForInput = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return '';
    }

    return String(value).substring(0, 10);
};

const isOverdue = (
    item: ChecklistItem,
): boolean => {
    if (
        !item.due_date ||
        item.status === 'completed' ||
        item.status === 'not_applicable'
    ) {
        return false;
    }

    const due = new Date(
        `${String(item.due_date).substring(0, 10)}T23:59:59`,
    );

    return due.getTime() < Date.now();
};

const getStatusLabel = (
    status: string,
): string => {
    return statusLabels[status] ?? status;
};

const getStatusClass = (
    status: string,
): string => {
    return statusClasses[status] ?? 'status-pending';
};

const resetForm = (): void => {
    form.reset();

    form.title = '';
    form.description = '';
    form.category = '';
    form.status = 'pending';
    form.due_date = '';
    form.assigned_to = '';
    form.notes = '';
    form.sort_order = props.checklistItems.length;
};

const openCreate = (): void => {
    editingItem.value = null;

    resetForm();

    showForm.value = true;
};

const openEdit = (
    item: ChecklistItem,
): void => {
    editingItem.value = item;

    form.title = item.title;
    form.description = item.description ?? '';
    form.category = item.category ?? '';
    form.status = item.status;
    form.due_date = formatDateForInput(item.due_date);
    form.assigned_to = item.assigned_to
        ? String(item.assigned_to)
        : '';
    form.notes = item.notes ?? '';
    form.sort_order = item.sort_order;

    showForm.value = true;
};

const closeForm = (): void => {
    if (form.processing) {
        return;
    }

    showForm.value = false;
    editingItem.value = null;
    form.clearErrors();
};

const submit = (): void => {
    if (!form.title.trim()) {
        Swal.fire({
            icon: 'warning',
            title: 'Falta información',
            text: 'Debes indicar el nombre de la tarea.',
            confirmButtonColor: '#249edb',
        });

        return;
    }

    if (editingItem.value) {
        form.put(
            admin.races.checklist.update({
                race: props.race.id,
                checklistItem: editingItem.value.id,
            }).url,
            {
                preserveScroll: true,
                onSuccess: () => {
                    showForm.value = false;
                    editingItem.value = null;
                },
            },
        );

        return;
    }

    form.post(
        admin.races.checklist.store({
            race: props.race.id,
        }).url,
        {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false;
                editingItem.value = null;
            },
        },
    );
};

const updateStatus = (
    item: ChecklistItem,
    status: string,
): void => {
    if (
        status === item.status ||
        statusForm.processing
    ) {
        return;
    }

    statusForm.status = status;

    statusForm.patch(
        admin.races.checklist.status({
            race: props.race.id,
            checklistItem: item.id,
        }).url,
        {
            preserveScroll: true,
        },
    );
};

const deleteItem = (
    item: ChecklistItem,
): void => {
    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar tarea?',
        text: `Se eliminará "${item.title}" del checklist.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e83e4d',
        cancelButtonColor: '#6f8491',
        reverseButtons: true,
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        useForm({}).delete(
            admin.races.checklist.destroy({
                race: props.race.id,
                checklistItem: item.id,
            }).url,
            {
                preserveScroll: true,
            },
        );
    });
};

const openTemplateForm = (): void => {
    templateForm.reset();

    templateForm.template_id = '';
    templateForm.replace_existing = false;

    showTemplateForm.value = true;
};

const closeTemplateForm = (): void => {
    if (templateForm.processing) {
        return;
    }

    showTemplateForm.value = false;
    templateForm.clearErrors();
};

const applyTemplate = (): void => {
    if (!templateForm.template_id) {
        Swal.fire({
            icon: 'warning',
            title: 'Selecciona una plantilla',
            text: 'Debes seleccionar una plantilla antes de continuar.',
            confirmButtonColor: '#249edb',
        });

        return;
    }

    templateForm.post(
        admin.races.checklist['apply-template']({
            race: props.race.id,
        }).url,
        {
            preserveScroll: true,
            onSuccess: () => {
                showTemplateForm.value = false;
            },
        },
    );
};

const clearFilters = (): void => {
    search.value = '';
    statusFilter.value = '';
    categoryFilter.value = '';
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
                title: 'Checklist',
                href: admin.races.index(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Checklist — ${race.name}`" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Carrera
                </p>

                <h1 class="admin-page-title">
                    Checklist
                </h1>

                <p class="admin-page-subtitle">
                    Control operativo de tareas para
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
                    class="admin-btn admin-btn-secondary"
                    @click="openTemplateForm"
                >
                    <RefreshCw
                        :size="14"
                        :stroke-width="2"
                    />

                    Aplicar plantilla
                </button>

                <button
                    type="button"
                    class="admin-btn admin-btn-primary"
                    @click="openCreate"
                >
                    <Plus
                        :size="14"
                        :stroke-width="2"
                    />

                    Nueva tarea
                </button>
            </div>
        </header>

        <!-- =================================================
             RACE INFO
        ================================================== -->

        <section class="race-checklist-info">
            <div class="race-checklist-info-icon">
                <ClipboardCheck
                    :size="18"
                    :stroke-width="2"
                />
            </div>

            <div class="race-checklist-info-main">
                <span>
                    Checklist de carrera
                </span>

                <strong>
                    {{ race.name }}
                </strong>
            </div>

            <div class="race-checklist-info-meta">
                <span
                    v-if="race.event_date"
                >
                    <CalendarDays
                        :size="13"
                        :stroke-width="2"
                    />

                    {{ formatDate(race.event_date) }}
                </span>

                <span
                    v-if="race.location"
                >
                    <Users
                        :size="13"
                        :stroke-width="2"
                    />

                    {{ race.location }}
                </span>
            </div>
        </section>

        <!-- =================================================
             SUMMARY
        ================================================== -->

        <section class="race-summary-grid">
            <div class="race-summary-card">
                <div class="race-summary-icon">
                    <ListChecks
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <span>
                        Total de tareas
                    </span>

                    <strong>
                        {{ totalItems }}
                    </strong>
                </div>
            </div>

            <div class="race-summary-card">
                <div class="race-summary-icon pending-icon">
                    <Clock3
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <span>
                        Pendientes
                    </span>

                    <strong>
                        {{ pendingItems }}
                    </strong>
                </div>
            </div>

            <div class="race-summary-card">
                <div class="race-summary-icon progress-icon">
                    <RefreshCw
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <span>
                        En proceso
                    </span>

                    <strong>
                        {{ progressItems }}
                    </strong>
                </div>
            </div>

            <div class="race-summary-card">
                <div class="race-summary-icon completed-icon">
                    <CheckCircle2
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <span>
                        Completadas
                    </span>

                    <strong>
                        {{ completedItems }}
                    </strong>
                </div>
            </div>
        </section>

        <!-- =================================================
             PROGRESS
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Avance
                    </p>

                    <h2 class="show-card-title">
                        Progreso del checklist
                    </h2>

                    <p class="show-card-description">
                        {{ completedItems }}
                        de
                        {{ totalItems }}
                        tareas completadas.
                    </p>
                </div>

                <div class="progress-percentage">
                    {{ completionPercentage }}%
                </div>
            </div>

            <div class="show-card-body progress-body">
                <div class="progress-track">
                    <div
                        class="progress-fill"
                        :style="{
                            width: `${completionPercentage}%`,
                        }"
                    ></div>
                </div>

                <div class="progress-footer">
                    <span>
                        {{ pendingItems }}
                        pendientes
                    </span>

                    <span
                        v-if="notApplicableItems > 0"
                    >
                        {{ notApplicableItems }}
                        no aplica
                    </span>
                </div>
            </div>
        </section>

        <!-- =================================================
             CHECKLIST
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Operación
                    </p>

                    <h2 class="show-card-title">
                        Tareas del checklist
                    </h2>

                    <p class="show-card-description">
                        Administra las actividades necesarias para
                        preparar y ejecutar la carrera.
                    </p>
                </div>

                <div class="show-card-icon">
                    <ClipboardCheck
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <!-- FILTERS -->

                <div class="filters-bar">
                    <div class="search-field">
                        <Search
                            :size="15"
                            :stroke-width="2"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar tarea..."
                        />
                    </div>

                    <select
                        v-model="statusFilter"
                        class="filter-select"
                    >
                        <option value="">
                            Todos los estados
                        </option>

                        <option value="pending">
                            Pendientes
                        </option>

                        <option value="in_progress">
                            En proceso
                        </option>

                        <option value="completed">
                            Completadas
                        </option>

                        <option value="not_applicable">
                            No aplica
                        </option>
                    </select>

                    <select
                        v-model="categoryFilter"
                        class="filter-select"
                    >
                        <option value="">
                            Todas las categorías
                        </option>

                        <option
                            v-for="category in categories"
                            :key="category"
                            :value="category"
                        >
                            {{ category }}
                        </option>
                    </select>

                    <button
                        v-if="
                            search ||
                            statusFilter ||
                            categoryFilter
                        "
                        type="button"
                        class="clear-filter-btn"
                        @click="clearFilters"
                    >
                        <X
                            :size="14"
                            :stroke-width="2"
                        />

                        Limpiar
                    </button>
                </div>

                <!-- LIST -->

                <div
                    v-if="filteredItems.length > 0"
                    class="checklist-list"
                >
                    <article
                        v-for="item in filteredItems"
                        :key="item.id"
                        class="checklist-item"
                        :class="{
                            'checklist-item-completed':
                                item.status === 'completed',
                        }"
                    >
                        <div class="checklist-check">
                            <Check
                                v-if="item.status === 'completed'"
                                :size="15"
                                :stroke-width="2.5"
                            />

                            <span
                                v-else
                            ></span>
                        </div>

                        <div class="checklist-content">
                            <div class="checklist-title-row">
                                <div>
                                    <h3>
                                        {{ item.title }}
                                    </h3>

                                    <div
                                        v-if="
                                            item.category ||
                                            item.assignedUser
                                        "
                                        class="checklist-meta"
                                    >
                                        <span
                                            v-if="
                                                item.category
                                            "
                                        >
                                            <Tag
                                                :size="11"
                                                :stroke-width="2"
                                            />

                                            {{ item.category }}
                                        </span>

                                        <span
                                            v-if="
                                                item.assignedUser
                                            "
                                        >
                                            <UserRound
                                                :size="11"
                                                :stroke-width="2"
                                            />

                                            {{
                                                item.assignedUser.name
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div class="checklist-actions">
                                    <button
                                        type="button"
                                        class="icon-action-btn"
                                        title="Editar"
                                        @click="openEdit(item)"
                                    >
                                        <Edit
                                            :size="14"
                                            :stroke-width="2"
                                        />
                                    </button>

                                    <button
                                        type="button"
                                        class="icon-action-btn icon-action-danger"
                                        title="Eliminar"
                                        @click="deleteItem(item)"
                                    >
                                        <Trash2
                                            :size="14"
                                            :stroke-width="2"
                                        />
                                    </button>
                                </div>
                            </div>

                            <p
                                v-if="item.description"
                                class="checklist-description"
                            >
                                {{ item.description }}
                            </p>

                            <div class="checklist-footer">
                                <div class="checklist-dates">
                                    <span
                                        v-if="item.due_date"
                                        :class="{
                                            'date-overdue':
                                                isOverdue(item),
                                        }"
                                    >
                                        <CalendarDays
                                            :size="12"
                                            :stroke-width="2"
                                        />

                                        Vence:
                                        {{
                                            formatDate(
                                                item.due_date,
                                            )
                                        }}
                                    </span>

                                    <span
                                        v-if="item.notes"
                                    >
                                        <FileText
                                            :size="12"
                                            :stroke-width="2"
                                        />

                                        Tiene notas
                                    </span>
                                </div>

                                <select
                                    :value="item.status"
                                    class="status-select"
                                    :class="
                                        getStatusClass(
                                            item.status,
                                        )
                                    "
                                    :disabled="
                                        statusForm.processing
                                    "
                                    @change="
                                        updateStatus(
                                            item,
                                            (
                                                $event.target as HTMLSelectElement
                                            ).value,
                                        )
                                    "
                                >
                                    <option value="pending">
                                        Pendiente
                                    </option>

                                    <option value="in_progress">
                                        En proceso
                                    </option>

                                    <option value="completed">
                                        Completada
                                    </option>

                                    <option value="not_applicable">
                                        No aplica
                                    </option>
                                </select>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- EMPTY -->

                <div
                    v-else
                    class="show-empty-block"
                >
                    <div class="empty-state-icon">
                        <ListChecks
                            :size="22"
                            :stroke-width="2"
                        />
                    </div>

                    <strong>
                        {{
                            totalItems === 0
                                ? 'No hay tareas en el checklist'
                                : 'No se encontraron tareas'
                        }}
                    </strong>

                    <span>
                        {{
                            totalItems === 0
                                ? 'Agrega una tarea o aplica una plantilla para comenzar.'
                                : 'Prueba cambiando los filtros de búsqueda.'
                        }}
                    </span>
                </div>
            </div>
        </section>

        <!-- =================================================
             CREATE / EDIT MODAL
        ================================================== -->

        <Teleport to="body">
            <div
                v-if="showForm"
                class="modal-backdrop"
                @click.self="closeForm"
            >
                <div class="admin-modal">
                    <div class="modal-header">
                        <div>
                            <p class="show-card-eyebrow">
                                {{
                                    editingItem
                                        ? 'Editar'
                                        : 'Nueva tarea'
                                }}
                            </p>

                            <h2>
                                {{
                                    editingItem
                                        ? 'Editar tarea'
                                        : 'Agregar tarea'
                                }}
                            </h2>
                        </div>

                        <button
                            type="button"
                            class="modal-close"
                            @click="closeForm"
                        >
                            <X
                                :size="17"
                                :stroke-width="2"
                            />
                        </button>
                    </div>

                    <form
                        class="modal-body"
                        @submit.prevent="submit"
                    >
                        <div class="form-group form-group-full">
                            <label>
                                Título
                                <span>*</span>
                            </label>

                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Ej. Confirmar ambulancia"
                            />

                            <small
                                v-if="form.errors.title"
                                class="form-error"
                            >
                                {{ form.errors.title }}
                            </small>
                        </div>

                        <div class="form-group form-group-full">
                            <label>
                                Descripción
                            </label>

                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Describe la tarea..."
                            ></textarea>

                            <small
                                v-if="form.errors.description"
                                class="form-error"
                            >
                                {{ form.errors.description }}
                            </small>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>
                                    Categoría
                                </label>

                                <input
                                    v-model="form.category"
                                    type="text"
                                    placeholder="Logística"
                                />

                                <small
                                    v-if="form.errors.category"
                                    class="form-error"
                                >
                                    {{ form.errors.category }}
                                </small>
                            </div>

                            <div class="form-group">
                                <label>
                                    Estado
                                </label>

                                <select
                                    v-model="form.status"
                                >
                                    <option value="pending">
                                        Pendiente
                                    </option>

                                    <option value="in_progress">
                                        En proceso
                                    </option>

                                    <option value="completed">
                                        Completada
                                    </option>

                                    <option value="not_applicable">
                                        No aplica
                                    </option>
                                </select>

                                <small
                                    v-if="form.errors.status"
                                    class="form-error"
                                >
                                    {{ form.errors.status }}
                                </small>
                            </div>

                            <div class="form-group">
                                <label>
                                    Fecha límite
                                </label>

                                <input
                                    v-model="form.due_date"
                                    type="date"
                                />

                                <small
                                    v-if="form.errors.due_date"
                                    class="form-error"
                                >
                                    {{ form.errors.due_date }}
                                </small>
                            </div>

                            <div class="form-group">
                                <label>
                                    Responsable
                                </label>

                                <select
                                    v-model="form.assigned_to"
                                >
                                    <option value="">
                                        Sin asignar
                                    </option>

                                    <option
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="String(user.id)"
                                    >
                                        {{ user.name }}
                                    </option>
                                </select>

                                <small
                                    v-if="
                                        form.errors.assigned_to
                                    "
                                    class="form-error"
                                >
                                    {{ form.errors.assigned_to }}
                                </small>
                            </div>

                            <div class="form-group">
                                <label>
                                    Orden
                                </label>

                                <input
                                    v-model.number="
                                        form.sort_order
                                    "
                                    type="number"
                                    min="0"
                                />

                                <small
                                    v-if="
                                        form.errors.sort_order
                                    "
                                    class="form-error"
                                >
                                    {{ form.errors.sort_order }}
                                </small>
                            </div>
                        </div>

                        <div class="form-group form-group-full">
                            <label>
                                Notas
                            </label>

                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Notas internas..."
                            ></textarea>

                            <small
                                v-if="form.errors.notes"
                                class="form-error"
                            >
                                {{ form.errors.notes }}
                            </small>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="admin-btn admin-btn-secondary"
                                :disabled="form.processing"
                                @click="closeForm"
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="admin-btn admin-btn-primary"
                                :disabled="form.processing"
                            >
                                <Check
                                    :size="14"
                                    :stroke-width="2"
                                />

                                {{
                                    form.processing
                                        ? 'Guardando...'
                                        : editingItem
                                          ? 'Guardar cambios'
                                          : 'Crear tarea'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- =================================================
             TEMPLATE MODAL
        ================================================== -->

        <Teleport to="body">
            <div
                v-if="showTemplateForm"
                class="modal-backdrop"
                @click.self="closeTemplateForm"
            >
                <div class="admin-modal admin-modal-small">
                    <div class="modal-header">
                        <div>
                            <p class="show-card-eyebrow">
                                Configuración
                            </p>

                            <h2>
                                Aplicar plantilla
                            </h2>
                        </div>

                        <button
                            type="button"
                            class="modal-close"
                            @click="closeTemplateForm"
                        >
                            <X
                                :size="17"
                                :stroke-width="2"
                            />
                        </button>
                    </div>

                    <form
                        class="modal-body"
                        @submit.prevent="applyTemplate"
                    >
                        <div class="template-info">
                            <RefreshCw
                                :size="18"
                                :stroke-width="2"
                            />

                            <div>
                                <strong>
                                    Checklist predefinido
                                </strong>

                                <span>
                                    Selecciona una plantilla para
                                    agregar sus tareas a esta carrera.
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                Plantilla
                                <span>*</span>
                            </label>

                            <select
                                v-model="
                                    templateForm.template_id
                                "
                            >
                                <option value="">
                                    Selecciona una plantilla
                                </option>

                                <option
                                    v-for="template in templates"
                                    :key="template.id"
                                    :value="String(template.id)"
                                >
                                    {{ template.name }}
                                </option>
                            </select>

                            <small
                                v-if="
                                    templateForm.errors
                                        .template_id
                                "
                                class="form-error"
                            >
                                {{
                                    templateForm.errors
                                        .template_id
                                }}
                            </small>
                        </div>

                        <div
                            v-if="
                                templateForm.template_id &&
                                templates.find(
                                    (template) =>
                                        String(template.id) ===
                                        templateForm.template_id,
                                )
                            "
                            class="template-preview"
                        >
                            <template
                                v-for="template in templates"
                                :key="template.id"
                            >
                                <div
                                    v-if="
                                        String(template.id) ===
                                        templateForm.template_id
                                    "
                                >
                                    <strong>
                                        {{ template.name }}
                                    </strong>

                                    <p
                                        v-if="
                                            template.description
                                        "
                                    >
                                        {{
                                            template.description
                                        }}
                                    </p>

                                    <span>
                                        {{
                                            template.items?.length ??
                                            0
                                        }}
                                        tareas
                                    </span>
                                </div>
                            </template>
                        </div>

                        <label class="checkbox-row">
                            <input
                                v-model="
                                    templateForm.replace_existing
                                "
                                type="checkbox"
                            />

                            <span>
                                <strong>
                                    Reemplazar checklist actual
                                </strong>

                                <small>
                                    Eliminar las tareas existentes
                                    antes de aplicar la plantilla.
                                </small>
                            </span>
                        </label>

                        <div
                            v-if="
                                templateForm.replace_existing &&
                                totalItems > 0
                            "
                            class="template-warning"
                        >
                            <FileText
                                :size="15"
                                :stroke-width="2"
                            />

                            <span>
                                Las {{ totalItems }} tareas actuales
                                serán eliminadas.
                            </span>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="admin-btn admin-btn-secondary"
                                :disabled="
                                    templateForm.processing
                                "
                                @click="closeTemplateForm"
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="admin-btn admin-btn-primary"
                                :disabled="
                                    templateForm.processing
                                "
                            >
                                <RefreshCw
                                    :size="14"
                                    :stroke-width="2"
                                />

                                {{
                                    templateForm.processing
                                        ? 'Aplicando...'
                                        : 'Aplicar plantilla'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
/* =========================================================
   PAGE
   ========================================================= */

.admin-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* =========================================================
   RACE INFO
   ========================================================= */

.race-checklist-info {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 15px 18px;
    border: 1px solid var(--sc-page-border);
    border-radius: var(--sc-page-radius);
    background: #ffffff;
    box-shadow:
        0 4px 15px rgba(27, 62, 90, 0.035),
        0 1px 3px rgba(27, 62, 90, 0.025);
}

.race-checklist-info-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    border: 1px solid #dce9ef;
    border-radius: 10px;
    background: #f5f9fb;
    color: #7592a3;
}

.race-checklist-info-main {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.race-checklist-info-main span {
    color: #91a1ac;
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.race-checklist-info-main strong {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 14px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.race-checklist-info-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 7px;
    margin-left: auto;
}

.race-checklist-info-meta span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 27px;
    padding: 0 9px;
    border: 1px solid #e0e9ed;
    border-radius: 7px;
    background: #f8fafb;
    color: #718692;
    font-size: 10px;
    font-weight: 600;
}

/* =========================================================
   SUMMARY
   ========================================================= */

.race-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.race-summary-card {
    display: flex;
    align-items: center;
    gap: 11px;
    min-height: 76px;
    padding: 14px;
    border: 1px solid var(--sc-page-border);
    border-radius: var(--sc-page-radius);
    background: #ffffff;
    box-shadow:
        0 4px 15px rgba(27, 62, 90, 0.035),
        0 1px 3px rgba(27, 62, 90, 0.025);
}

.race-summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    border: 1px solid #dce9ef;
    border-radius: 10px;
    background: #f5f9fb;
    color: #6d8a9b;
}

.race-summary-icon.pending-icon {
    border-color: #e7ddd0;
    background: #faf7f2;
    color: #927957;
}

.race-summary-icon.progress-icon {
    border-color: #d7e3ed;
    background: #f2f7fb;
    color: #5e8bad;
}

.race-summary-icon.completed-icon {
    border-color: #d7e9e0;
    background: #f2faf6;
    color: #638b79;
}

.race-summary-card > div:last-child {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 4px;
}

.race-summary-card span {
    color: #8a9aa6;
    font-size: 11px;
    font-weight: 600;
}

.race-summary-card strong {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 14px;
    font-weight: 650;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* =========================================================
   CARDS
   ========================================================= */

.admin-form-card {
    width: 100%;
    overflow: hidden;
}

/* =========================================================
   CARD HEADER
   ========================================================= */

.show-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 21px 24px;
    border-bottom: 1px solid var(--sc-page-border);
}

.show-card-eyebrow {
    margin: 0 0 5px;
    color: #91a1ac;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.show-card-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
}

.show-card-description {
    margin: 6px 0 0;
    color: #8999a4;
    font-size: 12px;
    line-height: 1.55;
}

.show-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid #dce9ef;
    border-radius: 9px;
    background: #f5f9fb;
    color: #7592a3;
}

.show-card-body {
    padding: 23px 24px;
}

/* =========================================================
   PROGRESS
   ========================================================= */

.progress-percentage {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 58px;
    height: 34px;
    padding: 0 10px;
    border: 1px solid #d7e9e0;
    border-radius: 999px;
    background: #f2faf6;
    color: #638b79;
    font-size: 13px;
    font-weight: 700;
}

.progress-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.progress-track {
    width: 100%;
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #edf1f3;
}

.progress-fill {
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(
        90deg,
        #249edb,
        #d95f91
    );
    transition: width 0.25s ease;
}

.progress-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    color: #91a1ac;
    font-size: 10px;
}

/* =========================================================
   FILTERS
   ========================================================= */

.filters-bar {
    display: grid;
    grid-template-columns: minmax(220px, 1fr) 190px 190px auto;
    gap: 10px;
    margin-bottom: 17px;
}

.search-field {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 39px;
    padding: 0 11px;
    border: 1px solid #dfe7eb;
    border-radius: 9px;
    background: #ffffff;
    color: #91a1ac;
}

.search-field:focus-within {
    border-color: #249edb;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.search-field input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    background: transparent;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 12px;
}

.search-field input::placeholder {
    color: #a0adb5;
}

.filter-select,
.status-select {
    min-height: 39px;
    padding: 0 10px;
    border: 1px solid #dfe7eb;
    border-radius: 9px;
    outline: 0;
    background: #ffffff;
    color: #647884;
    font-family: inherit;
    font-size: 11px;
    font-weight: 550;
}

.filter-select:focus {
    border-color: #249edb;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.clear-filter-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    min-height: 39px;
    padding: 0 11px;
    border: 1px solid #dfe7eb;
    border-radius: 9px;
    background: #ffffff;
    color: #718692;
    cursor: pointer;
    font-family: inherit;
    font-size: 11px;
    font-weight: 600;
}

.clear-filter-btn:hover {
    border-color: #c9d6dc;
    background: #f8fafb;
}

/* =========================================================
   CHECKLIST
   ========================================================= */

.checklist-list {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.checklist-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border: 1px solid #e5ebee;
    border-radius: 10px;
    background: #ffffff;
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.checklist-item:hover {
    border-color: #d6e3e9;
    box-shadow: 0 3px 10px rgba(27, 62, 90, 0.035);
}

.checklist-item-completed {
    background: #fbfdfc;
}

.checklist-check {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 27px;
    height: 27px;
    flex: 0 0 27px;
    margin-top: 1px;
    border: 1px solid #dce5e9;
    border-radius: 50%;
    background: #f8fafb;
    color: #ffffff;
}

.checklist-item-completed .checklist-check {
    border-color: #bcdccf;
    background: #6caa8d;
}

.checklist-check span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #aab7be;
}

.checklist-content {
    min-width: 0;
    flex: 1;
}

.checklist-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
}

.checklist-title-row h3 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.4;
}

.checklist-item-completed .checklist-title-row h3 {
    color: #71847b;
    text-decoration: line-through;
}

.checklist-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 5px;
}

.checklist-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #8d9ba4;
    font-size: 10px;
}

.checklist-description {
    margin: 8px 0 0;
    color: #7f909a;
    font-size: 11px;
    line-height: 1.6;
}

.checklist-actions {
    display: flex;
    align-items: center;
    gap: 5px;
    flex: 0 0 auto;
}

.icon-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: 1px solid #e0e8ec;
    border-radius: 8px;
    background: #ffffff;
    color: #708692;
    cursor: pointer;
}

.icon-action-btn:hover {
    border-color: #c9dbe5;
    background: #f6fafc;
    color: #249edb;
}

.icon-action-danger:hover {
    border-color: #f0d3d7;
    background: #fff5f6;
    color: #e83e4d;
}

.checklist-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 12px;
}

.checklist-dates {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.checklist-dates span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #8999a4;
    font-size: 10px;
}

.checklist-dates .date-overdue {
    color: #b15c66;
    font-weight: 650;
}

.status-select {
    min-width: 125px;
    min-height: 31px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 650;
    cursor: pointer;
}

.status-select:disabled {
    cursor: wait;
    opacity: 0.75;
}

.status-select.status-pending {
    border-color: #e7ddd0;
    background: #faf7f2;
    color: #927957;
}

.status-select.status-progress {
    border-color: #d7e3ed;
    background: #f2f7fb;
    color: #5e8bad;
}

.status-select.status-completed {
    border-color: #c7e8d9;
    background: #f0faf5;
    color: #438665;
}

.status-select.status-na {
    border-color: #dce3e7;
    background: #f5f7f8;
    color: #74828b;
}

/* =========================================================
   EMPTY
   ========================================================= */

.show-empty-block {
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    padding: 35px 20px;
    color: #93a1a9;
    text-align: center;
}

.show-empty-block strong {
    color: var(--sc-page-text);
    font-size: 13px;
}

.show-empty-block span {
    max-width: 420px;
    font-size: 11px;
    line-height: 1.5;
}

.empty-action {
    margin-top: 8px;
}

/* =========================================================
   MODAL
   ========================================================= */

.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background:rgb(18 35 55 / 55%);
    backdrop-filter: blur(3px);
}

.admin-modal {
    width: min(680px, 100%);
    max-height: calc(100vh - 40px);
    overflow: auto;
    border: 1px solid #dce5e9;
    border-radius: 16px;
    background: #ffffff;
    box-shadow:
        0 25px 60px rgba(23, 43, 61, 0.18),
        0 5px 15px rgba(23, 43, 61, 0.08);
}

.admin-modal-small {
    width: min(520px, 100%);
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    padding: 21px 24px;
    border-bottom: 1px solid var(--sc-page-border);
}

.modal-header h2 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 17px;
    font-weight: 700;
}

.modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    border: 1px solid #dfe7eb;
    border-radius: 8px;
    background: #ffffff;
    color: #718692;
    cursor: pointer;
}

.modal-close:hover {
    border-color: #cbd9df;
    background: #f7fafb;
    color: #249edb;
}

.modal-body {
    display: flex;
    flex-direction: column;
    gap: 17px;
    padding: 23px 24px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.form-group-full {
    width: 100%;
}

.form-group label {
    color: #718692;
    font-size: 11px;
    font-weight: 650;
}

.form-group label span {
    color: #e83e4d;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #dfe7eb;
    border-radius: 9px;
    outline: 0;
    background: #ffffff;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 12px;
}

.form-group input,
.form-group select {
    height: 40px;
    padding: 0 11px;
}

.form-group textarea {
    min-height: 90px;
    padding: 10px 11px;
    resize: vertical;
    line-height: 1.55;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #249edb;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #a0adb5;
}

.form-error {
    color: #c44f5a;
    font-size: 10px;
    line-height: 1.4;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 9px;
    padding-top: 5px;
}

/* =========================================================
   TEMPLATE
   ========================================================= */

.template-info {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 13px;
    border: 1px solid #dce9ef;
    border-radius: 10px;
    background: #f5f9fb;
    color: #7592a3;
}

.template-info > div {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.template-info strong {
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.template-info span {
    color: #8999a4;
    font-size: 10px;
    line-height: 1.5;
}

.template-preview {
    padding: 13px;
    border: 1px solid #e3eaee;
    border-radius: 10px;
    background: #fbfcfd;
}

.template-preview > div {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.template-preview strong {
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.template-preview p {
    margin: 0;
    color: #8999a4;
    font-size: 10px;
    line-height: 1.5;
}

.template-preview span {
    color: #638b79;
    font-size: 10px;
    font-weight: 650;
}

.checkbox-row {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 12px;
    border: 1px solid #e3eaee;
    border-radius: 9px;
    background: #ffffff;
    cursor: pointer;
}

.checkbox-row input {
    width: 15px;
    height: 15px;
    margin: 1px 0 0;
    accent-color: #249edb;
}

.checkbox-row > span {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.checkbox-row strong {
    color: var(--sc-page-text);
    font-size: 11px;
    font-weight: 650;
}

.checkbox-row small {
    color: #8999a4;
    font-size: 10px;
    line-height: 1.45;
}

.template-warning {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border: 1px solid #f0d3d7;
    border-radius: 9px;
    background: #fff5f6;
    color: #b15c66;
    font-size: 10px;
    font-weight: 600;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1000px) {
    .race-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .filters-bar {
        grid-template-columns: 1fr 1fr;
    }

    .clear-filter-btn {
        width: fit-content;
    }
}

@media (max-width: 700px) {
    .admin-page {
        gap: 16px;
    }

    .race-checklist-info {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .race-checklist-info-meta {
        width: 100%;
        margin-left: 51px;
        justify-content: flex-start;
    }

    .race-summary-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .show-card-header {
        padding: 18px;
    }

    .show-card-body {
        padding: 18px;
    }

    .filters-bar {
        grid-template-columns: 1fr;
    }

    .checklist-title-row {
        align-items: flex-start;
    }

    .checklist-footer {
        align-items: flex-start;
        flex-direction: column;
    }

    .status-select {
        width: 100%;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .modal-backdrop {
        align-items: flex-start;
        padding: 12px;
    }

    .admin-modal {
        max-height: calc(100vh - 24px);
    }

    .modal-header {
        padding: 18px;
    }

    .modal-body {
        padding: 18px;
    }

    .modal-footer {
        align-items: stretch;
        flex-direction: column-reverse;
    }

    .modal-footer .admin-btn {
        justify-content: center;
    }

    .race-checklist-info-main {
        min-width: calc(100% - 52px);
    }
}
</style>