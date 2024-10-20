<script setup lang="ts">
  import { computed, reactive, ref, watch } from 'vue';
  import InputText from 'primevue/inputtext';
  import Password from 'primevue/password';
  import Button from 'primevue/button';
  import { useAuthStore } from '@/stores/auth';
  import { storeToRefs } from 'pinia';
  import router from '@/router';
  import LoadingBar from '../components/LoadingBar.vue';
  import { useDebounceFn } from '@vueuse/core';
  import Checkbox from 'primevue/checkbox';

  const authStore = useAuthStore();
  const { isAuth } = storeToRefs(authStore);
  const { login } = authStore;

  const credentials = reactive({
    email: '',
    password: '',
    remember: false,
  });

  watch(credentials, () => {
    if (error.value) {
      error.value = null;
    }
  });

  const error = ref();

  const isError = computed(() => Boolean(error.value));

  async function auth() {
    try {
      await login(credentials);
    } catch (e) {
      error.value = e?.response.data;

      return;
    }
    if (isAuth) router.push('/admin/schedules/changes');
  }

  const debouncedAuth = useDebounceFn(auth, 300);
</script>

<template>
  <LoadingBar />
  <div class="">
    <form
      class="flex h-screen flex-col items-center justify-center gap-4"
      @submit.prevent="debouncedAuth()"
    >
      <div
        class="flex max-w-72 flex-col gap-4 rounded-lg bg-surface-100 px-4 py-8 dark:bg-surface-900"
      >
        <h1 class="mb-4 text-center text-2xl dark:text-surface-100">
          Авторизация
        </h1>
        <InputText
          v-model="credentials.email"
          autofocus
          :invalid="isError"
          placeholder="Электронная почта"
        />
        <Password
          v-model="credentials.password"
          :invalid="isError"
          fluid
          placeholder="Пароль"
          :feedback="false"
          toggle-mask
        />
        <div class="flex items-center gap-2">
          <Checkbox
            v-model="credentials.remember"
            input-id="remember"
            :binary="true"
          />
          <label class="text-slate-800 dark:text-surface-400" for="remember">
            Запомнить меня
          </label>
        </div>
        <Button
          :disabled="!credentials.email || !credentials.password"
          type="submit"
          label="Войти"
        />
        <span v-if="isError" class="w-full text-red-400">{{
          error?.message
        }}</span>
      </div>
    </form>
  </div>
</template>
