import '../css/app.css';
import axios from 'axios';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import PrimeVue from 'primevue/config';
import Aura from './presets/Aura';
import App from './App.vue';
import '@tanstack/vue-query';
import ToastService from 'primevue/toastservice';
import { VueQueryPlugin } from '@tanstack/vue-query';
import { locale } from './locale';
import ConfirmationService from 'primevue/confirmationservice';
import type { AxiosError } from 'axios';

declare module '@tanstack/vue-query' {
  interface Register {
    defaultError: AxiosError;
  }
}

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

axios.interceptors.response.use(
  response => {
    // Если ответ успешен, просто возвращаем его
    return response;
  },
  (error: AxiosError) => {
    // Если произошла ошибка, проверяем код статуса
    if (
      error.response &&
      // error?.request?.responseURL.includes('/api/user') &&
      error.response.status === 401
    ) {
      // console.log(error);
      // Если ошибка 401, перенаправляем пользователя на страницу входа
      localStorage.removeItem('token');
      // router.push('/admin/login');
    }

    // Возвращаем отклоненное обещание, чтобы остальные обработчики тоже могли обрабатывать ошибку
    return Promise.reject(error);
  }
);

const pinia = createPinia();

createApp(App)
  .use(pinia) // Глобальное хранилище
  .use(router) // Регистрация маршрутизации
  .use(VueQueryPlugin) // Плагин для данных
  .use(PrimeVue, {
    // Компоненты UI с настройками
    unstyled: true,
    pt: Aura,
    locale,
  })
  .use(ToastService) // Зависит от PrimeVue
  .use(ConfirmationService) // Зависит от PrimeVue
  .mount('#app');
