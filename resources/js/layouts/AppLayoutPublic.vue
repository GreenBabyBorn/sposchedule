<script setup lang="ts">
import TheHeader from "../components/PublicHeader.vue";
import TheFooter from "../components/PublicFooter.vue";
import Toast from 'primevue/toast';
</script>

<template>
  <Toast></Toast>
  <div class="page bg-surface-0 dark:text-surface-0 dark:bg-surface-950 min-h-screen">
    <!-- <TheHeader></TheHeader> -->
    <main>
      <slot />
    </main>
    <TheFooter></TheFooter>
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