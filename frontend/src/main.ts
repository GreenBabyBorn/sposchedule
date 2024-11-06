import './assets/css/app.css';
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
import KeyFilter from 'primevue/keyfilter';

declare module '@tanstack/vue-query' {
  interface Register {
    defaultError: AxiosError;
  }
}

axios.defaults.baseURL = import.meta.env.VITE_API_URL
  ? import.meta.env.VITE_API_URL
  : '';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

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
  .directive('keyfilter', KeyFilter) // Зависит от PrimeVue
  .mount('#app');
