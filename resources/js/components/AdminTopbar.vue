<script setup lang="ts">
import router from '@/router';
import { useAppStore } from '@/stores/app';
import { useAuthStore } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import Button from 'primevue/button';

const { onSidebarToggle } = useAppStore();

const authStore = useAuthStore()
const { user, isAuth } = storeToRefs(authStore)
const { logout } = authStore

async function signout() {
    try {
        await logout()
        router.push('/admin/login')
    }
    catch (e) {
        console.log(e)
    }
}
</script>

<template>
    <div class="fixed h-16 top-0 left-0 w-full z-50 bg-surface-900 flex items-center px-6 gap-4">
        <Button @click="onSidebarToggle" icon="pi pi-bars" text rounded />
        <div class="flex justify-between items-center w-full">
            <span class="font-bold text-xl text-white">Пары РКЭ</span>
            <div class="flex gap-2">
                <Button as="router-link" :label="user?.name" text to="/admin/user" />
                <Button @click="signout" icon="pi pi-sign-out" text />

            </div>
        </div>
    </div>
</template>