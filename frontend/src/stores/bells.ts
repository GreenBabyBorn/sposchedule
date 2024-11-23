import { defineStore } from 'pinia';
import { computed, ref, toRaw } from 'vue';

export const useBellsStore = defineStore('useBellsStore', () => {
  const bells = ref();
  const publicBells = ref();

  const isFetchedPublicBells = ref(false);

  function setBells(newBells) {
    bells.value = toRaw(newBells ?? []);
  }

  function setPublicBells(newBells) {
    publicBells.value = toRaw(newBells ?? []);
  }

  const isChangesPublicBells = computed(() => {
    if (!publicBells.value) return false;

    for (const bell of publicBells.value) {
      if (bell?.type === 'changes') {
        return true;
      }
    }
    return false;
  });

  return {
    bells,
    publicBells,
    isChangesPublicBells,
    isFetchedPublicBells,
    setBells,
    setPublicBells,
  };
});
