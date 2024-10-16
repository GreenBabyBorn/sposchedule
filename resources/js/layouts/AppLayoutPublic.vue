<script setup lang="ts">
  import TheFooter from '../components/PublicFooter.vue';
  import Toast from 'primevue/toast';
</script>

<template>
  <Toast />
  <div
    class="page bg-surface-0 dark:text-surface-0 dark:bg-surface-950 min-h-screen"
  >
    <main>
      <slot />
    </main>
    <TheFooter />
  </div>
</template>

<style>
  .page {
    display: grid;
    grid-template-rows: 1fr auto;
  }

  body {
    @apply bg-white dark:bg-surface-950;
  }
</style>
