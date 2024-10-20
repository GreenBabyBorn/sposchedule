<script setup lang="ts">
  import AdminNav from '../components/AdminNavbar.vue';
  import AdminTopbar from '../components/AdminTopbar.vue';
  import Toast from 'primevue/toast';
  import ConfirmDialog from 'primevue/confirmdialog';
  import LoadingBar from '../components/LoadingBar.vue';

  import { useAppStore } from '@/stores/app';
  const { isNavbarActive } = useAppStore();
</script>

<template>
  <Toast />
  <ConfirmDialog />
  <div class="h-screen">
    <LoadingBar />
    <AdminTopbar />
    <AdminNav />
    <main
      :class="{ 'md:ml-80': !isNavbarActive }"
      class="min-w-0 basis-full bg-surface-0 p-6 pt-24 transition-all dark:bg-surface-950 dark:text-surface-0"
    >
      <slot />
    </main>
  </div>
</template>
<style>
  body {
    @apply bg-white dark:bg-surface-950;
  }
</style>
