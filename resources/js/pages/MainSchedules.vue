<script setup lang="ts">
import ScheduleItem from '../components/MainScheduleItem.vue'
import Select from 'primevue/select';
import { onMounted, ref, watch } from 'vue'
import { useGroupsQuery } from '@/queries/groups';
import { useMainSchedulesQuery } from '@/queries/schedules';
import { useScheduleStore } from '@/stores/schedule'
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import router from '@/router';
import { useStorage } from '@vueuse/core';

const route = useRoute()

const scheduleStore = useScheduleStore();
const { schedules, selectedMainGroup, selectedMainSemester, queryParams } = storeToRefs(scheduleStore);
const { setSchedules } = scheduleStore;

// Используем useStorage для сохранения query параметров в localStorage
const storedParams = useStorage('mainSchedules', { group: '', semester: '' });

// Запросы данных
const { data: groups, isFetched } = useGroupsQuery();
const { data: mainSchedules } = useMainSchedulesQuery(selectedMainGroup, selectedMainSemester);

// Обновление query параметров в URL и localStorage
const updateQueryParams = () => {
    const newQuery = {
        ...route.query,
        group: selectedMainGroup.value?.id || null,
        semester: selectedMainSemester.value?.id || null,
    };

    router.replace({ query: newQuery });

    // Сохраняем новые параметры в localStorage
    storedParams.value.group = selectedMainGroup.value?.id || '';
    storedParams.value.semester = selectedMainSemester.value?.id || '';
};

// Следим за изменениями выбранной группы и семестра для обновления query параметров
watch([selectedMainGroup, selectedMainSemester], updateQueryParams, { deep: true });

// Следим за изменениями данных расписания и устанавливаем их в store
watch(mainSchedules, (newData) => {
    if (newData) {
        setSchedules(newData);
    }
});

// Инициализация при монтировании компонента
onMounted(() => {
    // Восстанавливаем значения из localStorage при загрузке
    if (storedParams.value.group) {
        selectedMainGroup.value = groups.value?.find((group) => group.id == storedParams.value.group) || null;
    }
    if (storedParams.value.semester && selectedMainGroup.value) {
        selectedMainSemester.value = selectedMainGroup.value.semesters.find((semester) => semester.id == storedParams.value.semester) || null;
    }

    // Синхронизируем параметры в URL с состоянием store
    updateQueryParams();

    // Следим за тем, когда данные групп будут загружены
    watch(
        isFetched,
        (isFetchedVal) => {
            if (isFetchedVal && queryParams.value.group && queryParams.value.semester) {
                // Находим группу, соответствующую query параметрам
                const queryGroup = groups.value?.find((group) => group.id == queryParams.value.group);
                if (queryGroup) {
                    selectedMainGroup.value = queryGroup;
                    selectedMainSemester.value = queryGroup.semesters.find((semester) => semester.id == queryParams.value.semester);
                }
            }
        },
        { immediate: true } // Наблюдатель начнет работу сразу
    );
})


</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Основное расписание</h1>
        </div>
        <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center">
                <Select editable v-model="selectedMainGroup" :options="groups" optionLabel="name" placeholder="Группа"
                    class="w-full md:w-[10rem]" />
                <Select v-model="selectedMainSemester" :options="selectedMainGroup?.semesters" optionLabel="name"
                    placeholder="Семестр" class="w-full md:w-[15rem]" />
            </div>

        </div>
        <div class="flex flex-col gap-6">
            <ScheduleItem :group="selectedMainGroup" :semester="selectedMainSemester" v-for="(item, index) in schedules"
                :item="item" :lessons="item.lessons" :week-day="index.toString()">
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