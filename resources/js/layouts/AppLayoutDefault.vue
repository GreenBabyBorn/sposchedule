<script setup lang="ts">
import TheHeader from "../components/AppHeader.vue";
import TheFooter from "../components/AppFooter.vue";
import Toast from 'primevue/toast';
</script>

<template>
  <Toast></Toast>
  <TheHeader></TheHeader>
  <main class="dark:text-surface-100">
    <slot />
  </main>
  <TheFooter></TheFooter>
</template>
