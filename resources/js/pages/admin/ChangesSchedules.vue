<script setup lang="ts">
import DatePicker from 'primevue/datepicker';
import ChangesScheduleItem from '@/components/ChangesScheduleItem.vue';
import { computed, onMounted, ref, watch } from "vue";
import { useChangesSchedulesQuery, useCoursesQuery } from '@/queries/schedules';
import { useDateFormat, useNow } from '@vueuse/core';
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
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
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
    localStorage.value.course = selectedCourse.value; 2
};

watch(changesSchedules, (newData) => {
    if (newData) {
        setSchedulesChanges(newData);
    }
});

watch([isoDate, selectedCourse], updateQueryParams, { deep: true });

onMounted(() => {
    const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2])\.(\d{4})$/;
    // Восстанавливаем значения сначала из query параметров, если они есть

    if (route.query.date && dateRegex.test(route.query.date as string)) {
        // Если дата есть в query параметрах, используем ее
        const [day, month, year] = (route.query.date as string).split('.').map(Number);
        date.value = new Date(year, month - 1, day);
        localStorage.value.date = route.query.date as string; // Сохраняем в localStorage
    } else if (localStorage.value.date && dateRegex.test(route.query.date as string)) {
        // Если даты нет в query, используем из localStorage
        const [day, month, year] = localStorage.value.date.split('.').map(Number);
        date.value = new Date(year, month - 1, day);
    } else {
        // Если нет даты ни в query, ни в localStorage, используем текущую дату
        date.value = new Date();
    }

    if (route.query.course) {
        // Если курс есть в query параметрах, используем его
        course.value = { course: Number(route.query.course as string) };
        localStorage.value.course = route.query.course as string; // Сохраняем в localStorage
    } else if (localStorage.value.course) {
        // Если курса нет в query, используем из localStorage
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
            <h1 class="text-2xl">Расписание изменений
            </h1>
        </div>
        <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center">
                <DatePicker showIcon iconDisplay="input" :invalid="isError" dateFormat="dd.mm.yy" v-model="date">
                    <template #inputicon="slotProps">
                        <div @click="slotProps.clickCallback" class="flex gap-2 justify-between items-center">
                            <small>{{ useDateFormat(date, 'dddd', {
                                locales: 'ru-RU'
                            }).value.toUpperCase()
                                }}</small>
                            <small>{{ schedulesChanges?.week_type }}</small>
                        </div>

                    </template>
                </DatePicker>
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