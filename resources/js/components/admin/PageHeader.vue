<script setup lang="ts">
import type { Component } from 'vue';
import { Link } from '@inertiajs/vue3';

interface HeaderAction {
    label: string;
    href?: string;
    icon?: Component;
    onClick?: () => void;
    variant?: 'primary' | 'secondary';
}

defineProps<{
    eyebrow?: string;
    title: string;
    subtitle?: string;
    actions?: HeaderAction[];
}>();
</script>

<template>
    <header class="page-header">
        <div class="page-header__content">
            <p
                v-if="eyebrow"
                class="page-header__eyebrow"
            >
                {{ eyebrow }}
            </p>

            <h1 class="page-header__title">
                {{ title }}
            </h1>

            <p
                v-if="subtitle"
                class="page-header__subtitle"
            >
                {{ subtitle }}
            </p>
        </div>

        <div
            v-if="actions?.length"
            class="page-header__actions"
        >
            <template
                v-for="(action, index) in actions"
                :key="index"
            >
                <Link
                    v-if="action.href"
                    :href="action.href"
                    class="admin-btn"
                    :class="{
                        'admin-btn-primary':
                            action.variant !== 'secondary',
                        'admin-btn-secondary':
                            action.variant === 'secondary',
                    }"
                >
                    <span
                        v-if="action.icon"
                        class="admin-btn-icon"
                    >
                        <component
                            :is="action.icon"
                            :size="15"
                            :stroke-width="2"
                        />
                    </span>

                    {{ action.label }}
                </Link>

                <button
                    v-else
                    type="button"
                    class="admin-btn"
                    :class="{
                        'admin-btn-primary':
                            action.variant !== 'secondary',
                        'admin-btn-secondary':
                            action.variant === 'secondary',
                    }"
                    @click="action.onClick?.()"
                >
                    <span
                        v-if="action.icon"
                        class="admin-btn-icon"
                    >
                        <component
                            :is="action.icon"
                            :size="15"
                            :stroke-width="2"
                        />
                    </span>

                    {{ action.label }}
                </button>
            </template>
        </div>
    </header>
</template>

<style scoped>
.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;

    width: 100%;
    margin-bottom: 22px;
}

.page-header__content {
    min-width: 0;
}

.page-header__eyebrow {
    margin: 0 0 5px;

    color: var(--sc-page-blue);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.page-header__title {
    margin: 0;

    color: var(--sc-page-text);
    font-size: 22px;
    font-weight: 700;
    line-height: 1.25;
    letter-spacing: -0.02em;
}

.page-header__subtitle {
    max-width: 720px;
    margin: 7px 0 0;

    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.5;
}

.page-header__actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;

    flex: 0 0 auto;
}

@media (max-width: 700px) {
    .page-header {
        align-items: stretch;
        flex-direction: column;
        gap: 16px;
    }

    .page-header__actions {
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}
</style>