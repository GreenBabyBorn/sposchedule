<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import { useAuthStore } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import router from '@/router';

import { useToast } from 'primevue/usetoast';
import { useDebounceFn } from '@vueuse/core';

const authStore = useAuthStore()
const { isAuth } = storeToRefs(authStore)
const { login } = authStore

const credentials = reactive({
    email: '',
    password: '',
})

watch(credentials, () => {
    if (error.value) {
        error.value = null
    }
})

const error = ref()

async function auth() {
    try {
        await login(credentials)
    }
    catch (e) {
        error.value = e?.response.data

        return
    }
    if (isAuth) router.push('/admin/schedules/changes')

}

const debouncedAuth = useDebounceFn(auth, 300);
</script>

<template>
    <div class="">

        <form @submit.prevent="debouncedAuth()" class="flex flex-col justify-center items-center h-screen gap-4">
            <div class=" flex flex-col gap-4 rounded-lg px-4 py-8 bg-surface-100 dark:bg-surface-900 max-w-72">
                <h1 class=" text-center  text-2xl mb-4">Авторизация</h1>
                <InputText :invalid="error" placeholder="Электронная почта" v-model="credentials.email"></InputText>
                <Password :invalid="error" fluid placeholder="Пароль" v-model="credentials.password" :feedback="false"
                    toggleMask>
                </Password>
                <Button :disabled="!credentials.email || !credentials.password" type="submit" label="Войти"></Button>
                <span class="text-red-400 w-full" v-if="error">{{ error?.message }}</span>
            </div>
        </form>
    </div>
</template>