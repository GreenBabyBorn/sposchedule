<script setup lang="ts">
import ScheduleItem from '../components/ScheduleItem.vue'
import SelectButton from 'primevue/selectbutton';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { ref, watch } from 'vue'
import { useGroupsQuery } from '@/queries/groups';
import { useMainSchedulesQuery } from '@/queries/schedules';
import { useScheduleStore } from '@/stores/schedule'
import { storeToRefs } from 'pinia';

const value = ref('Основное');
const options = ref(['Основное', 'Изменения']);

const selectedMainGroup: any = ref(null);
const selectedMainSemester: any = ref(null);
const { data: groups } = useGroupsQuery()
const { data: mainSchedules } = useMainSchedulesQuery(selectedMainGroup, selectedMainSemester)

const scheduleStore = useScheduleStore()
const { schedules } = storeToRefs(scheduleStore)
const { setSchedules } = scheduleStore

watch(mainSchedules, (newData) => {
    if (newData) {
        setSchedules(newData)
    }
})
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Расписание</h1>
        </div>
        <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center">

                <SelectButton v-model="value" :options="options" aria-labelledby="basic" />
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