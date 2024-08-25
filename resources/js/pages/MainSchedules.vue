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

const route = useRoute()


const scheduleStore = useScheduleStore()
const { schedules, selectedMainGroup, selectedMainSemester, queryParams } = storeToRefs(scheduleStore)
const { setSchedules, } = scheduleStore

const { data: groups, isFetched, } = useGroupsQuery()
const { data: mainSchedules } = useMainSchedulesQuery(selectedMainGroup, selectedMainSemester)

const updateQueryParams = () => {
    router.replace({
        query: {
            ...route.query,
            group: selectedMainGroup.value?.id || null,
            semester: selectedMainSemester.value?.id || null,
        },
    });
};

watch(
    [selectedMainGroup, selectedMainSemester],
    () => {
        updateQueryParams();
    },
    { deep: true }
);

watch(mainSchedules, (newData) => {
    if (newData) {
        setSchedules(newData)
    }
})


onMounted(() => {
    updateQueryParams()
    watch(isFetched, (isFetchedVal) => {
        if (isFetchedVal && queryParams.value.group && queryParams.value.semester) {
            // Находим группу, которая соответствует query параметру
            const queryGroup = groups.value?.find((group: any) => group.id == queryParams.value.group);
            if (queryGroup) {
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
    </div>
</template>