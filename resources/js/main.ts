import '../css/app.css';
import router from './router';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import Aura from './presets/Aura';
import App from './App.vue';
import ToastService from 'primevue/toastservice';
import { VueQueryPlugin, QueryClient } from '@tanstack/vue-query';
import { locale } from './locale';
import '@tanstack/vue-query';
import type { AxiosError } from 'axios';
import axios from 'axios';

declare module '@tanstack/vue-query' {
  interface Register {
    defaultError: AxiosError;
  }
}

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

const pinia = createPinia();

createApp(App)
  .use(pinia)
  .use(VueQueryPlugin)
  .use(ToastService)
  .use(PrimeVue, {
    unstyled: true,
    pt: Aura,
    locale,
  })
  .use(router)
  .mount('#app');
