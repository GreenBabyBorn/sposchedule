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
      console.error(e);
    }
  }
</script>

<template>
  <div
    style="z-index: 1100"
    class="fixed left-0 top-0 flex h-16 w-full items-center gap-4 bg-surface-0 px-6 shadow-md dark:bg-surface-900"
  >
    <Button
      icon="pi pi-bars"
      text
      severity="contrast"
      rounded
      @click="onSidebarToggle"
    />
    <div class="flex w-full items-center justify-between">
      <RouterLink
        title="на главную"
        to="/"
        class="flex items-center gap-2 text-xl font-bold dark:text-white"
      >
        пары.ркэ.рф
      </RouterLink>
      <div class="flex gap-2">
        <Button
          severity="contrast"
          as="router-link"
          :label="user?.name || 'Пользователь'"
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
