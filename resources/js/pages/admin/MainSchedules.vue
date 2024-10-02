<script setup lang="ts">
import ScheduleItem from '../../components/schedule/AdminMainScheduleItem.vue'
import Select from 'primevue/select';
import { computed, onMounted, ref, watch } from 'vue'
import { useGroupsQuery } from '@/queries/groups';
import { useMainSchedulesQuery } from '@/queries/schedules';
import { useScheduleStore } from '@/stores/schedule'
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import router from '@/router';
import { useStorage } from '@vueuse/core';
import Button from 'primevue/button';

const route = useRoute()

const scheduleStore = useScheduleStore();
const { schedules, selectedMainGroupName, selectedMainSemester, queryParams } = storeToRefs(scheduleStore);
const { data: groups, isFetched } = useGroupsQuery();
const { setSchedules } = scheduleStore;

// Используем useStorage для сохранения query параметров в localStorage
const storedParams = useStorage('mainSchedules', { group: '', semester: '' });
// Запросы данных
const selectedMainGroup = computed(() => groups.value?.find((group) => group.name == selectedMainGroupName.value))
const semesters = computed(() => selectedMainGroup.value?.semesters);
const { data: mainSchedules } = useMainSchedulesQuery(selectedMainGroup, selectedMainSemester);


// Обновление query параметров в URL и localStorage
const updateQueryParams = () => {
    const newQuery = {
        ...route.query,
        group: selectedMainGroupName.value,
    };

    router.replace({ query: newQuery });

    // Сохраняем новые параметры в localStorage
    storedParams.value.group = selectedMainGroupName.value;
};

// Следим за изменениями выбранной группы и семестра для обновления query параметров
watch([selectedMainGroupName], updateQueryParams, { deep: true });

// Следим за изменениями данных расписания и устанавливаем их в store
watch(mainSchedules, (newData) => {
    if (newData) {
        setSchedules(newData);
    }
});

// Инициализация при монтировании компонента
// Функция восстановления значений из query или localStorage
const restoreQueryParams = () => {
    if (route.query.group) {
        // Если группы нет в query, используем из localStorage
        selectedMainGroupName.value = route.query.group;
        storedParams.value.group = route.query.group as string; // Сохраняем в localStorage
    } else if (storedParams.value.group) {
        selectedMainGroupName.value = storedParams.value.group;
    }

};

// Инициализация при монтировании компонента
onMounted(() => {
    restoreQueryParams();
    updateQueryParams()

});

</script>

<template>
    <div class="flex flex-col gap-4 ">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Расписание</h1>
        </div>
        <div class="flex  flex-wrap items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex flex-wrap  gap-2 items-center">
                <Select fluid :autoFilterFocus="true" filter v-model="selectedMainGroupName" :options="groups"
                    optionValue="name" optionLabel="name" placeholder="Группа" class="" />
                <Select fluid v-model="selectedMainSemester" :options="semesters" optionLabel="name"
                    placeholder="Семестр" class="" />
                <Button target="_blank" icon="pi pi-print" as="router-link" :to="{
                    path: '/print/main',
                }" />
            </div>
        </div>
        <div class="flex flex-col gap-6">
            <ScheduleItem :group="selectedMainGroup" :semester="selectedMainSemester" v-for="(item, index) in schedules"
                :item="item" :lessons="item.lessons" :published="item?.published" :week-day="index.toString()">
            </ScheduleItem>
        </div>
        <div v-if="!schedules" class="">
            <p class="text-lg">
                Здесь заполняется Основное расписание, которое в дальнейшем можно будет использовать при заполнении
                изменений. Оно заполняется один раз.
            </p>
            <p class="text-lg">Чтобы начать, выберите сначала <b>группу</b> и <b>семестр</b>.</p>
        </div>

    </div>
</template>