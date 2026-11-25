<script setup lang="ts">
/**
 * Selector de ubicación con Leaflet + OpenStreetMap (gratis, sin API key).
 *
 * - Clic en el mapa o arrastrar el marcador para fijar la ubicación.
 * - "Buscar dirección" geocodifica con Nominatim (OSM, gratuito).
 * - "Mi ubicación" usa la geolocalización del navegador.
 */
import 'leaflet/dist/leaflet.css';

import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import { Crosshair, LocateFixed, Search, Trash2 } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const latitude = defineModel<number | null>('latitude', { default: null });
const longitude = defineModel<number | null>('longitude', { default: null });

const props = withDefaults(
    defineProps<{
        /** Dirección para la búsqueda (se arma con los campos del formulario). */
        address?: string;
        readonly?: boolean;
        height?: string;
    }>(),
    {
        address: '',
        readonly: false,
        height: '360px',
    },
);

// Centro por defecto: Durango, Dgo.
const DEFAULT_CENTER: L.LatLngTuple = [24.0277, -104.6532];

const defaultIcon = L.icon({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41],
});

const mapEl = ref<HTMLDivElement | null>(null);
const searching = ref(false);
const message = ref<string | null>(null);

let map: L.Map | null = null;
let marker: L.Marker | null = null;

const round = (value: number): number => Math.round(value * 1e7) / 1e7;

const setPosition = (lat: number, lng: number, pan = true): void => {
    latitude.value = round(lat);
    longitude.value = round(lng);

    if (!map) {
        return;
    }

    if (!marker) {
        marker = L.marker([lat, lng], {
            icon: defaultIcon,
            draggable: !props.readonly,
        }).addTo(map);

        marker.on('dragend', () => {
            const pos = marker!.getLatLng();
            latitude.value = round(pos.lat);
            longitude.value = round(pos.lng);
        });
    } else {
        marker.setLatLng([lat, lng]);
    }

    if (pan) {
        map.setView([lat, lng], Math.max(map.getZoom(), 16));
    }
};

const clearPosition = (): void => {
    latitude.value = null;
    longitude.value = null;

    if (marker && map) {
        map.removeLayer(marker);
        marker = null;
    }
};

const searchAddress = async (): Promise<void> => {
    const query = props.address.trim();

    if (!query) {
        message.value = 'Captura la dirección para poder buscarla.';

        return;
    }

    searching.value = true;
    message.value = null;

    try {
        const url = new URL('https://nominatim.openstreetmap.org/search');
        url.searchParams.set('format', 'json');
        url.searchParams.set('limit', '1');
        url.searchParams.set('countrycodes', 'mx');
        url.searchParams.set('q', query);

        const response = await fetch(url.toString(), {
            headers: { 'Accept-Language': 'es' },
        });

        const results: Array<{ lat: string; lon: string }> =
            await response.json();

        if (!results.length) {
            message.value =
                'No se encontró la dirección. Ubica la sucursal manualmente haciendo clic en el mapa.';

            return;
        }

        setPosition(Number(results[0].lat), Number(results[0].lon));
        message.value =
            'Ubicación aproximada encontrada. Arrastra el marcador para ajustarla.';
    } catch {
        message.value =
            'No fue posible buscar la dirección. Selecciona la ubicación en el mapa.';
    } finally {
        searching.value = false;
    }
};

const useMyLocation = (): void => {
    if (!navigator.geolocation) {
        message.value = 'Tu navegador no permite obtener la ubicación.';

        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            setPosition(pos.coords.latitude, pos.coords.longitude);
            message.value = null;
        },
        () => {
            message.value = 'No se pudo obtener tu ubicación.';
        },
        { enableHighAccuracy: true, timeout: 10000 },
    );
};

onMounted(() => {
    if (!mapEl.value) {
        return;
    }

    const hasPoint = latitude.value !== null && longitude.value !== null;

    map = L.map(mapEl.value, {
        scrollWheelZoom: !props.readonly,
    }).setView(
        hasPoint
            ? [latitude.value as number, longitude.value as number]
            : DEFAULT_CENTER,
        hasPoint ? 16 : 13,
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    if (hasPoint) {
        setPosition(latitude.value as number, longitude.value as number, false);
    }

    if (!props.readonly) {
        map.on('click', (event: L.LeafletMouseEvent) => {
            setPosition(event.latlng.lat, event.latlng.lng, false);
        });
    }

    // Evita que el mapa se dibuje cortado si el contenedor cambia de tamaño.
    setTimeout(() => map?.invalidateSize(), 200);
});

watch([latitude, longitude], ([lat, lng]) => {
    if (lat === null || lng === null || !marker) {
        return;
    }

    const current = marker.getLatLng();

    if (current.lat !== lat || current.lng !== lng) {
        marker.setLatLng([lat, lng]);
    }
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
    marker = null;
});
</script>

<template>
    <div class="branch-map">
        <div v-if="!readonly" class="branch-map-toolbar">
            <button
                type="button"
                class="admin-btn admin-btn-secondary"
                :disabled="searching"
                @click="searchAddress"
            >
                <span v-if="searching" class="admin-btn-loader"></span>
                <Search v-else :size="14" :stroke-width="2" />
                Buscar dirección
            </button>

            <button
                type="button"
                class="admin-btn admin-btn-secondary"
                @click="useMyLocation"
            >
                <LocateFixed :size="14" :stroke-width="2" />
                Mi ubicación
            </button>

            <button
                v-if="latitude !== null && longitude !== null"
                type="button"
                class="admin-btn admin-btn-secondary"
                @click="clearPosition"
            >
                <Trash2 :size="14" :stroke-width="2" />
                Quitar
            </button>
        </div>

        <div ref="mapEl" class="branch-map-canvas" :style="{ height }"></div>

        <p v-if="message" class="admin-form-help branch-map-message">
            {{ message }}
        </p>

        <p v-if="!readonly" class="admin-form-help branch-map-coords">
            <Crosshair :size="12" :stroke-width="2" />
            <template v-if="latitude !== null && longitude !== null">
                {{ latitude }}, {{ longitude }}
            </template>
            <template v-else>
                Haz clic en el mapa para marcar la ubicación de la sucursal.
            </template>
        </p>
    </div>
</template>

<style scoped>
.branch-map {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.branch-map-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.branch-map-canvas {
    position: relative;
    z-index: 0;
    width: 100%;
    border: 1px solid #e8eef3;
    border-radius: 12px;
    overflow: hidden;
}

.branch-map-message,
.branch-map-coords {
    margin: 0;
}

.branch-map-coords {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
</style>
