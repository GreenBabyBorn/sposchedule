import axios from 'axios';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useAuthStore = defineStore('useAuthStore', () => {
  const user = ref<{
    email: string | null;
    name: string;
  }>();

  const login = async (credentials: {
    email: string;
    password: string;
    remember: boolean;
  }) => {
    await axios.get('/sanctum/csrf-cookie');
    await axios.post('/api/login', credentials);
  };

  const logout = async () => {
    await axios.post('/api/logout');
    user.value = undefined;
  };

  const isAuth = computed(() => {
    return Boolean(user.value);
  });

  return {
    user,
    isAuth,

    login,
    logout,
  };
});
