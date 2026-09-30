<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
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

const submitSearch = () => {
    router.get(
        admin.users.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const deleteUser = (user: User) => {
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
            router.delete(admin.users.destroy(user.id).url, {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="Usuarios" />

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
                    Usuarios
                </h1>

                <p class="admin-page-subtitle">
                    Administra los usuarios y sus roles.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.users.create().url"
                    class="admin-btn admin-btn-primary"
                >
                    <Plus
                        :size="14"
                        :stroke-width="2"
                    />

                    Nuevo usuario
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
                            placeholder="Buscar usuario..."
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
                    {{ users.total }} usuarios
                </div>
            </div>

            <!-- TABLE -->

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                Usuario
                            </th>

                            <th>
                                Correo
                            </th>

                            <th>
                                Rol
                            </th>

                            <th class="text-right">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                        >
                            <!-- USER -->

                            <td>
                                <div class="payment-method-cell">
                                    <div class="payment-method-icon">
                                        <UserRound
                                            :size="18"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <div class="payment-method-info">
                                        <div class="font-medium">
                                            {{ user.name }}
                                        </div>

                                        <div class="payment-description">
                                            @{{ user.username }}
                                        </div>

                                        <div class="payment-description">
                                            ID: {{ user.id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- EMAIL -->

                            <td>
                                <div class="payment-method-cell">
                                    <div class="payment-method-info">
                                        <div class="payment-description flex items-center gap-1.5">
                                            <Mail
                                                :size="13"
                                                :stroke-width="2"
                                            />

                                            {{ user.email }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- ROLES -->

                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="role in user.roles"
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
                                        v-if="!user.roles.length"
                                        class="status-badge status-inactive"
                                    >
                                        Sin rol
                                    </span>
                                </div>
                            </td>

                            <!-- ACTIONS -->

                            <td>
                                <div class="table-actions">
                                    <Link
                                        :href="admin.users.edit(user.id).url"
                                        class="action-btn action-btn-edit"
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
                                        @click="deleteUser(user)"
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

                        <!-- =================================================
                             EMPTY
                        ================================================== -->

                        <tr v-if="users.data.length === 0">
                            <td
                                colspan="4"
                                class="admin-table-empty"
                            >
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <Users
                                            :size="22"
                                            :stroke-width="1.8"
                                        />
                                    </div>

                                    <div>
                                        <p>
                                            No se encontraron usuarios.
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

            <!-- =================================================
                 PAGINATION
            ================================================== -->

            <div
                v-if="users.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando
                    <strong>{{ users.from ?? 0 }}</strong>
                    a
                    <strong>{{ users.to ?? 0 }}</strong>
                    de
                    <strong>{{ users.total }}</strong>
                    usuarios
                </div>

                <div class="admin-pagination">
                    <template
                        v-for="(link, index) in users.links"
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
