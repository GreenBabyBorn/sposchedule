import type { Bell } from '@/components/bells/types';
import { defineStore } from 'pinia';
import { computed, ref, toRaw } from 'vue';

export const useBellsStore = defineStore('useBellsStore', () => {
  const bells = ref();
  const publicBells = ref();

  const isFetchedPublicBells = ref(false);

  function setBells(newBells: Bell[]) {
    bells.value = toRaw(newBells ?? []);
  }

  function setPublicBells(newBells: Bell[]) {
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
