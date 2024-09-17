<script setup lang="ts">
import DatePicker from 'primevue/datepicker';
import ScheduleItem from '@/components/ScheduleItem.vue';
import { computed, onMounted, ref, watch } from "vue";
import { useCoursesQuery, usePublicSchedulesQuery } from '@/queries/schedules';
import { useDateFormat } from '@vueuse/core';
import { storeToRefs } from 'pinia';
import router from '@/router';
import { useRoute } from 'vue-router';
import Select from 'primevue/select';
import { useStorage } from '@vueuse/core';
import { useGroupsPublicQuery, useGroupsQuery } from '@/queries/groups';
import { useSchedulePublicStore } from '@/stores/schedulePublic';
import Skeleton from 'primevue/skeleton';
import { usePublicBellsQuery } from '@/queries/bells';
import PublicRowPeriodBell from '@/components/PublicRowPeriodBell.vue';
import { useAuthStore } from '@/stores/auth';
import { useBuildingsQuery } from '@/queries/buildings';

const route = useRoute();
const scheduleStore = useSchedulePublicStore();
const { course, date, queryParams, schedulesChanges, selectedGroup } = storeToRefs(scheduleStore);
const { setSchedulesChanges } = scheduleStore;

const localStorage = useStorage('publicSchedules', {
    date: '', course: '', group: '', building: ''
});

const isoDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});



const coursesWithLabel = computed(() => {
    return courses.value?.map(course => ({
        label: `${course.course} курс`,
        value: course.course
    })) || [];

})

const selectedCourse = computed(() => {
    return course.value;
});

const building = ref(null)
const { data: buildingsFethed } = useBuildingsQuery()
const buildings = computed(() => {
    return buildingsFethed.value?.map(building => ({
        value: building.name,
        label: `${building.name} корпус`,
    })) || [];
})

const { data: courses, isFetched: coursesFetched } = useCoursesQuery(building);
// const buildings = ref([
//     {
//         value: 1,
//         label: '1 корпус',
//     },
//     {
//         value: 2,
//         label: '2 корпус',
//     },
//     {
//         value: 3,
//         label: '3 корпус',
//     },
//     {
//         value: 4,
//         label: '4 корпус',
//     },
//     {
//         value: 5,
//         label: '5 корпус',
//     },
//     {
//         value: 6,
//         label: '6 корпус',
//     },
// ])

const { data: changesSchedules, isFetched, error, isError, isLoading } = usePublicSchedulesQuery(isoDate, building, selectedCourse, selectedGroup);

const updateQueryParams = () => {
    router.replace({
        query: {
            ...route.query,
            date: isoDate.value || null,
            building: building.value || null,
            course: selectedCourse.value || null,
            group: selectedGroup.value || null,
        },
    });

    // if (isoDate.value) localStorage.value.date = isoDate.value;
    localStorage.value.building = building.value;
    localStorage.value.course = selectedCourse.value;
    localStorage.value.group = selectedGroup.value;
};

watch(changesSchedules, (newData) => {
    if (newData) {
        setSchedulesChanges(newData);
    }
});

watch([isoDate, selectedCourse, selectedGroup, building], updateQueryParams, { deep: true });

onMounted(() => {
    const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2])\.(\d{4})$/;

    if (route.query.date && dateRegex.test(route.query.date as string)) {


        const [day, month, year] = (route.query.date as string).split('.').map(Number);
        date.value = new Date(year, month - 1, day);
        localStorage.value.date = route.query.date as string;
    }
    // else if (localStorage.value.date && dateRegex.test(localStorage.value.date as string)) {
    //     // Если даты нет в query, используем из localStorage
    //     const [day, month, year] = localStorage.value.date.split('.').map(Number);
    //     date.value = new Date(year, month - 1, day);
    // }
    else {
        date.value = new Date();
    }

    if (route.query.building) {
        building.value = route.query.building as string;
        localStorage.value.building = route.query.building as string;
    } else if (localStorage.value.building) {
        building.value = localStorage.value.building;
    }

    if (route.query.course) {
        course.value = Number(route.query.course as string);
        localStorage.value.course = route.query.course as string;
    } else if (localStorage.value.course) {
        course.value = localStorage.value.course;
    }

    if (route.query.group) {

        selectedGroup.value = route.query.group;
        localStorage.value.group = route.query.group as string;
    } else if (localStorage.value.group) {
        selectedGroup.value = localStorage?.value?.group;
    }

    updateQueryParams();
});

const { data: groups } = useGroupsPublicQuery(selectedGroup, building, course);


const formattedDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});

const { data: publicBells, isFetched: isFetchedBells } = usePublicBellsQuery(building, formattedDate)



const authStore = useAuthStore()
const { user, isAuth } = storeToRefs(authStore)

const reducedWeekDays = {
    'понедельник': 'ПН',
    'вторник': 'ВТ',
    'среда': 'СР',
    'четверг': 'ЧТ',
    'пятница': 'ПТ',
    'суббота': 'СБ',
    'воскресенье': 'ВС',
}
</script>

<template>
    <div class="max-w-screen-xl mx-auto px-4 py-4 flex flex-col gap-4">
        <RouterLink title="В панель управления" v-if="isAuth"
            class="pi pi-pen-to-square text-white dark:text-surface-900 bg-primary-500 rounded-full p-4 fixed bottom-6 right-6  z-50"
            to="/admin/schedules/changes"></RouterLink>
        <!-- <Button label="В панель управления" /> -->
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
                <div class="flex flex-wrap gap-2 items-start w-full">
                    <div class="flex flex-col md:w-auto w-full">
                        <DatePicker fluid showIcon iconDisplay="input" :invalid="isError" dateFormat="dd.mm.yy"
                            v-model="date">
                            <template #inputicon="slotProps">
                                <div @click="slotProps.clickCallback" class="flex gap-2 justify-between items-center">
                                    <small>{{ reducedWeekDays[useDateFormat(date, 'dddd', {
                                        locales: 'ru-RU'
                                    }).value]
                                        }}</small>
                                    <small>{{ schedulesChanges?.week_type }}</small>
                                </div>
                            </template>
                        </DatePicker>
                    </div>
                    <Select title="Корпус" showClear v-model="building" :options="buildings" option-label="label"
                        option-value="value" placeholder="Корпус"></Select>
                    <Select class="" showClear v-model="course" :options="coursesWithLabel" option-label="label"
                        option-value="value" placeholder="Курс"></Select>
                    <Select :autoFilterFocus="true" emptyFilterMessage="Группы не найдены" filter showClear
                        v-model="selectedGroup" optionValue="name" :options="groups" optionLabel="name"
                        placeholder="Группа" class="w-full md:w-[10rem]" />
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
                <span v-else-if="isFetched && !schedulesChanges.schedules.length" class="text-2xl text-center">Группы не
                    найдены</span>
                <span class="text-2xl" v-else-if="isError">Расписание ещё не выложили, либо в расписании ошибка.</span>
                <ScheduleItem v-else class="schedule" v-for="item in schedulesChanges?.schedules" :date="isoDate"
                    :schedule="item.schedule" :semester="item.semester" :type="item.schedule.type" :group="item.group"
                    :lessons="item?.schedule?.lessons" :week_type="item.week_type"
                    :published="item?.schedule.published">
                </ScheduleItem>
            </div>
        </div>
        <div class="flex flex-col gap-4">
            <h1 class="text-2xl font-bold text-center ">Звонки</h1>
            <!-- <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
                <div class="flex flex-wrap gap-2 items-start w-full">
                    
                </div>
            </div> -->
            <div class="">
                <h2 class="text-xl text-center text-red-300" v-if="!building">Необходимо выбрать корпус</h2>
                <h2 class="text-2xl text-center" v-else-if="!publicBells && isFetchedBells">На эту дату расписание
                    звонков не
                    найдено
                </h2>
                <div v-if="publicBells" class="flex justify-center items-center ">
                    <div
                        class="overflow-x-auto rounded-md border border-surface-200 dark:border-surface-800 dark:bg-surface-950">
                        <table class="min-w-full border-collapse table-auto">
                            <thead>
                                <tr>
                                    <th
                                        class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                        №</th>
                                    <th
                                        class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                        Начало</th>
                                    <th
                                        class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                        Конец</th>
                                </tr>
                            </thead>
                            <tbody>
                                <PublicRowPeriodBell :key="period.id" v-for="period in publicBells?.periods"
                                    :period="period">
                                </PublicRowPeriodBell>
                            </tbody>
                        </table>
                    </div>
                </div>
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
        flex: 1 1 calc(100% - 10px);

    }
}

/* @media screen and (max-width: 480px) {
    .schedule {
        flex: 0 1 calc(100% - 10px);
    }
} */
</style>