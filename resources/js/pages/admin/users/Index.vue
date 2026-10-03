<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Edit,
    Mail,
    Plus,
    Search,
    ShieldCheck,
    Trash2,
    UserRound,
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
}

interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    roles: Role[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface UsersPagination {
    data: User[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    users: UsersPagination;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.users.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deleteUser = (user: User): void => {
    Swal.fire({
        title: '¿Eliminar usuario?',
        text: `Se eliminará a "${user.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(
                admin.users.destroy(user.id).url,
                {
                    preserveScroll: true,
                },
            );
        }
    });
};

const columns = [
    {
        key: 'user',
        label: 'Usuario',
    },
    {
        key: 'email',
        label: 'Correo',
    },
    {
        key: 'roles',
        label: 'Rol',
    },
];

const actions = [
    {
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (user: Record<string, any>) =>
            admin.users.edit(user.id).url,
    },
    {
        key: 'delete',
        label: 'Eliminar',
        icon: Trash2,
        class: 'action-btn-delete',
        onClick: (user: Record<string, any>) =>
            deleteUser(user as User),
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
                title: 'Usuarios',
                href: admin.users.index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Usuarios" />

    <div class="admin-page">
        <PageHeader
            eyebrow="Configuración"
            title="Usuarios"
            subtitle="Administra los usuarios y sus roles."
            :actions="[
                {
                    label: 'Nuevo usuario',
                    href: admin.users.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="users"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar usuario..."
            counter-label="usuarios"
            empty-title="No se encontraron usuarios."
            empty-description="Intenta realizar una búsqueda diferente."
            :search-icon="Search"
            :empty-icon="Users"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- USUARIO -->

            <template #cell-user="{ row }">
                <div class="user-cell">
                    <div class="user-icon">
                        <UserRound
                            :size="18"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="user-info">
                        <div class="user-name">
                            {{ row.name }}
                        </div>

                        <div class="user-username">
                            @{{ row.username }}
                        </div>

                        <div class="user-id">
                            ID: {{ row.id }}
                        </div>
                    </div>
                </div>
            </template>

            <!-- CORREO -->

            <template #cell-email="{ row }">
                <div class="user-email">
                    <Mail
                        :size="13"
                        :stroke-width="2"
                    />

                    {{ row.email }}
                </div>
            </template>

            <!-- ROLES -->

            <template #cell-roles="{ row }">
                <div class="roles-list">
                    <span
                        v-for="role in row.roles"
                        :key="role.id"
                        class="status-badge status-active"
                    >
                        <ShieldCheck
                            :size="12"
                            :stroke-width="2"
                        />

                        {{ role.name }}
                    </span>

                    <span
                        v-if="!row.roles.length"
                        class="status-badge status-inactive"
                    >
                        Sin rol
                    </span>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
.user-cell {
    display: flex;
    align-items: center;
    gap: 11px;
}

.user-icon {
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

.user-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 3px;
}

.user-name {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-username {
    overflow: hidden;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-id {
    color: #8b9ba6;
    font-size: 10px;
}

.user-email {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.user-email svg {
    flex: 0 0 auto;
    color: #7b8b97;
}

.roles-list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 7px;
}
</style>