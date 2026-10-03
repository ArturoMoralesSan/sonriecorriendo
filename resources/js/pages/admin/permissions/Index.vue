<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Edit,
    KeyRound,
    Plus,
    Search,
    ShieldCheck,
    Trash2,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import DataTable from '@/components/admin/DataTable.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import admin from '@/routes/admin';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PermissionsPagination {
    data: Permission[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    permissions: PermissionsPagination;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.permissions.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deletePermission = async (
    permission: Permission,
): Promise<void> => {
    const result = await Swal.fire({
        title: '¿Eliminar permiso?',
        text: `Se eliminará el permiso "${permission.name}". Esta acción no se puede deshacer.`,
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
        admin.permissions.destroy(permission.id).url,
        {
            preserveScroll: true,

            onSuccess: () => {
                Swal.fire({
                    title: 'Eliminado',
                    text: 'El permiso se eliminó correctamente.',
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false,
                });
            },

            onError: () => {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo eliminar el permiso.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            },
        },
    );
};

const moduleOf = (name: string): string => {
    return name.split('.')[0] ?? name;
};

const actionOf = (name: string): string => {
    return name.split('.')[1] ?? '';
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Permisos',
                href: admin.permissions.index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Permisos" />

    <div class="admin-page">
        <PageHeader
            eyebrow="Configuración"
            title="Permisos"
            subtitle="Administra los permisos disponibles en el sistema."
            :actions="[
                {
                    label: 'Nuevo permiso',
                    href: admin.permissions.create().url,
                    icon: Plus,
                },
            ]"
        />

        <DataTable
            v-model:search="search"
            :pagination="permissions"
            :columns="[
                {
                    key: 'name',
                    label: 'Permiso',
                    class: 'permission-column',
                },
                {
                    key: 'module',
                    label: 'Módulo',
                },
                {
                    key: 'action',
                    label: 'Acción',
                },
                {
                    key: 'guard_name',
                    label: 'Guard',
                },
            ]"
            :actions="[
                {
                    key: 'edit',
                    label: 'Editar',
                    icon: Edit,
                    class: 'action-btn-edit',
                    href: (row) =>
                        admin.permissions.edit(row.id).url,
                },
                {
                    key: 'delete',
                    label: 'Eliminar',
                    icon: Trash2,
                    class: 'action-btn-delete',
                    onClick: (row) =>
                        deletePermission(row as Permission),
                },
            ]"
            search-placeholder="Buscar permiso..."
            :search-icon="Search"
            counter-label="permisos"
            empty-title="No se encontraron permisos"
            empty-description="Intenta cambiar el término de búsqueda."
            :empty-icon="ShieldCheck"
            @search="submitSearch"
        >
            <template #cell-name="{ row }">
                <div class="permission-cell">
                    <div class="permission-icon">
                        <KeyRound
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="permission-info">
                        <strong>
                            {{ row.name }}
                        </strong>

                        <span>
                            ID: {{ row.id }}
                        </span>
                    </div>
                </div>
            </template>

            <template #cell-module="{ row }">
                <span class="order-badge">
                    {{ moduleOf(row.name) }}
                </span>
            </template>

            <template #cell-action="{ row }">
                <div
                    v-if="actionOf(row.name)"
                    class="permission-description"
                >
                    {{ actionOf(row.name) }}
                </div>

                <div
                    v-else
                    class="permission-description empty"
                >
                    Sin acción
                </div>
            </template>

            <template #cell-guard_name="{ row }">
                <span class="status-badge status-active">
                    {{ row.guard_name }}
                </span>
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
.permission-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.permission-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border-radius: 9px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue);
}

.permission-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.permission-info strong {
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.permission-info span {
    color: var(--sc-page-text-secondary);
    font-size: 10px;
}

.permission-description {
    color: var(--sc-page-text);
    font-size: 12px;
}

.permission-description.empty {
    color: var(--sc-page-text-secondary);
    font-style: italic;
}

.order-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 9px;
    border-radius: 7px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue);
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 9px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.status-active {
    background: var(--sc-page-green-light);
    color: #159c83;
}

.status-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: currentColor;
}
</style>