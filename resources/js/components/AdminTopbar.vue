<script setup lang="ts">
import { useAppStore } from '@/stores/app';
import Button from 'primevue/button';

const { onSidebarToggle, isNavbarActive } = useAppStore();
</script>

<template>
    <div class="fixed h-16 top-0 left-0 w-full z-50 bg-surface-900 flex items-center px-6 gap-4">
        <Button @click="onSidebarToggle" icon="pi pi-bars" text rounded />
        <div class="flex justify-between items-center w-full">
            <span class="font-bold text-xl text-white">СУР</span>
            <Button as="router-link" icon="pi pi-user" label="Имя" text to="/admin/user" />
        </div>
    </div>
</template>