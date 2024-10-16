<script setup lang="ts">
  import { useAuthStore } from '@/stores/auth';
  import { storeToRefs } from 'pinia';
  import InputText from 'primevue/inputtext';
  import Button from 'primevue/button';
  import Password from 'primevue/password';
  import { useUserUpdate } from '@/queries/users';
  import { reactive } from 'vue';
  import { useToast } from 'primevue/usetoast';

  const toast = useToast();
  const authStore = useAuthStore();
  const { user } = storeToRefs(authStore);

  const { mutateAsync: updateUser } = useUserUpdate();

  const password = reactive({
    password: '',
    password_confirmation: '',
  });

  async function updateProfile() {
    try {
      await updateUser({
        name: user.value?.name,
        email: user.value?.email,
      });
      toast.add({
        severity: 'success',
        summary: 'Успешно',
        detail: 'Информация обновлена',
        life: 3000,
        closable: true,
      });
    } catch (e) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response.data.message,
        life: 3000,
        closable: true,
      });
      return;
    }
  }

  async function updatePassword() {
    try {
      await updateUser({
        ...password,
      });
      toast.add({
        severity: 'success',
        summary: 'Успешно',
        detail: 'Пароль обновлен',
        life: 3000,
        closable: true,
      });
    } catch (e) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response.data.message,
        life: 3000,
        closable: true,
      });
      return;
    }
    password.password = '';
    password.password_confirmation = '';
  }
</script>
<template>
  <div class="flex flex-col items-start gap-4">
    <div class="">
      <h1 class="text-lg mb-2">Профиль</h1>
      <div class="flex flex-col items-start gap-4">
        <InputText v-model="user.name" class="" @change="updateProfile" />
        <InputText v-model="user.email" class="" @change="updateProfile" />
      </div>
    </div>

    <div class="">
      <h1 class="text-lg mb-2">Смена пароля</h1>
      <div class="flex flex-col items-start gap-4">
        <Password
          v-model="password.password"
          placeholder="Новый пароль"
          :feedback="false"
          toggle-mask
        />
        <Password
          v-model="password.password_confirmation"
          placeholder="Подтверждение пароля"
          :feedback="false"
          toggle-mask
        />
        <Button
          :disabled="!password.password_confirmation || !password.password"
          label="Сохранить"
          @click="updatePassword"
        />
      </div>
    </div>
  </div>
</template>
