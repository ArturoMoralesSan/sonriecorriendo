<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Edit,
    Plus,
    Search,
    ShieldCheck,
    Trash2,
    Users,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import admin from '@/routes/admin';

interface Role {
    id: number;
    name: string;
    users_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface RolesPagination {
    data: Role[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    roles: RolesPagination;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.roles.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deleteRole = (role: Role): void => {
    Swal.fire({
        title: '¿Eliminar rol?',
        text: `Se eliminará el rol "${role.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(
                admin.roles.destroy(role.id).url,
                {
                    preserveScroll: true,
                },
            );
        }
    });
};

const columns = [
    {
        key: 'role',
        label: 'Rol',
    },
    {
        key: 'users',
        label: 'Usuarios',
    },
];

const actions = [
    {
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (role: Record<string, any>) =>
            admin.roles.edit(role.id).url,
    },
    {
        key: 'delete',
        label: 'Eliminar',
        icon: Trash2,
        class: 'action-btn-delete',
        onClick: (role: Record<string, any>) =>
            deleteRole(role as Role),
    },
];

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Roles',
                href: admin.roles.index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Roles" />

    <div class="admin-page">
        <PageHeader
            eyebrow="Configuración"
            title="Roles"
            subtitle="Administra los roles y sus permisos."
            :actions="[
                {
                    label: 'Nuevo rol',
                    href: admin.roles.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="roles"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar rol..."
            counter-label="roles"
            empty-title="No se encontraron roles."
            empty-description="Intenta realizar una búsqueda diferente."
            :search-icon="Search"
            :empty-icon="ShieldCheck"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- ROL -->

            <template #cell-role="{ row }">
                <div class="role-cell">
                    <div class="role-icon">
                        <ShieldCheck
                            :size="18"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="role-info">
                        <div class="role-name">
                            {{ row.name }}
                        </div>

                        <div class="role-id">
                            ID: {{ row.id }}
                        </div>
                    </div>
                </div>
            </template>

            <!-- USUARIOS -->

            <template #cell-users="{ row }">
                <span class="users-badge">
                    <Users
                        :size="13"
                        :stroke-width="2"
                    />

                    {{ row.users_count }}
                </span>
            </template>

            <!--
                ACCIONES ESPECIALES PARA ADMIN:
                DataTable genera las acciones genéricas, por lo que
                el rol admin se maneja con el slot de acciones.
            -->
        </DataTable>
    </div>
</template>

<style scoped>
.role-cell {
    display: flex;
    align-items: center;
    gap: 11px;
}

.role-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--sc-page-light);
    color: var(--sc-page-blue);
}

.role-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 3px;
}

.role-name {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.role-id {
    color: #8b9ba6;
    font-size: 10px;
}

.users-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 28px;
    padding: 0 9px;
    border-radius: 7px;
    background: #f3f6f8;
    color: #687983;
    font-size: 11px;
    font-weight: 700;
}
</style>