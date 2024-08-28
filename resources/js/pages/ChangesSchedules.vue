<script setup lang="ts">

import DatePicker from 'primevue/datepicker';
import ChangesScheduleItem from '@/components/ChangesScheduleItem.vue';
import { computed, onMounted, ref, watch } from "vue";
import { useChangesSchedulesQuery } from '@/queries/schedules';
import { useDateFormat } from '@vueuse/core';
import { useScheduleStore } from '@/stores/schedule';
import { storeToRefs } from 'pinia';
import router from '@/router';
import { useRoute } from 'vue-router';

const route = useRoute()

const scheduleStore = useScheduleStore()
const { date, queryParams, schedulesChanges } = storeToRefs(scheduleStore)
const { setSchedulesChanges, } = scheduleStore

const isoDate = computed(() => {
    const formatted = date.value ? useDateFormat(date.value, 'YYYY/MM/DD').value : null
    return formatted;
})

const { data: changesSchedules, isFetched } = useChangesSchedulesQuery(isoDate)

const updateQueryParams = () => {
    router.replace({
        query: {
            ...route.query,
            date: isoDate.value || null,
        },
    });
};

watch(changesSchedules, (newData) => {
    if (newData) {
        setSchedulesChanges(newData)
    }
})


watch(
    isoDate,
    () => {
        updateQueryParams();
    },
    { deep: true }
);

onMounted(() => {
    if (!queryParams.value.date || !/^\d{4}\/\d{2}\/\d{2}$/.test(queryParams.value.date as string)) {
        if (!date.value) date.value = new Date()
        updateQueryParams()
        return
    }
    updateQueryParams()
    date.value = new Date(Date.parse(queryParams.value.date as string))

});
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Расписание (изменения)</h1>
        </div>
        <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center">
                <DatePicker v-model="date" />
                <span>{{ useDateFormat(date, 'dddd').value }}</span>
            </div>

        </div>
        <div class="schedules">
            <ChangesScheduleItem class="schedule" v-for="item in schedulesChanges" :date="isoDate"
                :schedule="item.schedule" :semester="item.semester" :type="item.schedule.type" :group="item.group"
                :lessons="item?.schedule?.lessons">
            </ChangesScheduleItem>

        </div>
    </div>
</template>

<style scoped>
.schedules {
    display: flex;
    flex-wrap: wrap;
    row-gap: 2rem;
    column-gap: 10px;
    justify-content: space-between;

}

.schedule {
    min-width: 440px;
    flex: 0 1 calc(25% - 10px);

}
</style>