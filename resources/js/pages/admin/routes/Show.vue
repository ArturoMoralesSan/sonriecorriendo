<script setup lang="ts">
import {
    Head,
    Link,
} from '@inertiajs/vue3';

import {
    ArrowLeft,
    Edit,
    FileImage,
    ImagePlus,
    Play,
    Video,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

interface RouteMedia {
    id: number;
    route_id: number;
    type: 'image' | 'video';
    file: string;
    title: string | null;
    sort_order: number;
    created_at?: string | null;
}

interface RouteItem {
    id: number;
    title: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    media?: RouteMedia[];
}

const props = defineProps<{
    route: RouteItem;
}>();

const getMediaUrl = (file: string): string => {
    if (!file) {
        return '';
    }

    const cleanFile = file
        .replace(/^\/+/, '')
        .replace(/^storage\//, '');

    return `/storage/${cleanFile}`;
};

const getFileExtension = (file: string): string => {
    return (
        file
            .split('?')[0]
            .split('#')[0]
            .split('.')
            .pop()
            ?.toLowerCase() ?? ''
    );
};

const isVideo = (media: RouteMedia): boolean => {
    const extension = getFileExtension(media.file);

    return [
        'mp4',
        'webm',
        'mov',
        'm4v',
        'ogg',
    ].includes(extension);
};

const isImage = (media: RouteMedia): boolean => {
    const extension = getFileExtension(media.file);

    return [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif',
        'avif',
    ].includes(extension);
};

const getImageCount = (): number => {
    return (
        props.route.media?.filter(
            (media) => isImage(media),
        ).length ?? 0
    );
};

const getVideoCount = (): number => {
    return (
        props.route.media?.filter(
            (media) => isVideo(media),
        ).length ?? 0
    );
};

const getMediaCount = (): number => {
    return props.route.media?.length ?? 0;
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Rutas',
                href: admin.routes.index(),
            },
            {
                title: 'Detalle',
                href: admin.routes.index(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Ruta: ${props.route.title}`" />

    <div class="admin-page">
        <!-- ENCABEZADO -->
        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Contenido
                </p>

                <h1 class="admin-page-title">
                    {{ props.route.title }}
                </h1>

                <p class="admin-page-subtitle">
                    Consulta la información y el contenido
                    multimedia de esta ruta.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.routes.index().url"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft
                        :size="16"
                        :stroke-width="2"
                    />

                    Regresar
                </Link>

                <Link
                    :href="admin.routes.edit(props.route.id).url"
                    class="admin-btn admin-btn-secondary"
                >
                    <Edit
                        :size="16"
                        :stroke-width="2"
                    />

                    Editar
                </Link>
            </div>
        </header>

        <!-- INFORMACIÓN GENERAL -->
        <section class="admin-show-card">
            <div class="admin-show-card-header">
                <div class="admin-show-section-icon">
                    <ImagePlus
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <h2 class="admin-show-card-title">
                        Datos de la ruta
                    </h2>

                    <p class="admin-show-card-description">
                        Información general y estado de publicación.
                    </p>
                </div>
            </div>

            <div class="admin-show-card-body">
                <!-- PERFIL DE LA RUTA -->
                <div class="route-profile">
                    <div class="route-profile-icon">
                        <ImagePlus
                            :size="30"
                            :stroke-width="1.7"
                        />
                    </div>

                    <div class="route-profile-info">
                        <h3>
                            {{ props.route.title }}
                        </h3>

                        <span class="route-profile-subtitle">
                            Ruta de contenido
                        </span>

                        <span
                            :class="[
                                'status-badge',
                                props.route.is_active
                                    ? 'status-active'
                                    : 'status-inactive',
                            ]"
                        >
                            <span class="status-dot"></span>

                            {{
                                props.route.is_active
                                    ? 'Activa'
                                    : 'Inactiva'
                            }}
                        </span>
                    </div>
                </div>

                <!-- DESCRIPCIÓN -->
                <div class="route-description">
                    <h3 class="route-section-label">
                        Descripción
                    </h3>

                    <p v-if="props.route.description">
                        {{ props.route.description }}
                    </p>

                    <p
                        v-else
                        class="route-empty-text"
                    >
                        Esta ruta todavía no tiene una descripción.
                    </p>
                </div>

                <!-- DETALLES Y ESTADÍSTICAS -->
                <div class="route-details-grid">
                    <div class="route-detail">
                        <span class="route-detail-label">
                            Estado
                        </span>

                        <span
                            :class="[
                                'status-badge',
                                props.route.is_active
                                    ? 'status-active'
                                    : 'status-inactive',
                            ]"
                        >
                            <span class="status-dot"></span>

                            {{
                                props.route.is_active
                                    ? 'Activa'
                                    : 'Inactiva'
                            }}
                        </span>
                    </div>

                    <div class="route-detail">
                        <span class="route-detail-label">
                            Orden de visualización
                        </span>

                        <span class="order-badge">
                            {{ props.route.sort_order }}
                        </span>
                    </div>

                    <div class="route-detail">
                        <span class="route-detail-label">
                            Fotografías
                        </span>

                        <span class="route-stat-value">
                            <FileImage
                                :size="16"
                                :stroke-width="2"
                            />

                            {{ getImageCount() }}
                        </span>
                    </div>

                    <div class="route-detail">
                        <span class="route-detail-label">
                            Videos
                        </span>

                        <span class="route-stat-value">
                            <Video
                                :size="16"
                                :stroke-width="2"
                            />

                            {{ getVideoCount() }}
                        </span>
                    </div>

                    <div class="route-detail">
                        <span class="route-detail-label">
                            Total de archivos
                        </span>

                        <span class="route-total-badge">
                            {{ getMediaCount() }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- GALERÍA MULTIMEDIA -->
        <section class="admin-show-card">
            <div class="admin-show-card-header">
                <div class="admin-show-section-icon">
                    <Video
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <h2 class="admin-show-card-title">
                        Fotos y videos
                    </h2>

                    <p class="admin-show-card-description">
                        Contenido multimedia asociado a esta ruta.
                    </p>
                </div>

                <div class="media-header-count">
                    {{ getMediaCount() }}
                    {{ getMediaCount() === 1 ? 'archivo' : 'archivos' }}
                </div>
            </div>

            <div class="admin-show-card-body">
                <!-- SIN ARCHIVOS -->
                <div
                    v-if="!props.route.media?.length"
                    class="empty-media"
                >
                    <div class="empty-media-icon">
                        <ImagePlus
                            :size="25"
                            :stroke-width="1.8"
                        />
                    </div>

                    <strong>
                        Sin archivos multimedia
                    </strong>

                    <span>
                        Esta ruta todavía no tiene fotos o videos.
                    </span>
                </div>

                <!-- ARCHIVOS -->
                <div
                    v-else
                    class="media-grid"
                >
                    <article
                        v-for="media in props.route.media"
                        :key="media.id"
                        class="media-card"
                    >
                        <div class="media-preview">
                            <!-- IMAGEN -->
                            <template v-if="isImage(media)">
                                <img
                                    :src="getMediaUrl(media.file)"
                                    :alt="
                                        media.title ||
                                        `Imagen de ${props.route.title}`
                                    "
                                    class="media-image"
                                    loading="lazy"
                                />
                            </template>

                            <!-- VIDEO -->
                            <template v-else-if="isVideo(media)">
                                <video
                                    :src="getMediaUrl(media.file)"
                                    class="media-video"
                                    controls
                                    preload="metadata"
                                    playsinline
                                ></video>

                                <div class="video-badge">
                                    <Play
                                        :size="11"
                                        :stroke-width="2.5"
                                    />

                                    Video
                                </div>
                            </template>

                            <!-- ARCHIVO DESCONOCIDO -->
                            <template v-else>
                                <div class="unknown-media">
                                    <FileImage
                                        :size="28"
                                        :stroke-width="1.7"
                                    />

                                    <span>
                                        Formato no compatible
                                    </span>
                                </div>
                            </template>
                        </div>

                        <div class="media-info">
                            <div class="media-info-top">
                                <span class="media-type">
                                    <FileImage
                                        v-if="isImage(media)"
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    <Video
                                        v-else-if="isVideo(media)"
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    {{
                                        isVideo(media)
                                            ? 'Video'
                                            : isImage(media)
                                                ? 'Fotografía'
                                                : 'Archivo'
                                    }}
                                </span>

                                <span class="media-order">
                                    Orden {{ media.sort_order }}
                                </span>
                            </div>

                            <p
                                v-if="media.title"
                                class="media-title"
                                :title="media.title"
                            >
                                {{ media.title }}
                            </p>

                            <p
                                v-else
                                class="media-no-title"
                            >
                                Sin título
                            </p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.admin-page {
    display: flex;
    flex-direction: column;
    gap: 18px;
    width: 100%;
    min-width: 0;
    color: var(--sc-page-text);
}

.admin-page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 4px;
}

.admin-page-eyebrow {
    margin: 0 0 5px;
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
    line-height: 1.2;
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

.admin-btn-secondary {
    border-color: var(--sc-page-border);
    background: #ffffff;
    color: var(--sc-page-text);
}

.admin-btn-secondary:hover {
    border-color: #d3dbe4;
    background: #fbfcfd;
}

/* TARJETAS */

.admin-show-card {
    width: 100%;
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: var(--sc-page-radius);
    background: #ffffff;
    box-shadow:
        0 4px 15px rgba(27, 62, 90, 0.035),
        0 1px 3px rgba(27, 62, 90, 0.025);
    box-sizing: border-box;
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
    margin: 4px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.5;
}

.admin-show-card-body {
    min-width: 0;
    padding: 22px;
}

.media-header-count {
    margin-left: auto;
    padding: 6px 10px;
    border: 1px solid #dceaf2;
    border-radius: 8px;
    background: #f3f9fc;
    color: var(--sc-page-blue-dark);
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

/* PERFIL */

.route-profile {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 22px;
    padding: 16px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #fbfcfd;
}

.route-profile-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 76px;
    height: 76px;
    flex: 0 0 76px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #ffffff;
    color: var(--sc-page-blue-dark);
}

.route-profile-info {
    display: flex;
    align-items: flex-start;
    flex-direction: column;
    min-width: 0;
}

.route-profile-info h3 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 18px;
    font-weight: 700;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.route-profile-subtitle {
    margin-top: 4px;
    color: var(--sc-page-muted);
    font-size: 11px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: fit-content;
    min-height: 25px;
    margin-top: 9px;
    padding: 0 9px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 650;
    line-height: 1.3;
    white-space: nowrap;
}

.status-dot {
    width: 6px;
    height: 6px;
    flex: 0 0 6px;
    border-radius: 50%;
}

.status-active {
    background: var(--sc-page-green-light);
    color: #12927b;
}

.status-active .status-dot {
    background: #18b89a;
}

.status-inactive {
    background: var(--sc-page-red-light);
    color: #c73542;
}

.status-inactive .status-dot {
    background: #e05260;
}

/* DESCRIPCIÓN */

.route-description {
    margin-bottom: 22px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--sc-page-border-soft);
}

.route-section-label {
    margin: 0 0 8px;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 650;
}

.route-description p {
    margin: 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.7;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.route-description .route-empty-text {
    color: var(--sc-page-muted);
    font-style: italic;
}

/* DATOS Y ESTADÍSTICAS */

.route-details-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1px;
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 9px;
    background: var(--sc-page-border);
}

.route-detail {
    display: flex;
    align-items: flex-start;
    flex-direction: column;
    gap: 7px;
    min-width: 0;
    min-height: 75px;
    padding: 13px;
    background: #ffffff;
}

.route-detail-label {
    color: var(--sc-page-muted);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.025em;
    text-transform: uppercase;
}

.order-badge,
.route-total-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 28px;
    padding: 0 9px;
    border-radius: 8px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue-dark);
    font-size: 12px;
    font-weight: 700;
}

.route-stat-value {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--sc-page-blue-dark);
    font-size: 14px;
    font-weight: 700;
}

/* ESTADO VACÍO */

.empty-media {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    min-height: 230px;
    padding: 30px 20px;
    border: 1px dashed #d9e5eb;
    border-radius: 12px;
    background: #fafcfd;
    text-align: center;
}

.empty-media-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    margin-bottom: 13px;
    border-radius: 12px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue-dark);
}

.empty-media strong {
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
}

.empty-media span {
    margin-top: 5px;
    color: var(--sc-page-muted);
    font-size: 11px;
    line-height: 1.5;
}

/* GALERÍA */

.media-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.media-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 11px;
    background: #ffffff;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        transform 0.2s ease;
}

.media-card:hover {
    transform: translateY(-2px);
    border-color: #c9dfec;
    box-shadow: 0 6px 16px rgba(27, 62, 90, 0.07);
}

.media-preview {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 225px;
    overflow: hidden;
    background: #f2f6f8;
}

.media-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: #f2f6f8;
}

.media-video {
    display: block;
    width: 100%;
    height: 100%;
    background: #101820;
    object-fit: contain;
}

.video-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 24px;
    padding: 0 8px;
    border-radius: 6px;
    background: rgba(23, 105, 168, 0.94);
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    pointer-events: none;
}

.unknown-media {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 9px;
    width: 100%;
    height: 100%;
    color: #94a3b8;
}

.unknown-media span {
    font-size: 11px;
}

/* INFORMACIÓN DEL ARCHIVO */

.media-info {
    padding: 12px 13px 14px;
    border-top: 1px solid var(--sc-page-border-soft);
}

.media-info-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.media-type {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--sc-page-blue-dark);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}

.media-order {
    color: var(--sc-page-muted);
    font-size: 10px;
    white-space: nowrap;
}

.media-title {
    margin: 8px 0 0;
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 650;
    line-height: 1.45;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.media-no-title {
    margin: 8px 0 0;
    color: var(--sc-page-muted);
    font-size: 11px;
    font-style: italic;
}

/* RESPONSIVE */

@media (max-width: 1100px) {
    .media-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 900px) {
    .route-details-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .admin-page {
        gap: 16px;
    }

    .admin-page-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 16px;
    }

    .admin-page-header-actions {
        width: 100%;
    }

    .admin-page-header-actions .admin-btn {
        flex: 1;
    }

    .admin-show-card-header {
        padding: 18px 18px 0;
    }

    .admin-show-card-body {
        padding: 18px;
    }

    .route-profile {
        align-items: flex-start;
        margin-bottom: 18px;
    }

    .route-description {
        margin-bottom: 18px;
    }

    .media-preview {
        height: 205px;
    }
}

@media (max-width: 520px) {
    .admin-page-title {
        font-size: 24px;
    }

    .admin-page-header-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .admin-page-header-actions .admin-btn {
        flex: none;
        width: 100%;
    }

    .admin-show-card-header {
        gap: 9px;
    }

    .admin-show-card-description {
        max-width: 250px;
    }

    .media-header-count {
        padding: 5px 7px;
        font-size: 10px;
    }

    .route-profile {
        gap: 12px;
        padding: 12px;
    }

    .route-profile-icon {
        width: 58px;
        height: 58px;
        flex-basis: 58px;
    }

    .route-profile-info h3 {
        font-size: 15px;
    }

    .route-details-grid {
        grid-template-columns: 1fr;
    }

    .route-detail {
        min-height: auto;
        padding: 12px;
    }

    .media-grid {
        grid-template-columns: 1fr;
    }

    .media-preview {
        height: 230px;
    }
}
</style>