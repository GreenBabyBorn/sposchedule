// stores/loadingStore.js
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLoadingStore = defineStore('loading', () => {
  const isLoading = ref(false);
  const requestDuration = ref(0);
  const activeRequests = ref(0); // Счетчик активных запросов
  let startTime = 0; // Время начала первого запроса

  const startLoading = () => {
    if (activeRequests.value === 0) {
      // Запускать только если это первый запрос
      isLoading.value = true;
      startTime = performance.now();
    }
    activeRequests.value++;
  };

  const stopLoading = () => {
    activeRequests.value--;
    if (activeRequests.value === 0) {
      // Остановить загрузку, если это был последний активный запрос
      isLoading.value = false;
      requestDuration.value = Number(
        (performance.now() - startTime).toFixed(2)
      );
    }
  };

  return { isLoading, requestDuration, startLoading, stopLoading };
});
