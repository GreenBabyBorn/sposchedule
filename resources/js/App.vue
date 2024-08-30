<script setup lang="ts">
import { RouterView } from "vue-router";
import AppLayout from "@/layouts/AppLayout.vue";
import { useAuthStore } from "./stores/auth";
import { onMounted } from "vue";
import axios from "axios";
import { useLoadingStore } from "./stores/loading";

const authStore = useAuthStore();
const { fetchUser } = authStore
fetchUser();

const loadingStore = useLoadingStore();

// Добавляем интерсептор запроса
axios.interceptors.request.use(config => {
    loadingStore.startLoading();
    // @ts-ignore
    config.meta = { startTime: performance.now() }; // Сохраняем время начала запроса
    console.log(config)
    return config;
});

// Добавляем интерсептор ответа
axios.interceptors.response.use(
    response => {
        // @ts-ignore
        loadingStore.stopLoading(response.config.meta.startTime);
        return response;
    },
    error => {
        loadingStore.stopLoading(error.config?.meta?.startTime || 0);
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
