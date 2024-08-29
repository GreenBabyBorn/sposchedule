<script setup lang="ts">
import DatePicker from 'primevue/datepicker';
import ChangesScheduleItem from '@/components/ChangesScheduleItem.vue';
import { computed, onMounted, ref, watch } from "vue";
import { useChangesSchedulesQuery, useCoursesQuery } from '@/queries/schedules';
import { useDateFormat } from '@vueuse/core';
import { useScheduleStore } from '@/stores/schedule';
import { storeToRefs } from 'pinia';
import router from '@/router';
import { useRoute } from 'vue-router';
import Select from 'primevue/select';
import { useStorage } from '@vueuse/core'
import ProgressSpinner from 'primevue/progressspinner';

const route = useRoute()
const scheduleStore = useScheduleStore();
const { course, date, queryParams, schedulesChanges } = storeToRefs(scheduleStore);
const { setSchedulesChanges } = scheduleStore;

// Хранение query параметров в localStorage
const localStorage = useStorage('changesSchedules', { date: '', course: '' });

const isoDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'YYYY/MM/DD').value : null;
});

const { data: courses, isFetched: coursesFetched } = useCoursesQuery();


const selectedCourse = computed(() => {


    return course.value?.course
});

const { data: changesSchedules, isFetched, error, isError, isLoading } = useChangesSchedulesQuery(isoDate, selectedCourse);

const updateQueryParams = () => {
    router.replace({
        query: {
            ...route.query,
            date: isoDate.value || null,
            course: selectedCourse.value || null,
        },
    });

    // Обновляем localStorage при изменении query параметров
    if (isoDate.value) localStorage.value.date = isoDate.value;
    if (selectedCourse.value) localStorage.value.course = selectedCourse.value;
};

watch(changesSchedules, (newData) => {
    if (newData) {
        setSchedulesChanges(newData);
    }
});

watch([isoDate, selectedCourse], updateQueryParams, { deep: true });

onMounted(() => {
    // Восстанавливаем значения из localStorage при загрузке
    if (localStorage.value.date) {
        date.value = new Date(Date.parse(localStorage.value.date));
    } else if (!date.value) {
        date.value = new Date();
    }

    if (localStorage.value.course) {
        course.value = { course: localStorage.value.course };
    }

    // Синхронизация параметров в URL
    updateQueryParams();
}
)


</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Расписание (изменения) | <span>{{ useDateFormat(date, 'dddd').value.toUpperCase()
                    }}</span> |
                <span>{{ schedulesChanges?.week_type }}</span>
            </h1>
        </div>
        <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center">
                <DatePicker :invalid="isError" v-model="date" />
                <Select class="basis-1/5" showClear v-model="course" :options="courses" option-label="course"
                    placeholder="Курс"></Select>


            </div>

        </div>
        <ProgressSpinner v-show="isLoading" />
        <div class="schedules">
            <span v-if="isError">Семестра на данную дату не найдено, чтобы добавить перейдите на экран добавления
                <RouterLink class="underline" to="/admin/semesters">семестра</RouterLink>
            </span>
            <ChangesScheduleItem v-else class="schedule" v-for="item in schedulesChanges?.schedules" :date="isoDate"
                :schedule="item.schedule" :semester="item.semester" :type="item.schedule.type" :group="item.group"
                :lessons="item?.schedule?.lessons" :week_type="item.week_type" :published="item?.schedule.published">
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