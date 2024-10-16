<script setup>
  import { computed } from 'vue';
  import { useLoadingStore } from '../stores/loading';

  const loadingStore = useLoadingStore();
  const isLoading = computed(() => loadingStore.isLoading);
</script>

<template>
  <div v-if="isLoading" class="loading-bar">
    <div class="loading-progress" />
  </div>
</template>

<style scoped>
  .loading-bar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    /* background: rgba(255, 0, 0, 0.212); */
    display: flex;
    align-items: center;
    justify-content: flex-start;
    z-index: 5000;
  }

  .loading-progress {
    height: 100%;
    background-color: #007bff;
    animation: loading 2s infinite;
  }

  @keyframes loading {
    0% {
      width: 0%;
    }

    50% {
      width: 50%;
    }

    100% {
      width: 100%;
    }
  }

  .loading-time {
    margin-left: 10px;
    font-size: 12px;
  }
</style>
