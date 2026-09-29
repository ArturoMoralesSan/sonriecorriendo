import type { ComputedRef, Ref } from 'vue';

import { computed, onMounted, ref } from 'vue';

import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

/**
 * El sitio utiliza exclusivamente el tema claro.
 *
 * No se consulta:
 * - prefers-color-scheme
 * - tema del sistema operativo
 * - tema del navegador
 *
 * La clase "dark" siempre se elimina del documento.
 */
export function updateTheme(_value: Appearance = 'light'): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.remove('dark');
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAppearance = (): Appearance | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

/**
 * Inicializa siempre el tema claro.
 *
 * Aunque exista una preferencia anterior guardada como "dark"
 * o "system", el sitio permanecerá en modo claro.
 */
export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    updateTheme('light');

    // Corregimos cualquier preferencia anterior para que
    // el sistema quede establecido permanentemente en light.
    localStorage.setItem('appearance', 'light');
    setCookie('appearance', 'light');
}

/**
 * Apariencia global de la aplicación.
 *
 * La interfaz queda permanentemente en modo claro.
 */
const appearance = ref<Appearance>('light');

export function useAppearance(): UseAppearanceReturn {
    onMounted(() => {
        // Ignoramos cualquier configuración anterior del usuario
        // y establecemos siempre light.
        appearance.value = 'light';

        localStorage.setItem('appearance', 'light');
        setCookie('appearance', 'light');

        updateTheme('light');
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() => {
        return 'light';
    });

    function updateAppearance(_value: Appearance) {
        // Independientemente del valor recibido (dark, light o system),
        // la aplicación siempre utiliza light.
        appearance.value = 'light';

        localStorage.setItem('appearance', 'light');
        setCookie('appearance', 'light');

        updateTheme('light');
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}