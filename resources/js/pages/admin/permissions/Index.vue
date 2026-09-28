<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import {
    Edit,
    KeyRound,
    Plus,
    Search,
    ShieldCheck,
    Trash2,
} from 'lucide-vue-next';

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
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Configuración
                </p>

                <h1 class="admin-page-title">
                    Permisos
                </h1>

                <p class="admin-page-subtitle">
                    Administra los permisos disponibles en el sistema.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.permissions.create().url"
                    class="admin-btn admin-btn-primary"
                >
                    <span class="admin-btn-icon">
                        <Plus
                            :size="14"
                            :stroke-width="2.2"
                        />
                    </span>

                    Nuevo permiso
                </Link>
            </div>
        </header>

        <!-- =================================================
             MAIN CARD
        ================================================== -->

        <section class="admin-table-card">
            <!-- TOOLBAR -->

            <div class="admin-table-toolbar">
                <form
                    class="admin-search-form"
                    @submit.prevent="submitSearch"
                >
                    <div class="admin-search-wrapper">
                        <Search
                            class="admin-search-icon"
                            :size="16"
                            :stroke-width="2"
                        />

                        <input
                            v-model="search"
                            type="search"
                            placeholder="Buscar permiso..."
                            class="admin-search-input"
                        />
                    </div>

                    <button
                        type="submit"
                        class="admin-btn admin-btn-search"
                    >
                        <Search
                            :size="14"
                            :stroke-width="2"
                        />

                        Buscar
                    </button>
                </form>

                <div class="admin-table-counter">
                    <strong>
                        {{ permissions.total }}
                    </strong>

                    <span>
                        permisos
                    </span>
                </div>
            </div>

            <!-- TABLE -->

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                Permiso
                            </th>

                            <th>
                                Módulo
                            </th>

                            <th>
                                Acción
                            </th>

                            <th>
                                Guard
                            </th>

                            <th class="text-right">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="permission in permissions.data"
                            :key="permission.id"
                        >
                            <!-- PERMISO -->

                            <td>
                                <div class="payment-method-cell">
                                    <div class="payment-method-icon">
                                        <KeyRound
                                            :size="17"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <div class="payment-method-info">
                                        <strong>
                                            {{ permission.name }}
                                        </strong>

                                        <span>
                                            ID: {{ permission.id }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- MÓDULO -->

                            <td>
                                <span class="order-badge">
                                    {{ moduleOf(permission.name) }}
                                </span>
                            </td>

                            <!-- ACCIÓN -->

                            <td>
                                <div
                                    v-if="actionOf(permission.name)"
                                    class="payment-description"
                                >
                                    {{ actionOf(permission.name) }}
                                </div>

                                <div
                                    v-else
                                    class="payment-description empty"
                                >
                                    Sin acción
                                </div>
                            </td>

                            <!-- GUARD -->

                            <td>
                                <span class="status-badge status-active">
                                    <span class="status-dot"></span>

                                    {{ permission.guard_name }}
                                </span>
                            </td>

                            <!-- ACCIONES -->

                            <td>
                                <div class="table-actions">
                                    <Link
                                        :href="
                                            admin.permissions.edit(
                                                permission.id,
                                            ).url
                                        "
                                        class="action-btn action-btn-edit"
                                        title="Editar permiso"
                                        aria-label="Editar permiso"
                                    >
                                        <Edit
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Editar
                                    </Link>

                                    <button
                                        type="button"
                                        class="action-btn action-btn-delete"
                                        title="Eliminar permiso"
                                        aria-label="Eliminar permiso"
                                        @click="
                                            deletePermission(
                                                permission,
                                            )
                                        "
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

                        <!-- EMPTY -->

                        <tr
                            v-if="
                                permissions.data.length === 0
                            "
                        >
                            <td
                                colspan="5"
                                class="admin-table-empty"
                            >
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <ShieldCheck
                                            :size="20"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <strong>
                                        No se encontraron permisos
                                    </strong>

                                    <span>
                                        Intenta cambiar el término
                                        de búsqueda.
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->

            <div
                v-if="permissions.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando

                    <strong>
                        {{ permissions.from ?? 0 }}
                    </strong>

                    a

                    <strong>
                        {{ permissions.to ?? 0 }}
                    </strong>

                    de

                    <strong>
                        {{ permissions.total }}
                    </strong>
                </div>

                <nav class="admin-pagination">
                    <template
                        v-for="(link, index) in permissions.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="pagination-btn"
                            :class="{
                                active: link.active,
                            }"
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
    </div>
</template>