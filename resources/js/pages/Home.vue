<script setup lang="ts">
import DatePicker from 'primevue/datepicker';
import ScheduleItem from '@/components/ScheduleItem.vue';
import { computed, onMounted, ref, watch } from "vue";
import { useCoursesQuery, usePublicSchedulesQuery } from '@/queries/schedules';
import { useDateFormat } from '@vueuse/core';
import { useScheduleStore } from '@/stores/schedule';
import { storeToRefs } from 'pinia';
import router from '@/router';
import { useRoute } from 'vue-router';
import Select from 'primevue/select';
import { useStorage } from '@vueuse/core'
import ProgressSpinner from 'primevue/progressspinner';
import { useGroupsQuery } from '@/queries/groups';
import { useSchedulePublicStore } from '@/stores/schedulePublic';

const route = useRoute()
const scheduleStore = useSchedulePublicStore();
const { course, date, queryParams, schedulesChanges, selectedGroup } = storeToRefs(scheduleStore);
const { setSchedulesChanges } = scheduleStore;

// Хранение query параметров в localStorage
const localStorage = useStorage('publicSchedules', { date: '', course: '', group: '' });

const isoDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'YYYY/MM/DD').value : null;
});

const { data: courses, isFetched: coursesFetched } = useCoursesQuery();


const selectedCourse = computed(() => {


    return course.value?.course
});

const { data: changesSchedules, isFetched, error, isError, isLoading } = usePublicSchedulesQuery(isoDate, selectedCourse, selectedGroup);

const updateQueryParams = () => {
    router.replace({
        query: {
            ...route.query,
            date: isoDate.value || null,
            course: selectedCourse.value || null,
            group: selectedGroup.value?.name || null,
        },
    });

    // Обновляем localStorage при изменении query параметров
    if (isoDate.value) localStorage.value.date = isoDate.value;
    localStorage.value.course = selectedCourse.value;
    localStorage.value.group = selectedGroup.value;
};

watch(changesSchedules, (newData) => {
    if (newData) {
        setSchedulesChanges(newData);
    }
});

watch([isoDate, selectedCourse, selectedGroup], updateQueryParams, { deep: true });

onMounted(() => {
    // Восстанавливаем значения из localStorage при загрузке
    if (localStorage.value.date) {
        date.value = new Date(Date.parse(localStorage.value.date));
    } else if (!date.value) {
        date.value = new Date();
    }

    if (localStorage.value.course) {
        course.value = { course: localStorage.value.course };
    } if (localStorage.value.group) {
        selectedGroup.value = localStorage.value.group;
    }

    // Синхронизация параметров в URL
    updateQueryParams();
}
)

const { data: groups } = useGroupsQuery();
</script>

<template>
    <div class="max-w-screen-xl mx-auto px-4 py-4">
        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap justify-between items-baseline">
                <h1 class="text-2xl">Расписание | <span>{{ useDateFormat(date, 'dddd').value.toUpperCase()
                        }}</span> |
                    <span>{{ schedulesChanges?.week_type }}</span>
                </h1>
            </div>
            <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
                <div class="flex flex-wrap gap-2 items-center w-full">

                    <DatePicker id="date" :invalid="isError" dateFormat="dd.mm.yy" v-model="date" />
                    <Select :disabled="course" editable showClear v-model="selectedGroup" :options="groups"
                        optionLabel="name" placeholder="Группа" class="w-full md:w-[10rem]" />
                    <Select :disabled="selectedGroup" class="" showClear v-model="course" :options="courses"
                        option-label="course" placeholder="Курс"></Select>


                </div>

            </div>
            <ProgressSpinner v-show="isLoading" />
            <div class="schedules">
                <span class="text-2xl" v-if="isError">Расписание ещё не выложили, либо в расписании ошибка.</span>
                <ScheduleItem v-else class="schedule" v-for="item in schedulesChanges?.schedules" :date="isoDate"
                    :schedule="item.schedule" :semester="item.semester" :type="item.schedule.type" :group="item.group"
                    :lessons="item?.schedule?.lessons" :week_type="item.week_type"
                    :published="item?.schedule.published">
                </ScheduleItem>
            </div>
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
    /* min-width: 440px; */
    flex: 0 1 calc(25% - 10px);

}

@media screen and (max-width: 768px) {
    .schedule {
        /* min-width: 440px; */
        flex: 0 1 calc(100% - 10px);

    }
}

/* @media screen and (max-width: 480px) {
    .schedule {
        flex: 0 1 calc(100% - 10px);
    }
} */
</style>