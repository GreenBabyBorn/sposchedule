<script setup lang="ts">
import axios from "axios";
import { RouterView } from "vue-router";
import AppLayout from "@/layouts/AppLayout.vue";
// import { useAuthStore } from "./stores/auth";
// import { onMounted } from "vue";
import { useLoadingStore } from "./stores/loading";
import { useToast } from "primevue/usetoast";

// const authStore = useAuthStore();
// const { fetchUser } = authStore
// fetchUser();
const toast = useToast();
const loadingStore = useLoadingStore();

axios.interceptors.request.use((config) => {
    loadingStore.startLoading(); // Увеличиваем счетчик активных запросов и запускаем загрузку при первом запросе
    return config;
});

axios.interceptors.response.use(
    (response) => {
        loadingStore.stopLoading(); // Уменьшаем счетчик при успешном ответе
        return response;
    },
    (error) => {
        loadingStore.stopLoading(); // Уменьшаем счетчик при ошибке
        if (error.response && error.response.status === 429) {
            // Показываем сообщение об ошибке
            // alert('Превышен лимит запросов. Попробуйте позже.');
            toast.add({ severity: 'error', summary: 'Ошибка', detail: error.response?.data?.message, life: 1000, closable: true });
        }
        return Promise.reject(error);
    }
);
</script>

<template>
    <Suspense>
        <AppLayout>
            <RouterView></RouterView>
        </AppLayout>
    </Suspense>
</template>
