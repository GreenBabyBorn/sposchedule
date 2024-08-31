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
import { useStorage } from '@vueuse/core';
import ProgressSpinner from 'primevue/progressspinner';
import { useGroupsQuery } from '@/queries/groups';
import { useSchedulePublicStore } from '@/stores/schedulePublic';
import Skeleton from 'primevue/skeleton';

const route = useRoute();
const scheduleStore = useSchedulePublicStore();
const { course, date, queryParams, schedulesChanges, selectedGroup } = storeToRefs(scheduleStore);
const { setSchedulesChanges } = scheduleStore;

// Хранение query параметров в localStorage
const localStorage = useStorage('publicSchedules', {
    date: '', course: '', group: ''
});

const isoDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});

const { data: courses, isFetched: coursesFetched } = useCoursesQuery();

const selectedCourse = computed(() => {
    return course.value?.course;
});

const { data: changesSchedules, isFetched, error, isError, isLoading } = usePublicSchedulesQuery(isoDate, selectedCourse, selectedGroup);

const updateQueryParams = () => {
    router.replace({
        query: {
            ...route.query,
            date: isoDate.value || null,
            course: selectedCourse.value || null,
            group: selectedGroup.value || null,
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
    // Восстанавливаем значения сначала из query параметров, если они есть
    if (route.query.date) {
        // Если дата есть в query параметрах, используем ее
        const [day, month, year] = (route.query.date as string).split('.').map(Number);
        date.value = new Date(year, month - 1, day);
        localStorage.value.date = route.query.date as string; // Сохраняем в localStorage
    } else if (localStorage.value.date) {
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

    if (route.query.group) {
        // Если группы нет в query, используем из localStorage
        selectedGroup.value = route.query.group;
        localStorage.value.group = route.query.group as string; // Сохраняем в localStorage
    } else if (localStorage.value.group) {
        selectedGroup.value = localStorage?.value?.group;
    }

    // Синхронизация параметров в URL
    updateQueryParams();
});

const { data: groups } = useGroupsQuery(selectedGroup);

</script>

<template>
    <div class="max-w-screen-xl mx-auto px-4 py-4">
        <div class="flex flex-col gap-4">

            <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
                <div class="flex flex-wrap gap-2 items-start w-full">
                    <div class="flex flex-col ">
                        <DatePicker fluid showIcon iconDisplay="input" :invalid="isError" dateFormat="dd.mm.yy"
                            v-model="date">
                            <template #inputicon="slotProps">
                                <div @click="slotProps.clickCallback" class="flex gap-2 justify-between items-center">
                                    <small>{{ useDateFormat(date, 'dddd').value.toUpperCase()
                                        }}</small>
                                    <small>{{ schedulesChanges?.week_type }}</small>
                                </div>

                            </template>
                        </DatePicker>



                    </div>

                    <Select emptyFilterMessage="Группы не найдены" :virtualScrollerOptions="{ itemSize: 38 }"
                        :disabled="Boolean(course)" filter showClear v-model="selectedGroup" optionValue="name"
                        :options="groups" optionLabel="name" placeholder="Группа" class="w-full md:w-[10rem]" />
                    <Select :disabled="Boolean(selectedGroup)" class="" showClear v-model="course" :options="courses"
                        option-label="course" placeholder="Курс"></Select>


                </div>

            </div>
            <!-- <ProgressSpinner v-show="isLoading" /> -->
            <div class="schedules">
                <div v-if="isLoading" v-for="item in 32" class="schedule">
                    <Skeleton height="2rem" class="mb-4">
                    </Skeleton>
                    <Skeleton height="10rem">
                    </Skeleton>
                </div>
                <span class="text-2xl" v-else-if="isError">Расписание ещё не выложили, либо в расписании ошибка.</span>
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