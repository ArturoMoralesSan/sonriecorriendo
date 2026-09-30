<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
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

const submitSearch = () => {
    router.get(
        admin.roles.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const deleteRole = (role: Role) => {
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
            router.delete(admin.roles.destroy(role.id).url, {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="Roles" />

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
                    Roles
                </h1>

                <p class="admin-page-subtitle">
                    Administra los roles y sus permisos.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.roles.create().url"
                    class="admin-btn admin-btn-primary"
                >
                    <Plus
                        :size="14"
                        :stroke-width="2"
                    />

                    Nuevo rol
                </Link>
            </div>
        </header>

        <!-- =================================================
             TABLE CARD
        ================================================== -->

        <div class="admin-table-card">
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
                            placeholder="Buscar rol..."
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
                    {{ roles.total }} roles
                </div>
            </div>

            <!-- TABLE -->

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                Rol
                            </th>

                            <th>
                                Usuarios
                            </th>

                            <th class="text-right">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="role in roles.data"
                            :key="role.id"
                        >
                            <td>
                                <div class="payment-method-cell">
                                    <div class="payment-method-icon">
                                        <ShieldCheck
                                            :size="18"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <div class="payment-method-info">
                                        <div class="font-medium">
                                            {{ role.name }}
                                        </div>

                                        <div class="payment-description">
                                            ID: {{ role.id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="order-badge">
                                    <Users
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    {{ role.users_count }}
                                </span>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <Link
                                        :href="admin.roles.edit(role.id).url"
                                        class="action-btn action-btn-edit"
                                    >
                                        <Edit
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Editar
                                    </Link>

                                    <button
                                        v-if="role.name !== 'admin'"
                                        type="button"
                                        class="action-btn action-btn-delete"
                                        @click="deleteRole(role)"
                                    >
                                        <Trash2
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Eliminar
                                    </button>

                                    <span
                                        v-else
                                        class="status-badge status-inactive"
                                    >
                                        Protegido
                                    </span>
                                </div>
                            </td>
                        </tr>

                        <!-- SIN RESULTADOS -->

                        <tr v-if="roles.data.length === 0">
                            <td
                                colspan="3"
                                class="admin-table-empty"
                            >
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <ShieldCheck
                                            :size="22"
                                            :stroke-width="1.8"
                                        />
                                    </div>

                                    <div>
                                        <p>
                                            No se encontraron roles.
                                        </p>

                                        <span>
                                            Intenta realizar una búsqueda
                                            diferente.
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->

            <div
                v-if="roles.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando
                    <strong>{{ roles.from ?? 0 }}</strong>
                    a
                    <strong>{{ roles.to ?? 0 }}</strong>
                    de
                    <strong>{{ roles.total }}</strong>
                    roles
                </div>

                <div class="admin-pagination">
                    <template
                        v-for="(link, index) in roles.links"
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
                </div>
            </div>
        </div>
    </div>
</template>