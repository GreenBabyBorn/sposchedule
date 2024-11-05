import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useAppStore = defineStore('useAppStore', () => {
  const sidebarState = ref(true);
  function onSidebarToggle() {
    sidebarState.value = !sidebarState.value;
  }

  const isNavbarActive = computed(() => sidebarState);
  return { isNavbarActive, onSidebarToggle };
});
