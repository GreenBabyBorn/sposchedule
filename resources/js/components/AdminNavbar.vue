<script setup lang="ts">

// import PanelMenu from 'primevue/panelmenu';
import Menu from 'primevue/menu';

import { ref } from "vue";
import { useAppStore } from '@/stores/app';

const { isNavbarActive } = useAppStore();

const items = ref([
    {
        label: 'Семестры',
        icon: 'pi pi-clipboard',
        route: '/admin/semesters'
    },
    {
        label: 'Группы',
        icon: 'pi pi-users',
        route: '/admin/groups'
    },
    {
        label: 'Расписание',
        icon: 'pi pi-clipboard',
        route: '/admin/schedules'
    },
    {
        label: 'Преподаватели',
        icon: 'pi pi-user',
        route: '/admin/teachers'
    }, {
        label: 'Предметы',
        icon: 'pi pi-book',
        route: '/admin/subjects'
    },

]);
</script>

<template>
    <div :class="{ 'active-navbar': isNavbarActive }"
        class="transition-transform fixed z-50 w-80 top-20 left-0 basis-80 flex-grow-0 lg:min-h-screen bg-surface-200 dark:bg-surface-950">
        <div class="flex flex-col py-4 px-4">
            <Menu :model="items" class="w-full p-4">

                <template #item="{ item, props }">
                    <router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
                        <a :href="href" v-bind="props.action" @click="navigate">
                            <span :class="item.icon" />
                            <span class="ml-2">{{ item.label }}</span>
                        </a>
                    </router-link>
                    <a v-else :href="item.url" :target="item.target" v-bind="props.action">
                        <span :class="item.icon" />
                        <span class="ml-2">{{ item.label }}</span>
                    </a>
                </template>
            </Menu>
        </div>
    </div>

</template>

<style>
.active-navbar {
    transform: translateX(-100%);
}
</style>