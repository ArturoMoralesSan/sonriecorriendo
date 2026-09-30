<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';

import {
    ArrowLeft,
    Building2,
    Check,
    CalendarDays,
    CheckCircle2,
    Edit,
    ExternalLink,
    Globe,
    Mail,
    MapPin,
    Plus,
    Eye,
    Trophy,
    UserRound,
    X,
    XCircle,
    Trash2,
    Phone,
    Save,
} from 'lucide-vue-next';

import Swal from 'sweetalert2';
import { ref } from 'vue';

import admin from '@/routes/admin';
import sponsors from '@/routes/admin/sponsors';

interface Race {
    id: number;
    name: string;
    slug: string;
    event_date: string | null;
    start_time: string | null;
    end_time: string | null;
    location: string | null;
}

interface RaceSponsor {
    id: number;
    type: string;
    amount: string | number | null;
    benefits: string | null;
    sort_order: number;
    is_active: boolean;
    race: Race | null;
}

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
    created_at?: string | null;
    updated_at?: string | null;
    race_sponsors: RaceSponsor[];
}

const props = defineProps<{
    sponsor: Sponsor;
    races: Race[];
}>();

const showAssociationModal = ref(false);

const form = useForm({
    race_id: '',
    type: 'sponsor',
    amount: '',
    benefits: '',
    is_active: true,
});

const openAssociationModal = (): void => {
    form.reset();

    form.race_id = '';
    form.type = 'sponsor';
    form.amount = '';
    form.benefits = '';
    form.is_active = true;

    showAssociationModal.value = true;
};

const closeAssociationModal = (): void => {
    if (form.processing) {
        return;
    }

    showAssociationModal.value = false;
    form.clearErrors();
};

const submitAssociation = (): void => {
    form.post(
        `/admin/sponsors/${props.sponsor.id}/races`,
        {
            preserveScroll: true,

            onSuccess: () => {
                showAssociationModal.value = false;

                form.reset();
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

const logoUrl = getLogoUrl(
    props.sponsor.logo,
);

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

        const parsedDate = new Date(
            normalizedDate,
        );

        if (
            Number.isNaN(
                parsedDate.getTime(),
            )
        ) {
            return date;
        }

        return new Intl.DateTimeFormat(
            'es-MX',
            {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
            },
        ).format(parsedDate);
    } catch {
        return date;
    }
};

const formatTime = (
    time: string | null | undefined,
): string => {
    if (!time) {
        return '';
    }

    return time.substring(0, 5);
};

const formatRaceDate = (
    race: Race,
): string => {
    return formatDate(
        race.event_date,
    );
};

const getRaceTime = (
    race: Race,
): string => {
    return formatTime(
        race.start_time,
    );
};

const formatRaceOption = (
    race: Race,
): string => {
    const date = formatDate(
        race.event_date,
    );

    const time = formatTime(
        race.start_time,
    );

    if (time) {
        return `${race.name} — ${date} · ${time}`;
    }

    return `${race.name} — ${date}`;
};

const formatAmount = (
    amount: string | number | null,
): string => {
    if (
        amount === null ||
        amount === undefined ||
        amount === ''
    ) {
        return '—';
    }

    const numericAmount = Number(amount);

    if (
        Number.isNaN(
            numericAmount,
        )
    ) {
        return String(amount);
    }

    return new Intl.NumberFormat(
        'es-MX',
        {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2,
        },
    ).format(numericAmount);
};

const getSponsorTypeLabel = (
    type: string,
): string => {
    const labels: Record<string, string> = {
        sponsor: 'Patrocinador',
        principal: 'Principal',
        official: 'Oficial',
        gold: 'Oro',
        silver: 'Plata',
        bronze: 'Bronce',
    };

    return (
        labels[type.toLowerCase()] ??
        type
    );
};

const getRaceLocation = (
    race: Race,
): string => {
    return (
        race.location ??
        'Ubicación no especificada'
    );
};

const getRaceShowUrl = (
    raceId: number,
): string => {
    return `/admin/races/${raceId}`;
};

const getRaceSponsorShowUrl = (
    sponsorId: number,
    raceSponsorId: number,
): string => {
    return `/admin/sponsors/${sponsorId}/races/${raceSponsorId}`;
};

const getRaceSponsorDestroyUrl = (
    sponsorId: number,
    raceSponsorId: number,
): string => {
    return `/admin/sponsors/${sponsorId}/races/${raceSponsorId}`;
};

const deleteRaceSponsor = async (
    raceSponsor: RaceSponsor,
): Promise<void> => {
    const raceName =
        raceSponsor.race?.name ??
        'esta carrera';

    const result = await Swal.fire({
        title: '¿Eliminar asociación?',
        text:
            `Se eliminará la asociación de ` +
            `${props.sponsor.name} con ${raceName}.`,
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
        getRaceSponsorDestroyUrl(
            props.sponsor.id,
            raceSponsor.id,
        ),
        {
            preserveScroll: true,

        },
    );
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
            {
                title: 'Detalle',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head
        :title="`Patrocinador: ${sponsor.name}`"
    />

    <div class="admin-page">
        <header class="admin-page-header">
            <div>
                <div class="admin-page-eyebrow">
                    Patrocinadores
                </div>

                <h1 class="admin-page-title">
                    {{ sponsor.name }}
                </h1>

                <p class="admin-page-subtitle">
                    Consulta la información y las carreras asociadas
                    a este patrocinador.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.sponsors.index().url"
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
                    :href="sponsors.edit(sponsor.id).url"
                    class="admin-btn admin-btn-secondary"
                >
                    <Edit
                        :size="16"
                        :stroke-width="2"
                        class="admin-btn-icon"
                    />

                    <span>Editar</span>
                </Link>

                <button
                    type="button"
                    class="admin-btn admin-btn-primary"
                    @click="openAssociationModal"
                >
                    <Plus
                        :size="16"
                        :stroke-width="2"
                        class="admin-btn-icon"
                    />

                    <span>
                        Asociar a carrera
                    </span>
                </button>
            </div>
        </header>

        <div class="admin-show-grid">
            <section
                class="admin-show-card admin-show-main"
            >
                <div class="admin-show-card-header">
                    <div class="admin-show-section-icon">
                        <Building2
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div>
                        <h2 class="admin-show-card-title">
                            Información del patrocinador
                        </h2>

                        <p class="admin-show-card-description">
                            Datos generales del patrocinador.
                        </p>
                    </div>
                </div>

                <div class="admin-show-profile">
                    <div class="admin-show-logo">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            :alt="`Logo de ${sponsor.name}`"
                        />

                        <Building2
                            v-else
                            :size="34"
                            :stroke-width="1.6"
                        />
                    </div>

                    <div class="admin-show-profile-info">
                        <h3>
                            {{ sponsor.name }}
                        </h3>

                        <span class="admin-show-slug">
                            {{ sponsor.slug }}
                        </span>

                        <span
                            :class="[
                                'status-badge',
                                sponsor.is_active
                                    ? 'status-active'
                                    : 'status-inactive',
                            ]"
                        >
                            <CheckCircle2
                                v-if="sponsor.is_active"
                                :size="13"
                                :stroke-width="2"
                            />

                            <XCircle
                                v-else
                                :size="13"
                                :stroke-width="2"
                            />

                            {{
                                sponsor.is_active
                                    ? 'Activo'
                                    : 'Inactivo'
                            }}
                        </span>
                    </div>
                </div>

                <div
                    v-if="sponsor.description"
                    class="admin-show-description"
                >
                    <h3 class="admin-show-label">
                        Descripción
                    </h3>

                    <p>
                        {{ sponsor.description }}
                    </p>
                </div>

                <div class="admin-show-details-grid">
                    <div class="admin-show-detail">
                        <span class="admin-show-detail-label">
                            Identificador
                        </span>

                        <span class="admin-show-detail-value">
                            {{ sponsor.slug }}
                        </span>
                    </div>

                    <div class="admin-show-detail">
                        <span class="admin-show-detail-label">
                            Estado
                        </span>

                        <span
                            :class="[
                                'status-badge',
                                sponsor.is_active
                                    ? 'status-active'
                                    : 'status-inactive',
                            ]"
                        >
                            {{
                                sponsor.is_active
                                    ? 'Activo'
                                    : 'Inactivo'
                            }}
                        </span>
                    </div>

                    <div
                        v-if="sponsor.created_at"
                        class="admin-show-detail"
                    >
                        <span class="admin-show-detail-label">
                            Registrado
                        </span>

                        <span class="admin-show-detail-value">
                            {{
                                formatDate(
                                    sponsor.created_at,
                                )
                            }}
                        </span>
                    </div>

                    <div
                        v-if="sponsor.updated_at"
                        class="admin-show-detail"
                    >
                        <span class="admin-show-detail-label">
                            Última actualización
                        </span>

                        <span class="admin-show-detail-value">
                            {{
                                formatDate(
                                    sponsor.updated_at,
                                )
                            }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="admin-show-card">
                <div class="admin-show-card-header">
                    <div class="admin-show-section-icon">
                        <UserRound
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div>
                        <h2 class="admin-show-card-title">
                            Contacto
                        </h2>

                        <p class="admin-show-card-description">
                            Información para contactar al patrocinador.
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
                                Persona de contacto
                            </span>

                            <span
                                v-if="sponsor.contact_name"
                                class="admin-contact-value"
                            >
                                {{ sponsor.contact_name }}
                            </span>

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
                                v-if="sponsor.email"
                                :href="`mailto:${sponsor.email}`"
                                class="admin-contact-value admin-contact-link"
                            >
                                {{ sponsor.email }}
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
                                v-if="sponsor.phone"
                                :href="`tel:${sponsor.phone}`"
                                class="admin-contact-value admin-contact-link"
                            >
                                {{ sponsor.phone }}
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
                            <Globe
                                :size="15"
                                :stroke-width="2"
                            />
                        </div>

                        <div class="admin-contact-content">
                            <span class="admin-contact-label">
                                Sitio web
                            </span>

                            <a
                                v-if="sponsor.website"
                                :href="sponsor.website"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="admin-contact-value admin-contact-link admin-contact-website"
                            >
                                <span>
                                    {{
                                        formatWebsite(
                                            sponsor.website,
                                        )
                                    }}
                                </span>

                                <ExternalLink
                                    :size="13"
                                    :stroke-width="2"
                                />
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

        <section
            class="admin-show-card admin-races-card"
        >
            <div
                class="admin-show-card-header admin-races-header"
            >
                <div class="admin-show-section-icon">
                    <Trophy
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <h2 class="admin-show-card-title">
                        Carreras asociadas
                    </h2>

                    <p class="admin-show-card-description">
                        Carreras en las que participa este patrocinador.
                    </p>
                </div>

                <div class="admin-races-header-actions">
                    <div class="admin-races-count">
                        {{
                            sponsor.race_sponsors?.length ??
                            0
                        }}

                        {{
                            (
                                sponsor.race_sponsors?.length ??
                                0
                            ) === 1
                                ? 'carrera'
                                : 'carreras'
                        }}
                    </div>
                </div>
            </div>

            <div
                v-if="sponsor.race_sponsors?.length"
                class="admin-races-list"
            >
                <div
                    v-for="raceSponsor in sponsor.race_sponsors"
                    :key="raceSponsor.id"
                    class="admin-race-item"
                >
                    <div class="admin-race-icon">
                        <Trophy
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="admin-race-info">
                        <template
                            v-if="raceSponsor.race"
                        >
                            <Link
                                :href="
                                    getRaceShowUrl(
                                        raceSponsor.race.id,
                                    )
                                "
                                class="admin-race-name"
                            >
                                {{ raceSponsor.race.name }}
                            </Link>

                            <div class="admin-race-meta">
                                <span
                                    v-if="
                                        raceSponsor.race.event_date
                                    "
                                >
                                    <CalendarDays
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    {{
                                        formatRaceDate(
                                            raceSponsor.race,
                                        )
                                    }}
                                </span>

                                <span
                                    v-if="
                                        getRaceTime(
                                            raceSponsor.race,
                                        )
                                    "
                                >
                                    {{
                                        getRaceTime(
                                            raceSponsor.race,
                                        )
                                    }}
                                </span>

                                <span>
                                    <MapPin
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    {{
                                        getRaceLocation(
                                            raceSponsor.race,
                                        )
                                    }}
                                </span>

                                <span
                                    v-if="
                                        raceSponsor.amount !==
                                            null &&
                                        raceSponsor.amount !==
                                            undefined
                                    "
                                    class="admin-race-amount"
                                >
                                    {{
                                        formatAmount(
                                            raceSponsor.amount,
                                        )
                                    }}
                                </span>
                            </div>
                        </template>

                        <span
                            v-else
                            class="admin-race-name admin-race-missing"
                        >
                            Carrera no disponible
                        </span>
                    </div>

                    <div class="admin-race-right">
                        <span class="race-type-badge">
                            {{
                                getSponsorTypeLabel(
                                    raceSponsor.type,
                                )
                            }}
                        </span>

                        <span
                            :class="[
                                'status-badge',
                                raceSponsor.is_active
                                    ? 'status-active'
                                    : 'status-inactive',
                            ]"
                        >
                            {{
                                raceSponsor.is_active
                                    ? 'Activo'
                                    : 'Inactivo'
                            }}
                        </span>

                        <div class="admin-race-actions">
                            <Link
                                :href="
                                    getRaceSponsorShowUrl(
                                        sponsor.id,
                                        raceSponsor.id,
                                    )
                                "
                                class="action-btn action-btn-view"
                            >
                                <Eye
                                    :size="14"
                                    :stroke-width="2"
                                />

                                <span>
                                    Ver patrocinio
                                </span>
                            </Link>

                            <button
                                type="button"
                                class="action-btn action-btn-delete"
                                @click="
                                    deleteRaceSponsor(
                                        raceSponsor,
                                    )
                                "
                            >
                                <Trash2
                                    :size="14"
                                    :stroke-width="2"
                                />

                                <span>
                                    Eliminar
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="admin-races-empty"
            >
                <div
                    class="admin-races-empty-icon"
                >
                    <Trophy
                        :size="22"
                        :stroke-width="1.7"
                    />
                </div>

                <h3>
                    Sin carreras asociadas
                </h3>

                <p>
                    Este patrocinador todavía no está asociado a ninguna
                    carrera.
                </p>
            </div>
        </section>

        <!-- MODAL ASOCIAR CARRERA -->
        <Teleport to="body">
            <div
                v-if="showAssociationModal"
                class="admin-modal-overlay"
                @click.self="closeAssociationModal"
            >
                <div
                    class="admin-modal"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="association-modal-title"
                >
                    <div class="admin-modal-header">
                        <div>
                            <span class="admin-modal-eyebrow">
                                Patrocinadores
                            </span>

                            <h2
                                id="association-modal-title"
                                class="admin-modal-title"
                            >
                                Asociar a carrera
                            </h2>

                            <p class="admin-modal-subtitle">
                                Asocia
                                <strong>{{ sponsor.name }}</strong>
                                a una carrera y define las condiciones
                                del patrocinio.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="admin-modal-close"
                            :disabled="form.processing"
                            @click="closeAssociationModal"
                        >
                            <X
                                :size="18"
                                :stroke-width="2"
                            />
                        </button>
                    </div>

                    <form
                        class="admin-modal-form"
                        @submit.prevent="submitAssociation"
                    >
                        <div class="admin-form-section">
                            <div class="admin-form-section-header">
                                <div class="admin-form-section-icon">
                                    <Building2
                                        :size="17"
                                        :stroke-width="2"
                                    />
                                </div>

                                <div>
                                    <h3 class="admin-form-section-title">
                                        Datos del patrocinio
                                    </h3>

                                    <p class="admin-form-section-description">
                                        Define la carrera y el monto acordado.
                                    </p>
                                </div>
                            </div>

                            <div class="admin-form-grid">
                                <div
                                    class="admin-form-group admin-form-group-full"
                                >
                                    <label
                                        for="modal_race_id"
                                        class="admin-form-label"
                                    >
                                        Carrera
                                    </label>

                                    <select
                                        id="modal_race_id"
                                        v-model="form.race_id"
                                        class="admin-form-input"
                                        :class="{
                                            'has-error':
                                                form.errors.race_id,
                                        }"
                                        :disabled="
                                            form.processing ||
                                            !races.length
                                        "
                                    >
                                        <option value="">
                                            {{
                                                races.length
                                                    ? 'Selecciona una carrera'
                                                    : 'No hay carreras disponibles'
                                            }}
                                        </option>

                                        <option
                                            v-for="race in races"
                                            :key="race.id"
                                            :value="race.id"
                                        >
                                            {{
                                                formatRaceOption(
                                                    race,
                                                )
                                            }}
                                        </option>
                                    </select>

                                    <p
                                        v-if="form.errors.race_id"
                                        class="admin-form-error"
                                    >
                                        {{ form.errors.race_id }}
                                    </p>
                                </div>

                                <div class="admin-form-group">
                                    <label
                                        for="modal_type"
                                        class="admin-form-label"
                                    >
                                        Tipo de patrocinio
                                    </label>

                                    <select
                                        id="modal_type"
                                        v-model="form.type"
                                        class="admin-form-input"
                                        :disabled="form.processing"
                                    >
                                        <option value="sponsor">
                                            Patrocinador
                                        </option>

                                        <option value="principal">
                                            Principal
                                        </option>

                                        <option value="official">
                                            Oficial
                                        </option>

                                        <option value="gold">
                                            Oro
                                        </option>

                                        <option value="silver">
                                            Plata
                                        </option>

                                        <option value="bronze">
                                            Bronce
                                        </option>
                                    </select>

                                    <p
                                        v-if="form.errors.type"
                                        class="admin-form-error"
                                    >
                                        {{ form.errors.type }}
                                    </p>
                                </div>

                                <div class="admin-form-group">
                                    <label
                                        for="modal_amount"
                                        class="admin-form-label"
                                    >
                                        Monto acordado
                                    </label>

                                    <input
                                        id="modal_amount"
                                        v-model="form.amount"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="admin-form-input"
                                        :class="{
                                            'has-error':
                                                form.errors.amount,
                                        }"
                                        placeholder="25000.00"
                                        :disabled="form.processing"
                                    />

                                    <p
                                        v-if="form.errors.amount"
                                        class="admin-form-error"
                                    >
                                        {{ form.errors.amount }}
                                    </p>
                                </div>

                                <div
                                    class="admin-form-group admin-form-group-full"
                                >
                                    <label
                                        for="modal_benefits"
                                        class="admin-form-label"
                                    >
                                        Beneficios / acuerdo
                                    </label>

                                    <textarea
                                        id="modal_benefits"
                                        v-model="form.benefits"
                                        class="admin-form-textarea"
                                        rows="5"
                                        placeholder="Describe los beneficios, presencia de marca o condiciones acordadas..."
                                        :disabled="form.processing"
                                    ></textarea>

                                    <p
                                        v-if="form.errors.benefits"
                                        class="admin-form-error"
                                    >
                                        {{ form.errors.benefits }}
                                    </p>
                                </div>

                                <div
                                    class="admin-form-group admin-form-group-full"
                                >
                                    <label class="admin-form-label">
                                        Estado
                                    </label>

                                    <label class="admin-switch">
                                        <input
                                            v-model="form.is_active"
                                            type="checkbox"
                                            :disabled="form.processing"
                                        />

                                        <span
                                            class="admin-switch-slider"
                                        ></span>

                                        <span
                                            class="admin-switch-text"
                                        >
                                            Patrocinio activo
                                        </span>
                                    </label>

                                    <p
                                        v-if="form.errors.is_active"
                                        class="admin-form-error"
                                    >
                                        {{ form.errors.is_active }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="admin-modal-actions">
                            <button
                                type="button"
                                class="admin-btn admin-btn-secondary"
                                :disabled="form.processing"
                                @click="closeAssociationModal"
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="admin-btn admin-btn-primary"
                                :disabled="
                                    form.processing ||
                                    !races.length
                                "
                            >
                                <Check
                                    v-if="!form.processing"
                                    :size="16"
                        
                                />

                                <span>
                                    {{
                                        form.processing
                                            ? 'Guardando...'
                                            : 'Asociar carrera'
                                    }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
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

.admin-btn:hover:not(:disabled) {
    transform: translateY(-1px);
}

.admin-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
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

.admin-btn-primary:hover:not(:disabled) {
    box-shadow: 0 6px 14px rgba(36, 158, 219, 0.18);
}

.admin-btn-secondary {
    border-color: var(--sc-page-border);
    background: #ffffff;
    color: var(--sc-page-text);
}

.admin-btn-secondary:hover:not(:disabled) {
    background: #fbfcfd;
    border-color: #d3dbe4;
}

.admin-btn-small {
    min-height: 34px;
    padding: 0 11px;
    border-radius: 8px;
    font-size: 11px;
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

.admin-show-logo img {
    display: block;
    width: 100%;
    height: 100%;
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
}

.admin-show-slug {
    margin-top: 4px;
    color: var(--sc-page-muted);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11px;
    line-height: 1.4;
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
    line-height: 1;
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
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 550;
    text-overflow: ellipsis;
    white-space: nowrap;
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
    word-break: break-word;
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

.admin-contact-website {
    max-width: 100%;
}

.admin-races-card {
    overflow: hidden;
}

.admin-races-header {
    align-items: center;
    padding-bottom: 20px;
}

.admin-races-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}

.admin-races-count {
    padding: 5px 9px;
    border: 1px solid var(--sc-page-border);
    border-radius: 999px;
    background: #fbfcfd;
    color: var(--sc-page-text-secondary);
    font-size: 10px;
    font-weight: 650;
    white-space: nowrap;
}

.admin-races-list {
    border-top: 1px solid var(--sc-page-border-soft);
}

.admin-race-item {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 15px 22px;
}

.admin-race-item + .admin-race-item {
    border-top: 1px solid var(--sc-page-border-soft);
}

.admin-race-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    border-radius: 9px;
    background: #fff7e7;
    color: #c28a1c;
}

.admin-race-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
}

.admin-race-name {
    width: fit-content;
    max-width: 100%;
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
    line-height: 1.4;
    text-decoration: none;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.admin-race-name:hover {
    color: var(--sc-page-blue-dark);
}

.admin-race-missing {
    color: var(--sc-page-muted);
}

.admin-race-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 4px;
}

.admin-race-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--sc-page-text-secondary);
    font-size: 10px;
    line-height: 1.4;
}

.admin-race-amount {
    color: var(--sc-page-text) !important;
    font-weight: 650;
}

.admin-race-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.race-type-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 24px;
    padding: 0 8px;
    border: 1px solid #dfe7ee;
    border-radius: 7px;
    background: #f8fafc;
    color: var(--sc-page-text-secondary);
    font-size: 10px;
    font-weight: 600;
}

.admin-race-right .status-badge {
    margin-top: 0;
}

.admin-race-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-left: 2px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    min-height: 32px;
    padding: 0 9px;
    border: 1px solid transparent;
    border-radius: 8px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    box-sizing: border-box;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        transform 0.15s ease;
}

.action-btn:hover {
    transform: translateY(-1px);
}

.action-btn-view {
    border-color: #d9e8f2;
    background: #f5faff;
    color: var(--sc-page-blue-dark);
}

.action-btn-view:hover {
    background: var(--sc-page-blue-light);
    border-color: #b9d8eb;
}

.action-btn-delete {
    border-color: #f0d4d7;
    background: #fff8f8;
    color: #c73542;
}

.action-btn-delete:hover {
    background: var(--sc-page-red-light);
    border-color: #e9b9bf;
}

.admin-races-empty {
    display: flex;
    align-items: center;
    flex-direction: column;
    padding: 42px 20px;
    border-top: 1px solid var(--sc-page-border-soft);
    text-align: center;
}

.admin-races-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin-bottom: 11px;
    border-radius: 12px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue-dark);
}

.admin-races-empty h3 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.admin-races-empty p {
    max-width: 420px;
    margin: 5px 0 14px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.5;
}

/* =========================================================
   FORMULARIO DEL MODAL
   ========================================================= */

.admin-form-section {
    width: 100%;
}

.admin-form-section-header {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    margin-bottom: 22px;
}

.admin-form-section-icon {
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

.admin-form-section-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 15px;
    font-weight: 700;
}

.admin-form-section-description {
    margin: 4px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 18px;
}

.admin-form-group {
    margin-bottom: 21px;
}

.admin-form-group-full {
    grid-column: 1 / -1;
}

.admin-form-label {
    display: block;
    margin-bottom: 7px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.admin-form-input,
.admin-form-textarea {
    display: block;
    width: 100%;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    outline: none;
    background: #fbfcfd;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 13px;
    box-sizing: border-box;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease,
        box-shadow 0.2s ease;
}

.admin-form-input {
    height: 42px;
    padding: 0 13px;
}

.admin-form-textarea {
    min-height: 110px;
    padding: 11px 13px;
    resize: vertical;
    line-height: 1.5;
}

.admin-form-input:focus,
.admin-form-textarea:focus {
    border-color: #a9d9f2;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.admin-form-input.has-error,
.admin-form-textarea.has-error {
    border-color: #efb8bf;
    background: var(--sc-page-red-light);
}

.admin-form-error {
    margin: 5px 0 0;
    color: var(--sc-page-red);
    font-size: 11px;
}

/* SWITCH */

.admin-switch {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    cursor: pointer;
    user-select: none;
}

.admin-switch input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

.admin-switch-slider {
    position: relative;
    display: block;
    width: 42px;
    height: 24px;
    flex: 0 0 42px;
    border: 1px solid #b8c4d1;
    border-radius: 999px;
    background: #cbd5e1;
    box-sizing: border-box;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.admin-switch-slider::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow:
        0 1px 3px rgba(15, 23, 42, 0.22),
        0 1px 2px rgba(15, 23, 42, 0.12);
    transition: transform 0.2s ease;
}

.admin-switch input:checked + .admin-switch-slider {
    background: var(--sc-page-blue);
    border-color: var(--sc-page-blue);
}

.admin-switch input:checked + .admin-switch-slider::after {
    transform: translateX(18px);
}

.admin-switch-text {
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    font-weight: 500;
}

.admin-switch input:checked ~ .admin-switch-text {
    color: var(--sc-page-blue-dark);
    font-weight: 600;
}

/* =========================================================
   MODAL
   ========================================================= */

.admin-modal-overlay {
    position: fixed;
    z-index: 9999;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(15, 23, 42, 0.48);
    box-sizing: border-box;
    overflow-y: auto;
}

.admin-modal {
    width: min(720px, 100%);
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    border: 1px solid var(--sc-page-border);
    border-radius: 16px;
    background: #ffffff;
    box-shadow:
        0 24px 60px rgba(15, 23, 42, 0.18),
        0 6px 20px rgba(15, 23, 42, 0.08);
    animation: admin-modal-in 0.18s ease-out;
}

@keyframes admin-modal-in {
    from {
        opacity: 0;
        transform: translateY(8px) scale(0.985);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.admin-modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    padding: 22px 24px 18px;
    border-bottom: 1px solid var(--sc-page-border-soft);
}

.admin-modal-eyebrow {
    display: block;
    margin-bottom: 4px;
    color: var(--sc-page-blue);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.admin-modal-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 19px;
    font-weight: 700;
    line-height: 1.3;
}

.admin-modal-subtitle {
    max-width: 580px;
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.5;
}

.admin-modal-subtitle strong {
    color: var(--sc-page-text);
    font-weight: 650;
}

.admin-modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid var(--sc-page-border);
    border-radius: 9px;
    background: #ffffff;
    color: var(--sc-page-text-secondary);
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        color 0.2s ease;
}

.admin-modal-close:hover:not(:disabled) {
    border-color: #d3dbe4;
    background: #f8fafc;
    color: var(--sc-page-text);
}

.admin-modal-close:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.admin-modal-form {
    padding: 24px;
}

.admin-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    padding-top: 18px;
    border-top: 1px solid var(--sc-page-border-soft);
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1100px) {
    .admin-race-item {
        align-items: flex-start;
    }

    .admin-race-right {
        align-items: flex-end;
        flex-direction: column;
    }

    .admin-race-actions {
        margin-left: 0;
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

    .admin-races-header {
        align-items: flex-start;
    }

    .admin-races-header-actions {
        align-items: flex-end;
        flex-direction: column;
    }

    .admin-form-grid {
        grid-template-columns: 1fr;
    }

    .admin-form-group-full {
        grid-column: auto;
    }

    .admin-modal-overlay {
        align-items: flex-start;
        padding: 14px;
    }

    .admin-modal {
        max-height: calc(100vh - 28px);
    }

    .admin-modal-header {
        padding: 18px;
    }

    .admin-modal-form {
        padding: 18px;
    }

    .admin-modal-actions {
        flex-direction: column-reverse;
    }

    .admin-modal-actions .admin-btn {
        width: 100%;
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

    .admin-races-header {
        padding-bottom: 18px;
    }

    .admin-races-header-actions {
        width: 100%;
        align-items: stretch;
        flex-direction: column;
        margin-left: 0;
    }

    .admin-races-header-actions .admin-btn {
        width: 100%;
    }

    .admin-race-item {
        flex-wrap: wrap;
        padding: 14px 18px;
    }

    .admin-race-info {
        width: calc(100% - 49px);
    }

    .admin-race-right {
        width: 100%;
        align-items: flex-start;
        flex-direction: column;
        padding-left: 49px;
    }

    .admin-race-actions {
        width: 100%;
        flex-direction: column;
    }

    .action-btn {
        width: 100%;
    }
}
</style>