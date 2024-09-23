<script setup lang="ts">

import Toast from 'primevue/toast';
</script>

<template>
  <Toast></Toast>
  <slot />
</template>
<style>
body {
  @apply bg-white dark:bg-surface-950;
}
</style>