// import axios from 'axios';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useAppStore = defineStore('useAppStore', () => {
  const sidebarState = ref(true);
  function onSidebarToggle() {
    sidebarState.value = !sidebarState.value;
  }

  // const user = ref();

  // async function fetchUser() {
  //   const user = (await axios.get('/api/user')).data;
  //   user.value = user;
  // }

  // const isAuth = computed(() => {
  //   return user.value;
  // });

  const isNavbarActive = computed(() => sidebarState);
  return { isNavbarActive, onSidebarToggle };
});
