<script setup lang="ts">
import type { Component } from 'vue';

interface TabStep {
    key: string;
    label: string;
    icon?: Component;
    hasError?: boolean;
}

const props = defineProps<{
    activeStep: string;
    steps: TabStep[];
}>();

const emit = defineEmits<{
    (event: 'update:activeStep', value: string): void;
}>();

const selectStep = (step: string): void => {
    emit('update:activeStep', step);
};
</script>

<template>
    <div class="tab-steps">
        <nav class="tab-steps__nav">
            <button
                v-for="step in props.steps"
                :key="step.key"
                type="button"
                class="tab-steps__item"
                :class="{
                    'tab-steps__item--active':
                        props.activeStep === step.key,
                }"
                @click="selectStep(step.key)"
            >
                <component
                    :is="step.icon"
                    v-if="step.icon"
                    :size="16"
                    class="tab-steps__icon"
                />

                <span class="tab-steps__label">
                    {{ step.label }}
                </span>

                <span
                    v-if="step.hasError"
                    class="tab-steps__error"
                ></span>
            </button>
        </nav>
    </div>
</template>

<style scoped>
.tab-steps {
    padding: 8px 28px 0;
    border-bottom: 1px solid var(--sc-page-border);
    background: #fbfcfd;
}

.tab-steps__nav {
    display: flex;
    align-items: stretch;
    gap: 4px;
    overflow-x: auto;
}

.tab-steps__item {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 48px;
    padding: 0 16px;
    border: 0;
    border-bottom: 2px solid transparent;
    border-radius: 7px 7px 0 0;
    background: transparent;
    color: #7b8b97;
    font-family: inherit;
    font-size: 12px;
    font-weight: 650;
    white-space: nowrap;
    cursor: pointer;
    transition:
        color 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.tab-steps__item:hover {
    background: #f4f8fa;
    color: var(--sc-page-text);
}

.tab-steps__item--active {
    border-bottom-color: #a9d9f2;
    background: #ffffff;
    color: var(--sc-page-text);
}

.tab-steps__icon {
    flex: 0 0 auto;
}

.tab-steps__label {
    display: inline-flex;
    align-items: center;
}

.tab-steps__error {
    width: 6px;
    height: 6px;
    flex: 0 0 6px;
    border-radius: 50%;
    background: #d95c4f;
}

@media (max-width: 900px) {
    .tab-steps {
        padding-left: 15px;
        padding-right: 15px;
    }
}

@media (max-width: 640px) {
    .tab-steps__item {
        padding: 0 12px;
    }

    .tab-steps__label {
        display: none;
    }
}
</style>