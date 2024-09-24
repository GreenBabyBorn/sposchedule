<script setup lang="ts">
import DatePicker from 'primevue/datepicker';
import ScheduleItem from '@/components/schedule/PublicScheduleItem.vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useCoursesQuery, usePublicSchedulesQuery } from '@/queries/schedules';
import { useDateFormat, useDebounceFn } from '@vueuse/core';
import { storeToRefs } from 'pinia';
import router from '@/router';
import { useRoute } from 'vue-router';
import Select from 'primevue/select';
import { useStorage } from '@vueuse/core';
import { useGroupsPublicQuery, useGroupsQuery } from '@/queries/groups';
import { useSchedulePublicStore } from '@/stores/schedulePublic';
import Skeleton from 'primevue/skeleton';
import { usePublicBellsQuery } from '@/queries/bells';
import PublicRowPeriodBell from '@/components/bells/PublicRowPeriodBell.vue';
import { useAuthStore } from '@/stores/auth';
import { useBuildingsQuery } from '@/queries/buildings';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

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

const cabinet = ref('')
const searchedCabinet = ref('')


const debouncedCabinetFn = useDebounceFn(() => {
    searchedCabinet.value = cabinet.value
}, 500, { maxWait: 1000 })

const teacher = ref('')
const searchedTeacher = ref('')

const debouncedTeacherFn = useDebounceFn(() => {
    searchedTeacher.value = teacher.value
}, 500, { maxWait: 1000 })

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

const { data: changesSchedules, isFetched, error, isError, isLoading } = usePublicSchedulesQuery(isoDate, building, selectedCourse, selectedGroup, searchedCabinet, searchedTeacher);
searchedTeacher
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

// console.log(route.query)

watch(changesSchedules, (newData) => {
    if (newData) {
        setSchedulesChanges(newData);
    }
});

const queryString = ref()


watch(() => route.query, () => {
    queryString.value = route.query as any
    // const filteredQuery = Object.fromEntries(
    //     Object.entries(route?.query).filter(([key, value]) => value !== null && value !== undefined)
    // );
    // queryString.value = new URLSearchParams(filteredQuery as any).toString()
})
// const queryString = new URLSearchParams(filteredQuery as any).toString();
watch([isoDate, selectedCourse, selectedGroup, building], () => {
    updateQueryParams();

}, { deep: true });




watch(building, () => {
    course.value = null
    selectedGroup.value = null
}, { flush: 'sync' })
watch(course, () => {
    selectedGroup.value = null
}, { flush: 'sync' })

onMounted(() => {
    // const filteredQuery = Object.fromEntries(
    //     Object.entries(route.query).filter(([key, value]) => value !== null && value !== undefined)
    // );
    // queryString.value = new URLSearchParams(filteredQuery as any).toString()
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

const headerRef = ref(null);
const headerHeight = ref(0);

const updateHeaderHeight = () => {
    if (headerRef.value) {
        headerHeight.value = headerRef.value.offsetHeight;
    }
};

// Используем ResizeObserver для отслеживания изменений размеров header
let resizeObserver = null;

onMounted(() => {
    resizeObserver = new ResizeObserver(updateHeaderHeight);
    if (headerRef.value) {
        resizeObserver.observe(headerRef.value);
        // Обновляем высоту при первой загрузке
        updateHeaderHeight();
    }
});

onBeforeUnmount(() => {
    if (resizeObserver && headerRef.value) {
        resizeObserver.unobserve(headerRef.value);
    }
});

const showFilters = ref(false)

function toggleFilters() {
    showFilters.value = !showFilters.value
    searchedCabinet.value = ''
    cabinet.value = ''
    searchedTeacher.value = ''
    teacher.value = ''
}
</script>

<template>
    <div class="max-w-screen-xl mx-auto px-4 py-4 flex flex-col gap-4">
        <RouterLink replace title="В панель управления" v-if="isAuth"
            class=" pi pi-pen-to-square text-white dark:text-surface-900 bg-primary-500 rounded-full p-4 fixed bottom-6 right-6 z-50"
            :to="{ path: '/admin/schedules/changes', query: queryString }"></RouterLink>
        <div ref="headerRef"
            class="fixed rounded-lg rounded-t-none max-w-screen-xl mx-auto z-50 top-0 left-0 right-0 flex flex-wrap justify-between gap-4 p-4 bg-surface-100 dark:bg-surface-800">

            <div class="flex flex-col flex-wrap gap-4 justify-between ">

                <div class="flex flex-wrap gap-2 items-center">
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
                    <div class="flex gap-2">

                        <Select :autoFilterFocus="true" emptyFilterMessage="Группы не найдены" filter showClear
                            v-model="selectedGroup" optionValue="name" :options="groups" optionLabel="name"
                            placeholder="Группа" class="w-full md:w-[10rem]" />
                        <Button title="Фильтры" @click="toggleFilters" severity="secondary" text
                            icon="pi pi-sliders-h"></Button>
                    </div>

                </div>

                <div v-show="showFilters" class="flex flex-wrap gap-2 items-center">
                    <InputText class="w-full md:w-auto" @input="debouncedCabinetFn" v-model="cabinet"
                        placeholder="Поиск по кабинету">
                    </InputText>
                    <InputText class="w-full md:w-auto" @input="debouncedTeacherFn" v-model="teacher"
                        placeholder="Поиск по преподавателю">
                    </InputText>
                </div>
            </div>

            <div class="">

                <div v-if="schedulesChanges?.last_updated"
                    class="flex gap-1 flex-row items-center lg:flex-col lg:gap-0 lg:items-end flex-wrap">
                    <span class="text-xs text-surface-400 leading-none text-nowrap">Последние обновление:</span>
                    <time title="Последние обновление" class="text-sm text-right text-surface-400"
                        :datetime="schedulesChanges?.last_updated">{{
                            useDateFormat(schedulesChanges?.last_updated,
                                'DD.MM.YYYY HH:mm') }}</time>
                </div>
            </div>


        </div>
        <div :style="{ marginTop: `${headerHeight + 10}px` }" class="flex flex-col gap-4">
            <div class="schedules">
                <div v-if="isLoading" v-for="item in 32" class="schedule">
                    <Skeleton height="2rem" class="mb-4">
                    </Skeleton>
                    <Skeleton height="10rem">
                    </Skeleton>
                </div>
                <span v-else-if="isFetched && !schedulesChanges?.schedules.length" class="text-2xl text-center">Группы
                    на
                    выбранную дату
                    не
                    найдены...</span>
                <span class="text-2xl" v-else-if="isError">Расписание ещё не выложили, либо в расписании ошибка.</span>
                <template v-else-if="schedulesChanges?.schedules">
                    <ScheduleItem class="schedule" v-for="item in schedulesChanges?.schedules" :key="item?.id"
                        :date="isoDate" :schedule="item?.schedule" :semester="item?.semester"
                        :type="item?.schedule?.type" :group_name="item?.group_name" :lessons="item?.schedule?.lessons"
                        :week_type="item?.week_type" :published="item?.schedule?.published">
                    </ScheduleItem>
                </template>

            </div>
        </div>
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold text-center py-2  ">Звонки</h1>
            <div class="">
                <h2 class="text-xl text-center text-red-300" v-if="!building">Необходимо выбрать корпус</h2>
                <h2 class="text-2xl text-center " v-else-if="!publicBells && isFetchedBells">На эту дату расписание
                    звонков не
                    найдено
                </h2>
                <div v-if="publicBells" class="flex justify-center items-center ">
                    <div class="overflow-x-auto rounded-md">
                        <table class="min-w-full border-collapse table-auto">
                            <!-- <thead>
                                <tr>
                                    <th
                                        class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                        № пары</th>
                                    <th
                                        class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                        График</th>
                                    <th
                                        class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                        Конец</th>
                                </tr>
                            </thead> -->
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
    display: grid;
    row-gap: 2rem;
    column-gap: 10px;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.schedules>*:only-child {
    justify-self: center;
    /* Центрирование */
    width: 300px;
    /* Установите минимальную или фиксированную ширину */
}

@media screen and (max-width: 768px) {
    .schedules>*:only-child {
        justify-self: center;
        /* Центрирование */
        width: 100%;
        /* Установите минимальную или фиксированную ширину */
    }
}
</style>