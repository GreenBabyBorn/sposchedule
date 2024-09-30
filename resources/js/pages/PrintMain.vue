<script setup lang="ts">
import { useCoursesQuery, usePrintMainSchedulesQuery } from '@/queries/schedules';
import { computed, onMounted, ref, watch, onUpdated, nextTick, watchEffect } from 'vue';
import { useRoute, } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import { useSemesterShowQuery, useSemestersQuery } from '@/queries/semesters';
import { useBuildingsQuery } from '@/queries/buildings';
import MultiSelect from 'primevue/multiselect';
import Select from 'primevue/select';
import Button from 'primevue/button';
import LoadingBar from '@/components/LoadingBar.vue';
import router from '@/router';

const route = useRoute();

const selectedSemester = ref(null);
const { data: semesters, isFetched: semestersFetched } = useSemestersQuery()

const course = ref(null);
const { data: courses } = useCoursesQuery();

const coursesWithLabel = computed(() => {
    return courses.value?.map(course => ({
        label: `${course.course} курс`,
        value: course.course
    })) || [];

})

const { data: buildingsData, isFetched: buildingsFetched } = useBuildingsQuery()
const selectedBuildings = ref(null)
const buildings = computed(() => {
    return buildingsData.value?.map(building => ({
        value: building.name,
        label: `${building.name} корпус`,
    })) || [];
})


const semesterId = computed(() => {
    return selectedSemester.value?.id
})

const buildingsArray = computed(() => {
    return [selectedBuildings.value?.map(obj => obj.value)]
})


const { data: mainSchedules, isSuccess, isFetchedAfterMount } = usePrintMainSchedulesQuery(semesterId, course, buildingsArray);


const dayNamesWithPreposition = {
    ПН: 'понедельник',
    ВТ: 'вторник',
    СР: 'среда',
    ЧТ: 'четверг',
    ПТ: 'пятница',
    СБ: 'суббота',
    ВС: 'воскресенье'
};



const authStore = useAuthStore()
const { user, isAuth } = storeToRefs(authStore)


watch([isFetchedAfterMount, isSuccess], async () => {
    if (isFetchedAfterMount.value && isSuccess.value) {
        // Ждём, пока контент будет полностью отрендерен
        await nextTick();

        // window.print();

    }
});



const daysOfWeek = ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'];


const getIndexesFromWeekdays = computed(() => {
    const daysIndexes = {
        'ПН': new Set(),
        'ВТ': new Set(),
        'СР': new Set(),
        'ЧТ': new Set(),
        'ПТ': new Set(),
        'СБ': new Set(),
    }

    for (const item of mainSchedules?.value) {
        for (const day of daysOfWeek) {
            for (const index of item.schedule[day]) {
                daysIndexes[day].add(index.index);
            }
        }
    }
    const result = {};
    for (const day in daysIndexes) {
        result[day] = Array.from(daysIndexes[day]).sort((a: number, b: number) => a - b);
    }

    return result;
})

const { data: semester } = useSemesterShowQuery(semesterId)

function printPage() {
    window.print();
}

function updateQueryParams() {
    router.replace({
        query: {
            ...route.query,
            semester: semesterId.value || undefined,
            buildings: buildingsArray.value || undefined,
            course: course.value || undefined,
        },
    });
};

watch([semesterId, course, selectedBuildings], () => {
    updateQueryParams();

}, { deep: true });



watchEffect(() => {
    if (semestersFetched.value && buildingsFetched.value) {
        // Восстанавливаем семестр, если query параметр "semester" существует и данные загружены
        if (route.query.semester) {
            selectedSemester.value = semesters.value?.find(item => item.id === Number(route.query.semester));
        }

        // Восстанавливаем здания, если query параметр "buildings" существует и данные загружены
        if (route.query.buildings) {
            const buildingsQueryParam = route.query.buildings;

            // Если это массив, используем его напрямую
            // Если строка, разбиваем её по запятой
            const buildingNames = route.query.buildings.toString(); // если строка, разбиваем на массив

            selectedBuildings.value = buildings.value?.filter(building => buildingNames.includes(building.value));
        }

        // Восстанавливаем курс, если query параметр "course" существует
        if (route.query.course) {
            course.value = Number(route.query.course);
        }
    }
});

</script>

<template>
    <LoadingBar />
    <div class="controls py-2 flex  flex-wrap gap-2 items-center  pl-2">
        <Select show-clear v-model="selectedSemester" :options="semesters" placeholder="Семестры" option-label="name"
            class="" />
        <MultiSelect :max-selected-labels="2" :selectedItemsLabel="'{0} выбрано'" v-model="selectedBuildings"
            :options="buildings" placeholder="Корпуса" option-label="label" />
        <Select showClear v-model="course" :options="coursesWithLabel" option-label="label" option-value="value"
            placeholder="Курс"></Select>
        <Button label="Печать" @click="printPage()"
            :disabled="!course || !selectedBuildings || !selectedSemester || !isSuccess" icon="pi pi-print" />


    </div>
    <div v-if="mainSchedules" class="main">

        <div class="top">
            <div class="flex justify-end">
                <div :contenteditable="isAuth" class="text-right ">
                    УТВЕРЖДАЮ <br>
                    директор <br>
                    _________ Клочков А.Ю.
                </div>
            </div>

            <div class="info">
                <h1 :contenteditable="isAuth" class="text-sm">Расписание учебных занятий на {{ semester?.index }}
                    cеместр {{
                        semester?.years }} учебного года
                </h1>
                <h2 :contenteditable="isAuth" class="text-xs"> {{ course }} курс Учебный корпус №{{
                    buildingsArray?.toString() }}
                </h2>
            </div>
        </div>

        <div>


            <div v-for="weekDay in daysOfWeek" :key="weekDay">
                <table :width="mainSchedules?.length * 150 + 'px'">
                    <tr v-if="weekDay === 'ПН'">

                        <template v-for="group_schedule in mainSchedules" :key="group_schedule?.group.name">
                            <th width="10px" class="bg-yellow-300">

                            </th>
                            <th width="" colspan="3" class=" bg-red-100">
                                <div v-if="weekDay === 'ПН'" class="group-name">{{
                                    group_schedule?.group?.name }}
                                </div>
                            </th>

                        </template>
                    </tr>
                </table>
                <table :width="mainSchedules?.length * 150 + 'px'"
                    :class="{ 'border-t-8 border-blue-400': weekDay !== 'ПН' }" class="border-collapse">
                    <thead>

                        <tr>
                            <template v-for="group_schedule in mainSchedules" :key="group_schedule?.group.name">
                                <th width="10px" class="bg-yellow-300">
                                    <div v-if="weekDay === 'ПН'"
                                        style="display: flex; justify-content: center; align-items: center; padding: 5px 0px;">
                                        <div
                                            style="writing-mode: vertical-lr; transform: rotate(180deg); font-size: 5px;">
                                            недели
                                            <br>
                                            день

                                        </div>
                                    </div>
                                </th>
                                <th width="12px">
                                    <div v-if="weekDay === 'ПН'"
                                        style="display: flex; justify-content: center; align-items: center;  padding: 5px 0px;">
                                        <div
                                            style="writing-mode: vertical-lr; transform: rotate(180deg); font-size: 5px;">
                                            пары
                                            <br>
                                            номер
                                        </div>
                                    </div>
                                </th>
                                <th width="100px">
                                    <div v-if="weekDay === 'ПН'" style="font-size: 6px; line-height: 150%;">
                                        Предмет, преподаватель <br>
                                        числитель /знаменатель
                                    </div>
                                </th>
                                <th>
                                    <div v-if="weekDay === 'ПН'"
                                        style="display: flex; justify-content: center; align-items: center;  padding: 5px 0px;">
                                        <div
                                            style="writing-mode: vertical-lr; transform: rotate(180deg); font-size: 5px;">
                                            кабинет
                                        </div>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody v-if="mainSchedules">
                        <tr v-for="index in getIndexesFromWeekdays?.[weekDay]" :key="index">
                            <template v-for="group_schedule in mainSchedules" :key="group_schedule?.group.name">
                                <td class="bg-yellow-300 font-bold"
                                    v-if="getIndexesFromWeekdays?.[weekDay][0] === index"
                                    :rowspan="getIndexesFromWeekdays?.[weekDay]?.length">
                                    <div
                                        style="display: flex; justify-content: center; align-items: center;  padding: 5px 0px;">
                                        <div
                                            style="writing-mode: vertical-lr; transform: rotate(180deg); font-size: 5px;">
                                            {{ dayNamesWithPreposition[weekDay] }}
                                        </div>
                                    </div>

                                </td>
                                <td class="index text-center" style="font-size: 5px;">{{ index }}</td>
                                <td>

                                    <template v-for="lesson in group_schedule?.schedule?.[weekDay]"
                                        :key="lesson?.index">
                                        <div class="">

                                        </div>
                                        <template v-if="lesson?.lesson?.index === index">
                                            <div class="subject-name ">
                                                {{ lesson?.lesson?.subject?.name }}
                                            </div>

                                            <div class="teacher">
                                                <span v-for="teacher in lesson?.lesson?.teachers">
                                                    {{ teacher.name }}
                                                </span>
                                            </div>
                                        </template>
                                        <template
                                            v-if="lesson?.['ЗНАМ']?.index === index || lesson?.['ЧИСЛ']?.index === index">
                                            <div class="">
                                                <div class="subject-name">
                                                    {{ lesson?.['ЧИСЛ']?.subject?.name }} /
                                                </div>

                                            </div>

                                            <div class="">
                                                <div class="subject-name">
                                                    {{ lesson?.['ЗНАМ']?.subject?.name }}
                                                </div>
                                            </div>
                                        </template>
                                    </template>
                                </td>
                                <td>

                                    <template v-for="lesson in group_schedule?.schedule?.[weekDay]"
                                        :key="lesson?.index">
                                        <template v-if="lesson?.lesson?.index === index">
                                            <div class="cabinet">
                                                {{ lesson?.lesson?.cabinet }}
                                            </div>
                                        </template>
                                        <template
                                            v-if="lesson?.['ЗНАМ']?.index === index || lesson?.['ЧИСЛ']?.index === index">

                                            <div class="">
                                                <div class="cabinet">
                                                    {{ lesson?.['ЧИСЛ']?.cabinet }} /
                                                </div>
                                            </div>

                                            <div class="">
                                                <div class="cabinet">
                                                    {{ lesson?.['ЗНАМ']?.cabinet }}
                                                </div>
                                            </div>
                                        </template>
                                    </template>
                                </td>
                            </template>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>


<style scoped>
@media print {


    .controls {
        display: none;
    }

    .main {
        overflow: visible !important;
        /* Убираем возможные разрывы страниц и переносы */
        /* page-break-inside: avoid; */
    }

}

* {

    /* font-size: 1.2rem; */
}

.group-header {
    width: 150px;
}

.bg-line {
    height: 1rem;
    background: rgba(45, 116, 209, 0.582);
}

.main {
    font-family: 'Arial', Times, serif;
    padding: 1rem;
    overflow: auto;
}


table {
    table-layout: fixed;
    border-collapse: collapse;
    /* width: 100%; */
}


tbody th {
    line-height: normal;
}

td {
    height: 10px;
}

th,
td {
    border: 1px solid black;
    padding-right: 4px;
    padding-left: 4px;
    padding-top: 0;
    padding-bottom: 0;
    line-height: normal;

    /* padding: 5px; */
}

.info * {
    line-height: normal;
    /* font-size: 2rem; */
    text-align: center;
    font-weight: bold;
}

.info {
    margin-bottom: 1rem;
}

.group-name {
    font-size: 10px;
}

.subject-name {
    text-align: center;
    text-transform: uppercase;
    font-size: 6px;

    padding-top: 5px;
}

.teacher {
    text-align: center;

    font-size: 6px;
    padding-bottom: 5px;
}

.cabinet {
    text-align: center;
    font-size: 6px;
    font-weight: bold;
}
</style>