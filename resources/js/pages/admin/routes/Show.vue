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

const getMediaUrl = (
    file: string,
): string => {
    if (!file) {
        return '';
    }

    const cleanFile = file
        .replace(/^\/+/, '')
        .replace(/^storage\//, '');

    return `/storage/${cleanFile}`;
};

const getFileExtension = (
    file: string,
): string => {
    return (
        file
            .split('?')[0]
            .split('#')[0]
            .split('.')
            .pop()
            ?.toLowerCase() ?? ''
    );
};

const isVideo = (
    media: RouteMedia,
): boolean => {
    const extension = getFileExtension(media.file);

    return [
        'mp4',
        'webm',
        'mov',
        'm4v',
        'ogg',
    ].includes(extension);
};

const isImage = (
    media: RouteMedia,
): boolean => {
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
    <Head :title="`Ruta: ${route.title}`" />

    <div class="admin-page">
        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Contenido
                </p>

                <h1 class="admin-page-title">
                    {{ route.title }}
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
                        :size="14"
                        :stroke-width="2"
                    />

                    Regresar
                </Link>

                <Link
                    :href="admin.routes.edit(route.id).url"
                    class="admin-btn admin-btn-primary"
                >
                    <Edit
                        :size="14"
                        :stroke-width="2"
                    />

                    Editar
                </Link>
            </div>
        </header>

        <section class="admin-show-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Información
                    </p>

                    <h2 class="show-card-title">
                        Datos de la ruta
                    </h2>

                    <p class="show-card-description">
                        Información general y estado de publicación.
                    </p>
                </div>

                <div class="show-card-icon">
                    <ImagePlus
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <div class="info-grid">
                    <div class="info-field info-field-full">
                        <span class="info-label">
                            Título
                        </span>

                        <div class="info-value info-value-title">
                            {{ route.title }}
                        </div>
                    </div>

                    <div class="info-field info-field-full">
                        <span class="info-label">
                            Descripción
                        </span>

                        <div
                            v-if="route.description"
                            class="info-value info-description"
                        >
                            {{ route.description }}
                        </div>

                        <div
                            v-else
                            class="info-empty"
                        >
                            Sin descripción.
                        </div>
                    </div>

                    <div class="info-field">
                        <span class="info-label">
                            Estado
                        </span>

                        <span
                            class="status-badge"
                            :class="{
                                'status-active':
                                    route.is_active,
                                'status-inactive':
                                    !route.is_active,
                            }"
                        >
                            <span class="status-dot"></span>

                            {{
                                route.is_active
                                    ? 'Activa'
                                    : 'Inactiva'
                            }}
                        </span>
                    </div>

                    <div class="info-field">
                        <span class="info-label">
                            Orden
                        </span>

                        <span class="order-badge">
                            {{ route.sort_order }}
                        </span>
                    </div>

                    <div class="info-field">
                        <span class="info-label">
                            Fotos
                        </span>

                        <div class="stat-value">
                            <FileImage
                                :size="16"
                                :stroke-width="2"
                            />

                            {{ getImageCount() }}
                        </div>
                    </div>

                    <div class="info-field">
                        <span class="info-label">
                            Videos
                        </span>

                        <div class="stat-value">
                            <Video
                                :size="16"
                                :stroke-width="2"
                            />

                            {{ getVideoCount() }}
                        </div>
                    </div>

                    <div class="info-field">
                        <span class="info-label">
                            Total de archivos
                        </span>

                        <div class="stat-value stat-value-total">
                            {{ getMediaCount() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-show-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Multimedia
                    </p>

                    <h2 class="show-card-title">
                        Fotos y videos
                    </h2>

                    <p class="show-card-description">
                        Contenido multimedia asociado a esta ruta.
                    </p>
                </div>

                <div class="show-card-icon">
                    <Video
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <div
                    v-if="!route.media?.length"
                    class="empty-media"
                >
                    <div class="empty-media-icon">
                        <ImagePlus
                            :size="22"
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

                <div
                    v-else
                    class="media-grid"
                >
                    <div
                        v-for="media in route.media"
                        :key="media.id"
                        class="media-card"
                    >
                        <div class="media-preview">
                            <!-- IMAGEN -->
                            <template
                                v-if="isImage(media)"
                            >
                                <img
                                    :src="getMediaUrl(media.file)"
                                    :alt="
                                        media.title ||
                                        `Imagen de ${route.title}`
                                    "
                                    class="media-image"
                                />
                            </template>

                            <!-- VIDEO -->
                            <template
                                v-else-if="isVideo(media)"
                            >
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
                            <div class="media-type">
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
                            </div>

                            <p
                                v-if="media.title"
                                class="media-title"
                            >
                                {{ media.title }}
                            </p>

                            <p class="media-order">
                                Orden {{ media.sort_order }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.admin-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.admin-page-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.admin-show-card {
    width: 100%;
    overflow: hidden;
}

.show-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 21px 24px;
    border-bottom: 1px solid var(--sc-page-border);
}

.show-card-eyebrow {
    margin: 0 0 5px;
    color: #91a1ac;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.show-card-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
}

.show-card-description {
    margin: 6px 0 0;
    color: #8999a4;
    font-size: 12px;
    line-height: 1.55;
}

.show-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid #dce9ef;
    border-radius: 9px;
    background: #f5f9fb;
    color: #7592a3;
}

.show-card-body {
    padding: 23px 24px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
}

.info-field {
    min-width: 0;
}

.info-field-full {
    grid-column: 1 / -1;
}

.info-label {
    display: block;
    margin-bottom: 7px;
    color: #91a1ac;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.info-value {
    color: var(--sc-page-text);
    font-size: 13px;
    line-height: 1.55;
}

.info-value-title {
    font-size: 15px;
    font-weight: 700;
}

.info-description {
    max-width: 900px;
    color: #526574;
    white-space: pre-line;
}

.info-empty {
    color: #a3b0b8;
    font-size: 12px;
    font-style: italic;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 27px;
    padding: 0 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.status-active {
    background: #eaf8f3;
    color: #16815f;
}

.status-active .status-dot {
    background: #18b89a;
}

.status-inactive {
    background: #f1f4f6;
    color: #71808a;
}

.status-inactive .status-dot {
    background: #9aa8b0;
}

.order-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 28px;
    padding: 0 8px;
    border-radius: 8px;
    background: #eaf6fc;
    color: #1769a8;
    font-size: 12px;
    font-weight: 700;
}

.stat-value {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #1769a8;
    font-size: 14px;
    font-weight: 700;
}

.stat-value-total {
    min-width: 32px;
    height: 28px;
    justify-content: center;
    padding: 0 9px;
    border-radius: 8px;
    background: #eaf6fc;
}

.empty-media {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    padding: 30px;
    border: 1px dashed #d9e5eb;
    border-radius: 12px;
    background: #fafcfd;
    text-align: center;
}

.empty-media-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin-bottom: 12px;
    border-radius: 12px;
    background: #eaf6fc;
    color: #5f9fc1;
}

.empty-media strong {
    color: var(--sc-page-text);
    font-size: 13px;
}

.empty-media span {
    margin-top: 4px;
    color: #91a1ac;
    font-size: 11px;
}

.media-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.media-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid #dce7ec;
    border-radius: 12px;
    background: #ffffff;
}

.media-preview {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 230px;
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
    top: 9px;
    left: 9px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    min-height: 23px;
    padding: 0 7px;
    border-radius: 6px;
    background: rgba(23, 105, 168, 0.92);
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    pointer-events: none;
}

.unknown-media {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    height: 100%;
    color: #94a3b8;
}

.unknown-media span {
    font-size: 11px;
}

.media-info {
    padding: 11px 12px 12px;
}

.media-type {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #1769a8;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}

.media-title {
    margin: 6px 0 0;
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 600;
    line-height: 1.4;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.media-order {
    margin: 5px 0 0;
    color: #94a3b8;
    font-size: 10px;
}

@media (max-width: 900px) {
    .info-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .media-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .admin-page {
        gap: 16px;
    }

    .admin-page-header-actions {
        width: 100%;
    }

    .admin-page-header-actions .admin-btn {
        flex: 1;
        justify-content: center;
    }

    .show-card-header {
        padding: 18px;
    }

    .show-card-body {
        padding: 18px;
    }

    .info-grid {
        grid-template-columns: 1fr;
        gap: 17px;
    }

    .media-grid {
        grid-template-columns: 1fr;
    }

    .media-preview {
        height: 220px;
    }
}
</style>
