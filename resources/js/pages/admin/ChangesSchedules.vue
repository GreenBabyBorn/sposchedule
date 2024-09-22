<script setup lang="ts">

import DatePicker from 'primevue/datepicker';
import ChangesScheduleItem from '@/components/schedule/AdminChangesScheduleItem.vue';
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { useChangesSchedulesQuery, useCoursesQuery } from '@/queries/schedules';
import { useDateFormat, useNow } from '@vueuse/core';
import { useScheduleStore } from '@/stores/schedule';
import { storeToRefs } from 'pinia';
import router from '@/router';
import { useRoute } from 'vue-router';
import Select from 'primevue/select';
import { useStorage } from '@vueuse/core'
// import ProgressSpinner from 'primevue/progressspinner';
// import Button from 'primevue/button';
import { useTeachersQuery } from '@/queries/teachers';
import { useSubjectsQuery } from '@/queries/subjects';
import { useBuildingsQuery } from '@/queries/buildings';
import { useGroupsPublicQuery } from '@/queries/groups';




const route = useRoute()
const scheduleStore = useScheduleStore();
const { course, date, queryParams, schedulesChanges } = storeToRefs(scheduleStore);
const { setSchedulesChanges } = scheduleStore;

const { data: subjects } = useSubjectsQuery()
const { data: teachers } = useTeachersQuery()

// Хранение query параметров в localStorage
// const localStorage = useStorage('changesSchedules', { date: '', course: '' });

const isoDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});

const selectedGroup = ref()
const building = ref(null)

// const { data: courses, isFetched: coursesFetched } = useCoursesQuery();
const { data: courses, isFetched: coursesFetched } = useCoursesQuery(building);

const coursesWithLabel = computed(() => {
    return courses.value?.map(course => ({
        label: `${course.course} курс`,
        value: course.course
    })) || [];

})

const selectedCourse = computed(() => {
    return course.value
});




const updateQueryParams = () => {
    router.replace({
        query: {
            ...route.query,
            date: isoDate.value || null,
            course: selectedCourse.value || null,
            building: building.value || null,
            group: selectedGroup.value || null,
        },
    });

    // Обновляем localStorage при изменении query параметров
    // if (isoDate.value) localStorage.value.date = isoDate.value;
    // localStorage.value.course = selectedCourse.value; 2
};

let initialized = ref(false);

watch([isoDate, building, selectedCourse, selectedGroup], () => {
    if (initialized.value) {
        updateQueryParams()
    }
}, { deep: true });

onMounted(() => {
    const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2])\.(\d{4})$/;
    // Восстанавливаем значения сначала из query параметров, если они есть

    if (route.query.date && dateRegex.test(route.query.date as string)) {
        // Если дата есть в query параметрах, используем ее
        const [day, month, year] = (route.query.date as string).split('.').map(Number);
        date.value = new Date(year, month - 1, day);
        // localStorage.value.date = route.query.date as string; // Сохраняем в localStorage
    }
    // else if (localStorage.value.date && dateRegex.test(route.query.date as string)) {
    //     // Если даты нет в query, используем из localStorage
    //     const [day, month, year] = localStorage.value.date.split('.').map(Number);
    //     date.value = new Date(year, month - 1, day);
    // } 
    else {
        // Если нет даты ни в query, ни в localStorage, используем текущую дату
        date.value = new Date();
    }

    if (route.query.course) {
        // Если курс есть в query параметрах, используем его
        course.value = Number(route.query.course as string);
        // localStorage.value.course = route.query.course as string; // Сохраняем в localStorage
    }

    if (route.query.group) {
        selectedGroup.value = route.query.group as string;
    }

    if (route.query.building) {
        building.value = route.query.building as string;
    }
    // else if (localStorage.value.course) {
    //     // Если курса нет в query, используем из localStorage
    //     // course.value = localStorage.value.course;
    // }

    // Синхронизация параметров в URL
    initialized.value = true;
    updateQueryParams();
})

const reducedWeekDays = {
    'понедельник': 'ПН',
    'вторник': 'ВТ',
    'среда': 'СР',
    'четверг': 'ЧТ',
    'пятница': 'ПТ',
    'суббота': 'СБ',
    'воскресенье': 'ВС',
}


const { data: groups } = useGroupsPublicQuery(selectedGroup, building, course);

const { data: buildingsFethed } = useBuildingsQuery()
const buildings = computed(() => {
    return buildingsFethed.value?.map(building => ({
        value: building.name,
        label: `${building.name} корпус`,
    })) || [];
})

watch(building, () => {
    if (initialized.value) {
        course.value = null
        selectedGroup.value = null
    }
}, { flush: 'sync' })
watch(course, () => {
    if (initialized.value) {
        selectedGroup.value = null
    }
}, { flush: 'sync' })
const { data: changesSchedules, isFetched, error, isError, isLoading } = useChangesSchedulesQuery(isoDate, building, selectedCourse, selectedGroup);

watch(changesSchedules, (newData) => {

    if (newData) {
        setSchedulesChanges(newData);
    }
},
    { deep: true }
);

</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Расписание изменений
            </h1>
        </div>
        <div class="flex  items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center flex-wrap">
                <DatePicker class="shrink-0" showIcon iconDisplay="input" :invalid="isError" dateFormat="dd.mm.yy"
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
                <Select title="Корпус" showClear v-model="building" :options="buildings" option-label="label"
                    option-value="value" placeholder="Корпус"></Select>
                <Select class="" showClear v-model="course" :options="coursesWithLabel" option-label="label"
                    option-value="value" placeholder="Курс"></Select>
                <Select :autoFilterFocus="true" emptyFilterMessage="Группы не найдены" filter showClear
                    v-model="selectedGroup" optionValue="name" :options="groups" optionLabel="name" placeholder="Группа"
                    class="w-full md:w-[10rem]" />
                <a class="pi pi-print relative items-center inline-flex text-center align-bottom justify-center leading-[normal] px-3 py-2 rounded-md text-primary-contrast bg-primary border border-primary focus:outline-none focus:outline-offset-0 focus:ring-1 hover:bg-primary-emphasis hover:border-primary-emphasis focus:ring-primary transition duration-200 ease-in-out cursor-pointer overflow-hidden select-none"
                    target="_blank" title="На печать" :href="`/print/changes?date=${isoDate}`"></a>
            </div>
        </div>

        <div class="schedules">
            <span v-if="isError">Семестра на данную дату не найдено, чтобы добавить перейдите на экран добавления
                <RouterLink class="underline" to="/admin/semesters">семестра</RouterLink>
            </span>

            <ChangesScheduleItem :key="index" :subjects="subjects" :teachers="teachers" class="schedule"
                v-for="(item, index) in schedulesChanges?.schedules" :date="isoDate" :schedule="item?.schedule"
                :semester="item?.semester" :type="item?.schedule?.type" :group="item?.group"
                :lessons="item?.schedule?.lessons" :week_type="item?.week_type" :published="item?.schedule?.published">
            </ChangesScheduleItem>
        </div>
    </div>
</template>

<style scoped>
.schedules {
    display: flex;
    /* flex-direction: column; */
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