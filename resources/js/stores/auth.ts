import axios from 'axios';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useAuthStore = defineStore('useAuthStore', () => {
  const user = ref(null);

  const register = async credentials => {
    const response = await axios.post('/api/register', credentials);
  };

  const login = async credentials => {
    const response = await axios.post('/api/login', credentials);
  };

  const logout = async () => {
    await axios.post('/api/logout');
    user.value = null;
  };

  const fetchUser = async () => {
    try {
      const response = await axios.get('/api/user');
      user.value = response.data;
    } catch (e) {
      return e;
    }
  };

  const isAuth = computed(() => {
    return Boolean(user.value);
  });

  return {
    user,
    isAuth,
    register,
    login,
    logout,
    fetchUser,
  };
});
