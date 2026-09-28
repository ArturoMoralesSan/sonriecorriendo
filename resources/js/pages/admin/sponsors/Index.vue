<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import {
    Building2,
    Edit,
    Eye,
    Globe,
    Mail,
    Plus,
    Search,
    Trash2,
    UserRound,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

interface Sponsor {
    id: number;
    name: string;
    slug: string;
    logo: string | null;
    description: string | null;
    contact_name: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    is_active: boolean;
    race_sponsors_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface SponsorsPagination {
    data: Sponsor[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    sponsors: SponsorsPagination;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.sponsors.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deleteSponsor = async (
    sponsor: Sponsor,
): Promise<void> => {
    const result = await Swal.fire({
        title: '¿Eliminar patrocinador?',
        text: `Se eliminará el patrocinador "${sponsor.name}". Esta acción no se puede deshacer.`,
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
        admin.sponsors.destroy(sponsor.id).url,
        {
            preserveScroll: true,

            onSuccess: () => {
                Swal.fire({
                    title: 'Eliminado',
                    text: 'El patrocinador se eliminó correctamente.',
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false,
                });
            },

            onError: () => {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo eliminar el patrocinador.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            },
        },
    );
};

const getLogoUrl = (
    logo: string | null,
): string | null => {
    if (!logo) {
        return null;
    }

    if (
        logo.startsWith('http://') ||
        logo.startsWith('https://')
    ) {
        return logo;
    }

    return `/storage/${logo}`;
};

const formatWebsite = (
    website: string | null,
): string => {
    if (!website) {
        return '—';
    }

    return website
        .replace(/^https?:\/\//, '')
        .replace(/\/$/, '');
};

const defineStatusClass = (
    isActive: boolean,
): string => {
    return isActive
        ? 'status-badge status-active'
        : 'status-badge status-inactive';
};

const defineStatusLabel = (
    isActive: boolean,
): string => {
    return isActive
        ? 'Activo'
        : 'Inactivo';
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Patrocinadores',
                href: admin.sponsors.index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Patrocinadores" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Carreras
                </p>

                <h1 class="admin-page-title">
                    Patrocinadores
                </h1>

                <p class="admin-page-subtitle">
                    Administra los patrocinadores disponibles
                    para las carreras.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.sponsors.create().url"
                    class="admin-btn admin-btn-primary"
                >
                    <span class="admin-btn-icon">
                        <Plus
                            :size="14"
                            :stroke-width="2.2"
                        />
                    </span>

                    Nuevo patrocinador
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
                            placeholder="Buscar patrocinador..."
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
                        {{ sponsors.total }}
                    </strong>

                    <span>
                        patrocinadores
                    </span>
                </div>
            </div>

            <!-- TABLE -->

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                Patrocinador
                            </th>

                            <th>
                                Contacto
                            </th>

                            <th>
                                Sitio web
                            </th>

                            <th>
                                Carreras
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
                            v-for="sponsor in sponsors.data"
                            :key="sponsor.id"
                        >
                            <!-- PATROCINADOR -->

                            <td>
                                <div class="payment-method-cell">
                                    <div class="payment-method-icon sponsor-logo">
                                        <img
                                            v-if="
                                                getLogoUrl(
                                                    sponsor.logo,
                                                )
                                            "
                                            :src="
                                                getLogoUrl(
                                                    sponsor.logo,
                                                ) ?? undefined
                                            "
                                            :alt="
                                                `Logo de ${sponsor.name}`
                                            "
                                        />

                                        <Building2
                                            v-else
                                            :size="17"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <div class="payment-method-info">
                                        <strong>
                                            {{ sponsor.name }}
                                        </strong>

                                        <span>
                                            ID: {{ sponsor.id }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- CONTACTO -->

                            <td>
                                <div
                                    v-if="
                                        sponsor.contact_name ||
                                        sponsor.email
                                    "
                                    class="sponsor-contact"
                                >
                                    <div
                                        v-if="
                                            sponsor.contact_name
                                        "
                                        class="contact-line contact-primary"
                                    >
                                        <UserRound
                                            :size="12"
                                            :stroke-width="2"
                                        />

                                        {{
                                            sponsor.contact_name
                                        }}
                                    </div>

                                    <div
                                        v-if="
                                            sponsor.email
                                        "
                                        class="contact-line"
                                    >
                                        <Mail
                                            :size="12"
                                            :stroke-width="2"
                                        />

                                        {{ sponsor.email }}
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="payment-description empty"
                                >
                                    Sin contacto
                                </div>
                            </td>

                            <!-- SITIO WEB -->

                            <td>
                                <a
                                    v-if="sponsor.website"
                                    :href="sponsor.website"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="website-link"
                                >
                                    <Globe
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    {{
                                        formatWebsite(
                                            sponsor.website,
                                        )
                                    }}
                                </a>

                                <div
                                    v-else
                                    class="payment-description empty"
                                >
                                    Sin sitio web
                                </div>
                            </td>

                            <!-- CARRERAS -->

                            <td>
                                <span class="order-badge">
                                    {{
                                        sponsor.race_sponsors_count
                                    }}
                                </span>
                            </td>

                            <!-- ESTADO -->

                            <td>
                                <span
                                    :class="
                                        defineStatusClass(
                                            sponsor.is_active,
                                        )
                                    "
                                >
                                    <span
                                        class="status-dot"
                                    ></span>

                                    {{
                                        defineStatusLabel(
                                            sponsor.is_active,
                                        )
                                    }}
                                </span>
                            </td>

                            <!-- ACCIONES -->

                            <td>
                                <div class="table-actions">
                                    <Link
                                        :href="
                                            admin.sponsors.show(
                                                sponsor.id,
                                            ).url
                                        "
                                        class="action-btn action-btn-view"
                                        title="Ver patrocinador"
                                        aria-label="Ver patrocinador"
                                    >
                                        <Eye
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Detalle
                                    </Link>

                                    <Link
                                        :href="
                                            admin.sponsors.edit(
                                                sponsor.id,
                                            ).url
                                        "
                                        class="action-btn action-btn-edit"
                                        title="Editar patrocinador"
                                        aria-label="Editar patrocinador"
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
                                        title="Eliminar patrocinador"
                                        aria-label="Eliminar patrocinador"
                                        @click="
                                            deleteSponsor(
                                                sponsor,
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
                                sponsors.data.length === 0
                            "
                        >
                            <td
                                colspan="6"
                                class="admin-table-empty"
                            >
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <Building2
                                            :size="20"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <strong>
                                        No hay patrocinadores
                                    </strong>

                                    <span>
                                        {{
                                            search
                                                ? 'No se encontraron patrocinadores con ese término de búsqueda.'
                                                : 'Todavía no has registrado ningún patrocinador.'
                                        }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->

            <div
                v-if="sponsors.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando

                    <strong>
                        {{ sponsors.from ?? 0 }}
                    </strong>

                    a

                    <strong>
                        {{ sponsors.to ?? 0 }}
                    </strong>

                    de

                    <strong>
                        {{ sponsors.total }}
                    </strong>
                </div>

                <nav class="admin-pagination">
                    <template
                        v-for="(
                            link, index
                        ) in sponsors.links"
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

<style scoped>
.sponsor-logo {
    overflow: hidden;
}

.sponsor-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.sponsor-contact {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.contact-line {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #64748b;
    font-size: 10px;
    line-height: 1.35;
}

.contact-primary {
    color: #172b4d;
    font-size: 12px;
    font-weight: 600;
}

.website-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    max-width: 190px;
    color: #1769a8;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
}

.website-link:hover {
    text-decoration: underline;
}

.payment-description.empty {
    color: #94a3b8;
}
</style>