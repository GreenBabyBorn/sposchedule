import '../css/app.css';
import router from './router';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import Aura from './presets/Aura';
import App from './App.vue';
import ToastService from 'primevue/toastservice';
import { VueQueryPlugin, QueryClient } from '@tanstack/vue-query';
import type { AxiosError } from 'axios';

// declare module '@tanstack/vue-query' {
//     interface Register {
//         defaultError: AxiosError
//     }
// }

const pinia = createPinia();

createApp(App)
  .use(VueQueryPlugin)
  .use(ToastService)
  .use(pinia)
  .use(PrimeVue, {
    unstyled: true,
    pt: Aura,
  })
  .use(router)
  .mount('#app');
