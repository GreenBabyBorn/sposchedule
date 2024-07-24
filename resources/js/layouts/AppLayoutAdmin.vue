<script setup lang="ts">
import AdminNav from '../components/AdminNav.vue'
import Toast from 'primevue/toast';
</script>

<template>
  <Toast></Toast>
  <div class="flex flex-col lg:flex-row">
    <AdminNav></AdminNav>
    <main class="min-w-0 basis-full bg-surface-0 dark:text-surface-0 dark:bg-surface-950 p-6">
      <slot />
    </main>
  </div>

</template>
