<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import {
    Building2,
    Edit,
    Mail,
    MapPin,
    Plus,
    Search,
    Trash2,
    Users,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

interface Club {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
    responsible: string | null;
    phone: string | null;
    email: string | null;
    city: string | null;
    address: string | null;
    is_active: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface ClubsPagination {
    data: Club[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    clubs: ClubsPagination;
    filters?: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = () => {
    router.get(
        admin.clubs.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const getLogoUrl = (logo: string | null) => {
    if (!logo) {
        return null;
    }

    if (logo.startsWith('http://') || logo.startsWith('https://')) {
        return logo;
    }

    return `/storage/${logo}`;
};

const deleteClub = (club: Club) => {
    Swal.fire({
        title: '¿Eliminar club?',
        text: `Se eliminará a "${club.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(admin.clubs.destroy(club.id).url, {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="Clubes" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Organización
                </p>

                <h1 class="admin-page-title">
                    Clubes
                </h1>

                <p class="admin-page-subtitle">
                    Administra los clubes registrados en Sonríe Corriendo.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.clubs.create().url"
                    class="admin-btn admin-btn-primary"
                >
                    <Plus
                        :size="14"
                        :stroke-width="2"
                    />

                    Nuevo club
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
                            placeholder="Buscar club..."
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
                    {{ clubs.total }} clubes
                </div>
            </div>

            <!-- TABLE -->

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                Club
                            </th>

                            <th>
                                Responsable
                            </th>

                            <th>
                                Contacto
                            </th>

                            <th>
                                Ciudad
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
                            v-for="club in clubs.data"
                            :key="club.id"
                        >
                            <!-- CLUB -->

                            <td>
                                <div class="payment-method-cell">
                                    <div class="payment-method-icon">
                                        <img
                                            v-if="getLogoUrl(club.logo)"
                                            :src="getLogoUrl(club.logo)!"
                                            :alt="club.name"
                                            class="club-logo"
                                        />

                                        <Building2
                                            v-else
                                            :size="18"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <div class="payment-method-info">
                                        <div class="font-medium">
                                            {{ club.name }}
                                        </div>

                                        <div class="payment-description">
                                            {{ club.slug }}
                                        </div>

                                        <div class="payment-description">
                                            ID: {{ club.id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- RESPONSABLE -->

                            <td>
                                <div
                                    v-if="club.responsible"
                                    class="payment-method-info"
                                >
                                    <div class="font-medium">
                                        {{ club.responsible }}
                                    </div>

                                    <div
                                        v-if="club.email"
                                        class="payment-description flex items-center gap-1.5"
                                    >
                                        <Mail
                                            :size="13"
                                            :stroke-width="2"
                                        />

                                        {{ club.email }}
                                    </div>
                                </div>

                                <span
                                    v-else
                                    class="payment-description"
                                >
                                    Sin responsable
                                </span>
                            </td>

                            <!-- CONTACT -->

                            <td>
                                <div
                                    v-if="club.phone || club.email"
                                    class="payment-method-info"
                                >
                                    <div
                                        v-if="club.phone"
                                        class="payment-description"
                                    >
                                        {{ club.phone }}
                                    </div>

                                    <div
                                        v-if="club.email"
                                        class="payment-description flex items-center gap-1.5"
                                    >
                                        <Mail
                                            :size="13"
                                            :stroke-width="2"
                                        />

                                        {{ club.email }}
                                    </div>
                                </div>

                                <span
                                    v-else
                                    class="payment-description"
                                >
                                    Sin contacto
                                </span>
                            </td>

                            <!-- CITY -->

                            <td>
                                <div
                                    v-if="club.city"
                                    class="payment-description flex items-center gap-1.5"
                                >
                                    <MapPin
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    {{ club.city }}
                                </div>

                                <span
                                    v-else
                                    class="payment-description"
                                >
                                    Sin ciudad
                                </span>
                            </td>

                            <!-- STATUS -->

                            <td>
                                <span
                                    v-if="club.is_active"
                                    class="status-badge status-active"
                                >
                                    Activo
                                </span>

                                <span
                                    v-else
                                    class="status-badge status-inactive"
                                >
                                    Inactivo
                                </span>
                            </td>

                            <!-- ACTIONS -->

                            <td>
                                <div class="table-actions">
                                    <Link
                                        :href="admin.clubs.edit(club.id).url"
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
                                        @click="deleteClub(club)"
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

                        <tr v-if="clubs.data.length === 0">
                            <td
                                colspan="6"
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
                                        <strong>
                                            No se encontraron clubes.
                                        </strong>

                                        <p>
                                            Intenta realizar una búsqueda
                                            diferente.
                                        </p>
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
                v-if="clubs.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando
                    <strong>{{ clubs.from ?? 0 }}</strong>
                    a
                    <strong>{{ clubs.to ?? 0 }}</strong>
                    de
                    <strong>{{ clubs.total }}</strong>
                    clubes
                </div>

                <div class="admin-pagination">
                    <template
                        v-for="(link, index) in clubs.links"
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

<style scoped>
.club-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 10px;
    background: #ffffff;
}

.payment-method-icon {
    overflow: hidden;
}
</style>