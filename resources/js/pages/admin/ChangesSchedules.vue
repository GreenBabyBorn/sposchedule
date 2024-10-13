<script setup lang="ts">
import DatePicker from 'primevue/datepicker';
import ChangesScheduleItem from '@/components/schedule/AdminChangesScheduleItem.vue';
import { computed, onMounted, ref, watch } from "vue";
import { useChangesSchedulesQuery, useCoursesQuery } from '@/queries/schedules';
import { useDateFormat } from '@vueuse/core';
import { useScheduleStore } from '@/stores/schedule';
import { storeToRefs } from 'pinia';
import router from '@/router';
import { useRoute } from 'vue-router';
import Select from 'primevue/select';
import Button from 'primevue/button';
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

const isoDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});

const selectedGroup = ref()
const building = ref(null)

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
            date: isoDate.value || undefined,
            building: building.value || undefined,
            course: selectedCourse.value || undefined,
            group: selectedGroup.value || undefined,
        },
    });
};

const { data: changesSchedules, isError, isSuccess } = useChangesSchedulesQuery(isoDate, building, selectedCourse, selectedGroup);

watch(changesSchedules, (newData) => {

    if (newData) {
        setSchedulesChanges(newData);
    }
},
    { deep: true }
);

watch([isoDate, building, selectedCourse, selectedGroup], () => {

    updateQueryParams()

}, { deep: true });

watch(building, () => {

    course.value = null
    selectedGroup.value = null

}, { flush: 'sync' })

watch(course, () => {
    selectedGroup.value = null
}, { flush: 'sync' })

onMounted(() => {
    const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2])\.(\d{4})$/;
    // Восстанавливаем значения сначала из query параметров, если они есть

    if (route.query.date && dateRegex.test(route.query.date as string)) {
        // Если дата есть в query параметрах, используем ее
        const [day, month, year] = (route.query.date as string).split('.').map(Number);
        date.value = new Date(year, month - 1, day);
    }

    else {
        // Если нет даты ни в query, ни в localStorage, используем текущую дату
        date.value = new Date();
    }
    if (route.query.building) {
        building.value = route.query.building as string;
    }
    if (route.query.course) {
        // Если курс есть в query параметрах, используем его
        course.value = Number(route.query.course as string);
        // localStorage.value.course = route.query.course as string; // Сохраняем в localStorage
    }

    if (route.query.group) {
        selectedGroup.value = route.query.group as string;
    }

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

function handleDatePickerBtns(day) {
    switch (day) {
        case 'today':
            date.value = new Date();
            break

        case 'tomorrow':
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            date.value = tomorrow;
            break

    }
}   
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Расписание (изменения)
            </h1>
        </div>
        <div class="flex  items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center flex-wrap">
                <DatePicker append-to="self" class="shrink-0" showIcon iconDisplay="input" :invalid="isError"
                    dateFormat="dd.mm.yy" v-model="date">
                    <template #inputicon="slotProps">
                        <div @click="slotProps.clickCallback" class="flex gap-2 justify-between items-center">
                            <small>{{ reducedWeekDays[useDateFormat(date, 'dddd', {
                                locales: 'ru-RU'
                            }).value]
                                }}</small>
                            <small>{{ schedulesChanges?.week_type }}</small>
                        </div>
                    </template>
                    <template #footer="slotProps">
                        <div class="flex justify-between pt-1">

                            <Button @click="handleDatePickerBtns('today')" severity="secondary" size="small"
                                label="Сегодня"></Button>
                            <Button @click="handleDatePickerBtns('tomorrow')" severity="secondary" size="small"
                                label="Завтра"></Button>
                        </div>
                    </template>
                </DatePicker>
                <Select title="Корпус" showClear v-model="building" :options="buildings" option-label="label"
                    option-value="value" placeholder="Корпус"></Select>
                <Select showClear v-model="course" :options="coursesWithLabel" option-label="label" option-value="value"
                    placeholder="Курс"></Select>
                <Select :autoFilterFocus="true" emptyFilterMessage="Группы не найдены" filter showClear
                    v-model="selectedGroup" optionValue="name" :options="groups" optionLabel="name"
                    placeholder="Группа" />
                <Button target="_blank" icon="pi pi-print" as="router-link" :to="{
                    path: '/print/changes',
                    query: {
                        date: isoDate
                    }
                }" />
            </div>
        </div>

        <span v-if="isError">Семестра на данную дату не найдено, чтобы добавить перейдите на экран добавления
            <RouterLink class="underline" to="/admin/semesters">семестра</RouterLink>
        </span>
        <div v-if="isSuccess" class="schedules">
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