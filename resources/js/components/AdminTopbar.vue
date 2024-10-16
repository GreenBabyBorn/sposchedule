<script setup lang="ts">
  import router from '@/router';
  import { useAppStore } from '@/stores/app';
  import { useAuthStore } from '@/stores/auth';
  import { storeToRefs } from 'pinia';
  import Button from 'primevue/button';

  const { onSidebarToggle } = useAppStore();

  const authStore = useAuthStore();
  const { user } = storeToRefs(authStore);
  const { logout } = authStore;

  async function signout() {
    try {
      await logout();
      router.push('/admin/login');
    } catch (e) {
      console.log(e);
    }
  }
</script>

<template>
  <div
    style="z-index: 1100"
    class="fixed h-16 top-0 left-0 w-full dark:bg-surface-900 bg-surface-200 flex items-center px-6 gap-4"
  >
    <Button
      icon="pi pi-bars"
      text
      severity="contrast"
      rounded
      @click="onSidebarToggle"
    />
    <div class="flex justify-between items-center w-full">
      <RouterLink to="/" class="font-bold text-xl dark:text-white">
        Пары РКЭ
      </RouterLink>
      <div class="flex gap-2">
        <Button
          severity="contrast"
          as="router-link"
          :label="user?.name"
          text
          to="/admin/user"
        />
        <Button
          severity="contrast"
          icon="pi pi-sign-out"
          text
          @click="signout"
        />
      </div>
    </div>
  </div>
</template>
