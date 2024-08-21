<script setup lang="ts">
import ScheduleItem from '../components/ScheduleItem.vue'
import Button from 'primevue/button';
import Select from 'primevue/select';
import { onMounted, ref, watch } from 'vue'
import { useGroupsQuery } from '@/queries/groups';
import { useMainSchedulesQuery } from '@/queries/schedules';
import { useScheduleStore } from '@/stores/schedule'
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import router from '@/router';

const route = useRoute()
const queryParams = ref(route.query);

const selectedMainGroup: any = ref(null);
const selectedMainSemester: any = ref(null);
const { data: groups, isFetched, } = useGroupsQuery()
const { data: mainSchedules } = useMainSchedulesQuery(selectedMainGroup, selectedMainSemester)

const scheduleStore = useScheduleStore()
const { schedules } = storeToRefs(scheduleStore)
const { setSchedules } = scheduleStore

watch(mainSchedules, (newData) => {
    if (newData) {
        setSchedules(newData)
    }
})

// Функция для обновления query параметров
const updateQueryParams = () => {
    router.replace({
        query: {
            ...route.query, // Сохраняем другие query параметры, если они есть
            group: selectedMainGroup.value?.id || null,
            semester: selectedMainSemester.value?.id || null,
        },
    });
};

// Watch для изменения группы и семестра
watch([selectedMainGroup, selectedMainSemester], () => {
    updateQueryParams();
}, { deep: true });

// Обрабатываем монтирование компонента
onMounted(() => {
    // Ждем, пока данные по группам будут загружены
    watch(isFetched, (isFetchedVal) => {
        if (isFetchedVal && queryParams.value.group && queryParams.value.semester) {
            // Находим группу, которая соответствует query параметру
            const queryGroup = groups.value?.find((group: any) => group.id == queryParams.value.group);
            if (queryGroup) {
                // Устанавливаем выбранную группу
                selectedMainGroup.value = queryGroup;
                selectedMainSemester.value = queryGroup.semesters.find(semester => semester.id == queryParams.value.semester)
            }
        }
    }, { immediate: true });
});

</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Основное расписание</h1>
        </div>
        <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center">
                <Select v-model="selectedMainGroup" :options="groups" optionLabel="name" placeholder="Группа"
                    class="w-full md:w-[10rem]" />
                <Select v-model="selectedMainSemester" :options="selectedMainGroup?.semesters" optionLabel="name"
                    placeholder="Семестр" class="w-full md:w-[10rem]" />
            </div>
            <Button>Сохранить</Button>
        </div>
        <div class="flex flex-col gap-6">
            <ScheduleItem :group="selectedMainGroup" :semester="selectedMainSemester" v-for="(item, index) in schedules"
                :item="item" :lessons="item.lessons" :week-day="index.toString()">
            </ScheduleItem>
        </div>
    </div>




</template>