<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { storeToRefs } from 'pinia';

const authStore = useAuthStore()
const { user, isAuth } = storeToRefs(authStore)
</script>
<template>
    <div class="flex flex-col">
        <span class="text-xl">
            {{ user?.name }}
        </span>
        <span>
            {{ user?.email }}
        </span>
    </div>
</template>