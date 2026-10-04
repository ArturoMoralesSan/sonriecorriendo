<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        phone: string;
        message?: string;
        label?: string;
    }>(),
    {
        message: '',
        label: 'Contactar por WhatsApp',
    },
);

const whatsappUrl = () => {
    const phone = props.phone.replace(/\D/g, '');

    const message = props.message
        ? `?text=${encodeURIComponent(props.message)}`
        : '';

    return `https://wa.me/${phone}${message}`;
};

const openWhatsApp = () => {
    window.open(
        whatsappUrl(),
        '_blank',
        'noopener,noreferrer',
    );
};
</script>

<template>
    <button
        type="button"
        class="whatsapp-button"
        :aria-label="label"
        :title="label"
        @click="openWhatsApp"
    >
        <svg
            class="whatsapp-icon"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <path
                d="M20.52 3.48A11.82 11.82 0 0 0 12.05 0C5.48 0 .13 5.35.13 11.92c0 2.1.55 4.15 1.6 5.96L.03 24l6.27-1.64a11.9 11.9 0 0 0 5.75 1.47h.01c6.57 0 11.91-5.35 11.91-11.92 0-3.18-1.24-6.17-3.45-8.43ZM12.06 21.8h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.72.97.99-3.63-.23-.37a9.84 9.84 0 0 1-1.51-5.26C2.19 6.49 6.61 2.07 12.05 2.07c2.64 0 5.12 1.03 6.98 2.9a9.8 9.8 0 0 1 2.89 6.97c0 5.44-4.42 9.86-9.86 9.86Zm5.41-7.39c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.64-2.05-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.87 1.22 3.07c.15.2 2.1 3.2 5.09 4.49.71.31 1.27.5 1.7.64.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.28.17-1.41-.07-.12-.27-.2-.57-.35Z"
                fill="white"
            />
        </svg>
    </button>
</template>

<style scoped>
.whatsapp-button {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 1000;

    width: 58px;
    height: 58px;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0;

    border: 0;
    border-radius: 50%;

    background: transparent;
    color: #ffffff;

    cursor: pointer;
    background: #25d366;

    transition:
        transform 0.2s ease,
        opacity 0.2s ease;
}

.whatsapp-button:hover {
    transform: translateY(-3px);
    opacity: 0.8;
}

.whatsapp-button:active {
    transform: translateY(-1px);
}

.whatsapp-icon {
    width: 34px;
    height: 34px;

    display: block;
}

@media (max-width: 600px) {
    .whatsapp-button {
        right: 17px;
        bottom: 17px;

        width: 54px;
        height: 54px;
    }

    .whatsapp-icon {
        width: 31px;
        height: 31px;
    }
}

</style>