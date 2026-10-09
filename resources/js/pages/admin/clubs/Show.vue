<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import {
    ArrowLeft,
    Building2,
    CalendarDays,
    CheckCircle2,
    Edit,
    Mail,
    MapPin,
    Phone,
    ShieldCheck,
    UserRound,
    Users,
    XCircle,
} from 'lucide-vue-next';

import { computed } from 'vue';
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
    created_at?: string | null;
    updated_at?: string | null;
}

const props = defineProps<{
    club: Club;
}>();

const logoUrl = computed(() => {
    if (!props.club.logo) {
        return null;
    }

    const logo = props.club.logo.replace(/^\/+/, '');

    if (/^https?:\/\//i.test(logo)) {
        return logo;
    }

    if (logo.startsWith('storage/')) {
        return `/${logo}`;
    }

    return `/storage/${logo}`;
});

const formatDate = (
    date: string | null | undefined,
): string => {
    if (!date) {
        return '—';
    }

    try {
        const normalizedDate = date.includes('T')
            ? date
            : `${date}T00:00:00`;

        const parsedDate = new Date(normalizedDate);

        if (Number.isNaN(parsedDate.getTime())) {
            return date;
        }

        return new Intl.DateTimeFormat('es-MX', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        }).format(parsedDate);
    } catch {
        return date;
    }
};

const fullAddress = (): string => {
    const parts = [
        props.club.address,
        props.club.city,
    ];

    return parts.filter(Boolean).join(', ') || 'No especificada';
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Clubs',
                href: admin.clubs.index(),
            },
            {
                title: 'Detalle',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Club: ${props.club.name}`" />

    <div class="admin-page">
        <header class="admin-page-header">
            <div>
                <div class="admin-page-eyebrow">
                    Clubs
                </div>

                <h1 class="admin-page-title">
                    {{ props.club.name }}
                </h1>

                <p class="admin-page-subtitle">
                    Consulta la información general, los datos de contacto
                    y la ubicación de este club.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.clubs.index().url"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft
                        :size="16"
                        :stroke-width="2"
                        class="admin-btn-icon"
                    />

                    <span>Volver</span>
                </Link>

                <Link
                    :href="admin.clubs.edit(props.club.id).url"
                    class="admin-btn admin-btn-secondary"
                >
                    <Edit
                        :size="16"
                        :stroke-width="2"
                        class="admin-btn-icon"
                    />

                    <span>Editar</span>
                </Link>
            </div>
        </header>

        <div class="admin-show-grid">
            <!-- INFORMACIÓN DEL CLUB -->
            <section class="admin-show-card admin-show-main">
                <div class="admin-show-card-header">
                    <div class="admin-show-section-icon">
                        <Building2
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div>
                        <h2 class="admin-show-card-title">
                            Información del club
                        </h2>

                        <p class="admin-show-card-description">
                            Datos generales, descripción y estado del club.
                        </p>
                    </div>
                </div>

                <div class="admin-show-profile">
                    <div class="admin-show-logo">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            :alt="`Logo de ${props.club.name}`"
                            class="admin-show-logo-image"
                        />

                        <Users
                            v-else
                            :size="34"
                            :stroke-width="1.6"
                        />
                    </div>

                    <div class="admin-show-profile-info">
                        <h3>
                            {{ props.club.name }}
                        </h3>

                        <span class="admin-show-slug">
                            {{ props.club.slug }}
                        </span>

                        <span
                            :class="[
                                'status-badge',
                                props.club.is_active
                                    ? 'status-active'
                                    : 'status-inactive',
                            ]"
                        >
                            <CheckCircle2
                                v-if="props.club.is_active"
                                :size="13"
                                :stroke-width="2"
                            />

                            <XCircle
                                v-else
                                :size="13"
                                :stroke-width="2"
                            />

                            {{
                                props.club.is_active
                                    ? 'Activo'
                                    : 'Inactivo'
                            }}
                        </span>
                    </div>
                </div>

                <div class="admin-show-description">
                    <h3 class="admin-show-label">
                        Descripción
                    </h3>

                    <p>
                        {{ props.club.description || 'Sin descripción registrada.' }}
                    </p>
                </div>

                <div class="admin-show-details-grid">
                    <div class="admin-show-detail">
                        <span class="admin-show-detail-label">
                            Identificador
                        </span>

                        <span class="admin-show-detail-value">
                            {{ props.club.slug || '—' }}
                        </span>
                    </div>

                    <div class="admin-show-detail">
                        <span class="admin-show-detail-label">
                            Estado
                        </span>

                        <span
                            :class="[
                                'status-badge',
                                props.club.is_active
                                    ? 'status-active'
                                    : 'status-inactive',
                            ]"
                        >
                            {{
                                props.club.is_active
                                    ? 'Activo'
                                    : 'Inactivo'
                            }}
                        </span>
                    </div>

                    <div class="admin-show-detail">
                        <span class="admin-show-detail-label">
                            Responsable
                        </span>

                        <span class="admin-show-detail-value">
                            {{ props.club.responsible || 'No especificado' }}
                        </span>
                    </div>

                    <div
                        v-if="props.club.created_at"
                        class="admin-show-detail"
                    >
                        <span class="admin-show-detail-label">
                            Registrado
                        </span>

                        <span class="admin-show-detail-value">
                            {{ formatDate(props.club.created_at) }}
                        </span>
                    </div>

                    <div
                        v-if="props.club.updated_at"
                        class="admin-show-detail"
                    >
                        <span class="admin-show-detail-label">
                            Última actualización
                        </span>

                        <span class="admin-show-detail-value">
                            {{ formatDate(props.club.updated_at) }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- CONTACTO -->
            <section class="admin-show-card">
                <div class="admin-show-card-header">
                    <div class="admin-show-section-icon">
                        <Phone
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div>
                        <h2 class="admin-show-card-title">
                            Contacto
                        </h2>

                        <p class="admin-show-card-description">
                            Información del responsable y medios de contacto.
                        </p>
                    </div>
                </div>

                <div class="admin-contact-list">
                    <div class="admin-contact-item">
                        <div class="admin-contact-icon">
                            <UserRound
                                :size="15"
                                :stroke-width="2"
                            />
                        </div>

                        <div class="admin-contact-content">
                            <span class="admin-contact-label">
                                Responsable
                            </span>

                            <span class="admin-contact-value">
                                {{ props.club.responsible || 'No especificado' }}
                            </span>
                        </div>
                    </div>

                    <div class="admin-contact-item">
                        <div class="admin-contact-icon">
                            <Phone
                                :size="15"
                                :stroke-width="2"
                            />
                        </div>

                        <div class="admin-contact-content">
                            <span class="admin-contact-label">
                                Teléfono
                            </span>

                            <a
                                v-if="props.club.phone"
                                :href="`tel:${props.club.phone}`"
                                class="admin-contact-value admin-contact-link"
                            >
                                {{ props.club.phone }}
                            </a>

                            <span
                                v-else
                                class="admin-contact-empty"
                            >
                                No especificado
                            </span>
                        </div>
                    </div>

                    <div class="admin-contact-item">
                        <div class="admin-contact-icon">
                            <Mail
                                :size="15"
                                :stroke-width="2"
                            />
                        </div>

                        <div class="admin-contact-content">
                            <span class="admin-contact-label">
                                Correo electrónico
                            </span>

                            <a
                                v-if="props.club.email"
                                :href="`mailto:${props.club.email}`"
                                class="admin-contact-value admin-contact-link"
                            >
                                {{ props.club.email }}
                            </a>

                            <span
                                v-else
                                class="admin-contact-empty"
                            >
                                No especificado
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- UBICACIÓN -->
        <section class="admin-show-card admin-location-card">
            <div class="admin-show-card-header">
                <div class="admin-show-section-icon">
                    <MapPin
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <h2 class="admin-show-card-title">
                        Detalles de ubicación
                    </h2>

                    <p class="admin-show-card-description">
                        Domicilio y ciudad registrados para el club.
                    </p>
                </div>
            </div>

            <div class="admin-location-grid">
                <div class="admin-location-detail admin-location-detail-full">
                    <span class="admin-location-label">
                        Dirección
                    </span>

                    <span class="admin-location-value">
                        {{ props.club.address || 'No especificada' }}
                    </span>
                </div>

                <div class="admin-location-detail">
                    <span class="admin-location-label">
                        Ciudad
                    </span>

                    <span class="admin-location-value">
                        {{ props.club.city || 'No especificada' }}
                    </span>
                </div>

                <div class="admin-location-detail">
                    <span class="admin-location-label">
                        Dirección completa
                    </span>

                    <span class="admin-location-value">
                        {{ fullAddress() }}
                    </span>
                </div>
            </div>
        </section>

        <!-- ESTADO DEL CLUB -->
        <section class="admin-show-card admin-delivery-card">
            <div class="admin-show-card-header">
                <div class="admin-show-section-icon">
                    <ShieldCheck
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <h2 class="admin-show-card-title">
                        Estado del club
                    </h2>

                    <p class="admin-show-card-description">
                        Situación actual del registro.
                    </p>
                </div>
            </div>

            <div class="admin-delivery-content">
                <div class="admin-delivery-icon">
                    <Building2
                        :size="22"
                        :stroke-width="1.8"
                    />
                </div>

                <div class="admin-delivery-info">
                    <h3>
                        {{
                            props.club.is_active
                                ? 'Club activo'
                                : 'Club inactivo'
                        }}
                    </h3>

                    <p>
                        {{
                            props.club.is_active
                                ? 'Este club está marcado como activo en el sistema.'
                                : 'Este club está marcado como inactivo en el sistema.'
                        }}
                    </p>
                </div>

                <div class="admin-delivery-count">
                    <span class="admin-delivery-count-value">
                        {{ props.club.is_active ? 'Sí' : 'No' }}
                    </span>

                    <span class="admin-delivery-count-label">
                        {{
                            props.club.is_active
                                ? 'Disponible'
                                : 'No disponible'
                        }}
                    </span>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.admin-page {
    width: 100%;
    min-height: 90%;
    padding: 28px 30px 38px;
    background: var(--sc-page-background);
    color: var(--sc-page-text);
}

.admin-page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 24px;
}

.admin-page-eyebrow {
    margin-bottom: 5px;
    color: var(--sc-page-blue);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.admin-page-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 27px;
    font-weight: 750;
    line-height: 1.15;
    letter-spacing: -0.02em;
    overflow-wrap: anywhere;
}

.admin-page-subtitle {
    margin: 7px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 13px;
    line-height: 1.5;
}

.admin-page-header-actions {
    display: flex;
    align-items: center;
    gap: 9px;
    flex-shrink: 0;
}

.admin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 40px;
    padding: 0 14px;
    border: 1px solid transparent;
    border-radius: 10px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    white-space: nowrap;
    box-sizing: border-box;
    cursor: pointer;
    transition:
        transform 0.15s ease,
        box-shadow 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.admin-btn:hover {
    transform: translateY(-1px);
}

.admin-btn-icon {
    flex: 0 0 auto;
}

.admin-btn-primary {
    border-color: transparent;
    background: var(--button-primary);
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(36, 158, 219, 0.14);
}

.admin-btn-primary:hover {
    box-shadow: 0 6px 14px rgba(36, 158, 219, 0.18);
}

.admin-btn-secondary {
    border-color: var(--sc-page-border);
    background: #ffffff;
    color: var(--sc-page-text);
}

.admin-btn-secondary:hover {
    background: #fbfcfd;
    border-color: #d3dbe4;
}

.admin-show-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.65fr);
    gap: 18px;
    margin-bottom: 18px;
}

.admin-show-card {
    width: 100%;
    border: 1px solid var(--sc-page-border);
    border-radius: var(--sc-page-radius);
    background: #ffffff;
    box-shadow:
        0 4px 15px rgba(27, 62, 90, 0.035),
        0 1px 3px rgba(27, 62, 90, 0.025);
    box-sizing: border-box;
}

.admin-show-main {
    min-width: 0;
}

.admin-show-card-header {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 22px 22px 0;
}

.admin-show-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border-radius: 9px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue-dark);
}

.admin-show-card-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 14px;
    font-weight: 700;
    line-height: 1.3;
}

.admin-show-card-description {
    margin: 3px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.5;
}

.admin-show-profile {
    display: flex;
    align-items: center;
    gap: 16px;
    margin: 22px;
    padding: 16px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #fbfcfd;
}

.admin-show-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 92px;
    height: 92px;
    flex: 0 0 92px;
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #ffffff;
    color: var(--sc-page-muted);
}

.admin-show-logo-image {
    width: 100%;
    height: 100%;
    padding: 5px;
    object-fit: contain;
}

.admin-show-profile-info {
    display: flex;
    align-items: flex-start;
    flex-direction: column;
    min-width: 0;
}

.admin-show-profile-info h3 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 18px;
    font-weight: 700;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.admin-show-slug {
    margin-top: 4px;
    color: var(--sc-page-muted);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    width: fit-content;
    margin-top: 9px;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 650;
    line-height: 1.3;
    white-space: nowrap;
}

.status-active {
    background: var(--sc-page-green-light);
    color: #12927b;
}

.status-inactive {
    background: var(--sc-page-red-light);
    color: #c73542;
}

.admin-show-description {
    margin: 0 22px 20px;
    padding-top: 18px;
    border-top: 1px solid var(--sc-page-border-soft);
}

.admin-show-label {
    margin: 0 0 7px;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 650;
}

.admin-show-description p {
    margin: 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.65;
    overflow-wrap: anywhere;
    white-space: pre-line;
}

.admin-show-details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1px;
    margin: 0 22px 22px;
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 9px;
    background: var(--sc-page-border);
}

.admin-show-detail {
    display: flex;
    align-items: flex-start;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    padding: 12px;
    background: #ffffff;
}

.admin-show-detail-label {
    color: var(--sc-page-muted);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.admin-show-detail-value {
    min-width: 0;
    max-width: 100%;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 550;
    overflow-wrap: anywhere;
}

.admin-show-detail .status-badge {
    margin-top: 0;
}

.admin-contact-list {
    display: flex;
    flex-direction: column;
    margin: 22px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    overflow: hidden;
}

.admin-contact-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 13px;
    background: #ffffff;
}

.admin-contact-item + .admin-contact-item {
    border-top: 1px solid var(--sc-page-border-soft);
}

.admin-contact-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    border-radius: 8px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue-dark);
}

.admin-contact-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.admin-contact-label {
    color: var(--sc-page-muted);
    font-size: 10px;
    font-weight: 600;
    line-height: 1.3;
}

.admin-contact-value {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 2px;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 550;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.admin-contact-link {
    color: var(--sc-page-blue-dark);
    text-decoration: none;
}

.admin-contact-link:hover {
    text-decoration: underline;
}

.admin-contact-empty {
    margin-top: 2px;
    color: var(--sc-page-muted);
    font-size: 11px;
    line-height: 1.45;
}

/* UBICACIÓN */

.admin-location-card {
    margin-bottom: 18px;
    overflow: hidden;
}

.admin-location-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1px;
    margin-top: 22px;
    border-top: 1px solid var(--sc-page-border);
    background: var(--sc-page-border);
}

.admin-location-detail {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
    padding: 16px 20px;
    background: #ffffff;
}

.admin-location-detail-full {
    grid-column: 1 / -1;
}

.admin-location-label {
    color: var(--sc-page-muted);
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.025em;
    text-transform: uppercase;
}

.admin-location-value {
    color: var(--sc-page-text);
    font-size: 12px;
    line-height: 1.55;
    overflow-wrap: anywhere;
}

/* ESTADO DEL CLUB */

.admin-delivery-card {
    overflow: hidden;
}

.admin-delivery-content {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 22px;
    padding: 18px;
    border: 1px solid var(--sc-page-border);
    border-radius: 11px;
    background: #fbfcfd;
}

.admin-delivery-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    border-radius: 11px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue-dark);
}

.admin-delivery-info {
    min-width: 0;
    flex: 1;
}

.admin-delivery-info h3 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
}

.admin-delivery-info p {
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.6;
}

.admin-delivery-count {
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: 4px;
    min-width: 115px;
    padding-left: 16px;
    border-left: 1px solid var(--sc-page-border);
}

.admin-delivery-count-value {
    color: var(--sc-page-blue-dark);
    font-size: 24px;
    font-weight: 750;
    line-height: 1;
}

.admin-delivery-count-label {
    color: var(--sc-page-muted);
    font-size: 10px;
    text-align: center;
}

/* RESPONSIVE */

@media (max-width: 1100px) {
    .admin-show-grid {
        grid-template-columns: minmax(0, 1fr) minmax(280px, 0.8fr);
    }

    .admin-location-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 900px) {
    .admin-show-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 700px) {
    .admin-page {
        padding: 22px 20px 30px;
    }

    .admin-page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .admin-page-header-actions {
        width: 100%;
        flex-wrap: wrap;
    }

    .admin-page-header-actions .admin-btn {
        flex: 1;
    }

    .admin-show-details-grid {
        grid-template-columns: 1fr;
    }

    .admin-location-grid {
        grid-template-columns: 1fr;
    }

    .admin-location-detail-full {
        grid-column: auto;
    }
}

@media (max-width: 520px) {
    .admin-page {
        padding: 18px 14px 24px;
    }

    .admin-page-title {
        font-size: 24px;
    }

    .admin-page-header-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .admin-page-header-actions .admin-btn {
        width: 100%;
        flex: none;
    }

    .admin-show-card-header {
        padding: 18px 18px 0;
    }

    .admin-show-profile {
        align-items: flex-start;
        flex-direction: column;
        margin: 18px;
    }

    .admin-show-description {
        margin-right: 18px;
        margin-left: 18px;
    }

    .admin-show-details-grid {
        margin-right: 18px;
        margin-left: 18px;
    }

    .admin-contact-list {
        margin: 18px;
    }

    .admin-location-detail {
        padding: 14px 18px;
    }

    .admin-delivery-content {
        align-items: flex-start;
        flex-wrap: wrap;
        margin: 18px;
    }

    .admin-delivery-info {
        flex-basis: calc(100% - 60px);
    }

    .admin-delivery-count {
        align-items: flex-start;
        width: 100%;
        padding: 14px 0 0;
        border-top: 1px solid var(--sc-page-border);
        border-left: 0;
    }

    .admin-delivery-count-label {
        text-align: left;
    }
}
</style>