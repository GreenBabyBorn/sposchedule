import { defineStore } from 'pinia';
import { ref, toRaw, watch } from 'vue';

export const useBellsStore = defineStore('useBellsStore', () => {
  const bells = ref();
  function setBells(newBells) {
    bells.value = toRaw(newBells ?? []);
  }

  return {
    bells,
    setBells,
  };
});
