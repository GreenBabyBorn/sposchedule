<script setup lang="ts">
import AdminNav from '../components/AdminNavbar.vue'
import AdminTopbar from '../components/AdminTopbar.vue'
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';

import { useAppStore } from '@/stores/app';
const { isNavbarActive } = useAppStore();
</script>

<template>
  <Toast></Toast>
  <ConfirmDialog></ConfirmDialog>
  <div class="h-screen">
    <AdminTopbar></AdminTopbar>
    <AdminNav></AdminNav>
    <main :class="{ 'ml-80': !isNavbarActive }"
      class="pt-24 transition-all min-w-0 basis-full bg-surface-0 dark:text-surface-0 dark:bg-surface-950 p-6">
      <slot />
    </main>
  </div>
</template>
