<script setup lang="ts">
  import { useRoute } from "vue-router";
  const route = useRoute();
</script>

<template>
  <component :is="route.meta.layoutComponent">
    <slot />
  </component>
</template>

<style scoped></style>
