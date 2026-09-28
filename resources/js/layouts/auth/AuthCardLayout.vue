<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="auth-page">
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="auth-logo"
            >
                <AppLogo />
            </Link>

            <div class="flex flex-col gap-6">
                <Card class="auth-card">
                    <CardHeader class="auth-card-header">
                        <CardTitle class="auth-card-title">
                            {{ title }}
                        </CardTitle>

                        <CardDescription class="auth-card-description">
                            {{ description }}
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="auth-card-content">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>