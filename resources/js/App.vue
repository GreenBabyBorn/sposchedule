<script setup lang="ts">
import { RouterView } from "vue-router";
import AppLayout from "@/layouts/AppLayout.vue";
import { useAuthStore } from "./stores/auth";

const authStore = useAuthStore();
const { fetchUser } = authStore
fetchUser();

</script>

<template>
    <Suspense>
        <AppLayout>
            <RouterView></RouterView>
        </AppLayout>
    </Suspense>
</template>
