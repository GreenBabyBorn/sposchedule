<script setup lang="ts">
import { useAppStore } from '@/stores/app';
import Button from 'primevue/button';

const { onSidebarToggle, isNavbarActive } = useAppStore();
</script>

<template>
    <div class="fixed h-16 top-0 left-0 w-full z-50 bg-surface-900 flex items-center px-6 gap-4">
        <Button @click="onSidebarToggle" icon="pi pi-bars" text rounded />
        <div class="">
            <span class="font-bold text-xl text-white">СПОР</span>
        </div>
    </div>
</template>