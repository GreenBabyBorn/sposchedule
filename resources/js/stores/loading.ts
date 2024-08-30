// stores/loadingStore.js
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLoadingStore = defineStore('loading', () => {
  const isLoading = ref(false);
  const requestDuration = ref(0);

  // Функция для начала загрузки
  const startLoading = () => {
    isLoading.value = true;
    requestDuration.value = 0; // Сброс таймера
  };

  // Функция для завершения загрузки
  const stopLoading = startTime => {
    isLoading.value = false;
    requestDuration.value = Number((performance.now() - startTime).toFixed(2)); // Время выполнения в мс
  };

  return { isLoading, requestDuration, startLoading, stopLoading };
});
