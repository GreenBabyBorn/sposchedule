import router from '@/router';
import axios from 'axios';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useAuthStore = defineStore('useAuthStore', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('token') || null);

  // Устанавливаем токен в заголовок Axios
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  const register = async credentials => {
    const response = await axios.post('/api/register', credentials);
    token.value = response.data.token;
    localStorage.setItem('token', token.value);
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  };

  const login = async credentials => {
    const response = await axios.post('/api/login', credentials);
    token.value = response.data.token;
    localStorage.setItem('token', token.value);
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  };

  const logout = async () => {
    await axios.post('/api/logout');
    token.value = null;
    user.value = null;
    localStorage.removeItem('token');
    delete axios.defaults.headers.common['Authorization'];
  };

  const fetchUser = async () => {
    if (token.value) {
      const response = await axios.get('/api/user');
      user.value = response.data;
    }
  };

  const isAuth = computed(() => {
    return Boolean(user.value);
  });

  return {
    user,
    token,
    isAuth,
    register,
    login,
    logout,
    fetchUser,
  };
});
