<script setup lang="ts">
import TheHeader from "../components/AppHeader.vue";
import TheFooter from "../components/AppFooter.vue";
</script>

<template>
  <TheHeader></TheHeader>
  <main class="">
    <slot />
  </main>
  <TheFooter></TheFooter>
</template>
