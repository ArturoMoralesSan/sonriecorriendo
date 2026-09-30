import { createInertiaApp } from '@inertiajs/vue3';

import { initializeTheme } from '@/composables/useAppearance';

import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    layout: (name) => {
        switch (true) {
            // ==========================================
            // PÁGINAS PÚBLICAS
            // ==========================================
            case name === 'Welcome':
            case name === 'Races':
            case name === 'Gallery':
            case name === 'Shop':
            case name === 'RaceShow':
            case name === 'GalleryShow':
            case name === 'Clubs':
            case name === 'Cart':
            case name === 'ProductShow':
            case name === 'SaleShow':
            case name === 'ClubShow':
                return null;

            // ==========================================
            // AUTENTICACIÓN
            // ==========================================
            case name.startsWith('auth/'):
                return AuthLayout;

            // ==========================================
            // CONFIGURACIÓN
            // ==========================================
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];

            // ==========================================
            // ADMIN
            // ==========================================
            default:
                return AppLayout;
        }
    },

    progress: {
        color: '#249edb',
    },
});

initializeTheme();

initializeFlashToast();