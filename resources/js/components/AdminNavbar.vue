<script setup lang="ts">
  import Menu from 'primevue/menu';
  import { ref } from 'vue';
  import { useAppStore } from '@/stores/app';

  const { isNavbarActive } = useAppStore();

  const items = ref([
    {
      label: 'Семестры',
      icon: 'pi pi-hourglass',
      route: '/admin/semesters',
    },
    {
      label: 'Корпуса',
      icon: 'pi pi-building',
      route: '/admin/buildings',
    },
    {
      label: 'Группы',
      icon: 'pi pi-users',
      route: '/admin/groups',
    },
    {
      label: 'Предметы',
      icon: 'pi pi-book',
      route: '/admin/subjects',
    },
    {
      label: 'Преподаватели',
      icon: 'pi pi-user',
      route: '/admin/teachers',
    },
    {
      label: 'Расписание',
      icon: 'pi pi-calendar',
      route: '/admin/schedules/main',
    },
    {
      label: 'Изменения',
      icon: 'pi pi-calendar-plus',
      route: '/admin/schedules/changes',
    },
    {
      label: 'Звонки',
      icon: 'pi pi-bell',
      route: '/admin/bells',
    },
    {
      label: 'Аналитика',
      icon: 'pi pi-chart-bar',
      route: '/analytics',
    },
    {
      label: 'История',
      icon: 'pi pi-list',
      route: '/admin/history',
    },
  ]);
</script>

<template>
  <div
    :class="{ 'active-navbar': isNavbarActive }"
    class="fixed left-0 top-16 z-50 w-80 flex-grow-0 basis-80 transition-transform md:bg-surface-200 lg:min-h-screen md:dark:bg-surface-950"
  >
    <div class="flex flex-col px-4 py-4">
      <Menu :model="items" class="w-full p-4">
        <template #item="{ item, props }">
          <RouterLink
            v-if="item.route"
            v-slot="{ href, navigate }"
            :to="item.route"
            custom
          >
            <a
              :class="{
                'font-bold !text-primary-500': $route.path === item.route,
              }"
              :href="href"
              v-bind="props.action"
              @click="navigate"
            >
              <span :class="item.icon" />
              <span class="ml-2">{{ item.label }}</span>
            </a>
          </RouterLink>
          <a
            v-else
            :class="{
              'font-bold !text-primary-500': $route.path === item.route,
            }"
            :href="item.url"
            :target="item.target"
            v-bind="props.action"
          >
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
