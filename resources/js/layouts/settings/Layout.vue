<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import {
    Palette,
    ShieldCheck,
    UserRound,
} from 'lucide-vue-next';

import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Perfil',
        href: editProfile(),
    },
    {
        title: 'Seguridad',
        href: editSecurity(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();

const icons = {
    Perfil: UserRound,
    Seguridad: ShieldCheck,
    Apariencia: Palette,
};
</script>

<template>
    <div
        class="min-h-full bg-[var(--sc-page-background)] px-4 py-6"
    >
        <!-- Encabezado -->

        <div class="mb-8">
            <p class="admin-page-eyebrow">
                Configuración
            </p>

            <h1 class="admin-page-title">
                Configuración
            </h1>

            <p class="admin-page-subtitle">
                Administra tu perfil y la configuración de tu cuenta.
            </p>
        </div>

        <!-- Contenido -->

        <div
            class="flex flex-col gap-8 lg:flex-row"
        >
            <!-- Navegación -->

            <aside
                class="w-full shrink-0 lg:w-56"
            >
                <nav
                    class="rounded-2xl border border-[var(--sc-page-border)] bg-white p-2 shadow-sm"
                    aria-label="Configuración"
                >
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        :href="item.href"
                        :class="[
                            'flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors',
                            isCurrentOrParentUrl(item.href)
                                ? 'bg-[var(--sc-page-blue-light)] text-[var(--sc-page-blue)]'
                                : 'text-[var(--sc-page-text-secondary)] hover:bg-[var(--sc-page-background)] hover:text-[var(--sc-page-text)]',
                        ]"
                    >
                        <component
                            :is="
                                icons[
                                    item.title as keyof typeof icons
                                ]
                            "
                            class="h-4 w-4 shrink-0"
                        />

                        <span>
                            {{ item.title }}
                        </span>
                    </Link>
                </nav>
            </aside>

            <!-- Página -->

            <main class="min-w-0 flex-1">
                <div class="w-full">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>