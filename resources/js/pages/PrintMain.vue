<script setup lang="ts">
import { usePrintMainSchedulesQuery } from '@/queries/schedules';
import { useDateFormat, useStorage } from '@vueuse/core';
import { computed, onMounted, ref, watch, onUpdated, nextTick } from 'vue';
import { useRoute, } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import { useSemesterShowQuery } from '@/queries/semesters';

const route = useRoute();
const semesterId = ref()
const course = ref()
const buildings = ref()
onMounted(() => {
    semesterId.value = route.query.semester;
    course.value = route.query.course;
    buildings.value = route.query.buildings;
})

// const selectedCourse = ref()
const { data: mainSchedules, isFetched, error, isError, isLoading, isSuccess, isFetchedAfterMount } = usePrintMainSchedulesQuery(semesterId, course, buildings);


const dayNamesWithPreposition = {
    ПН: 'понедельник',
    ВТ: 'вторник',
    СР: 'среда',
    ЧТ: 'четверг',
    ПТ: 'пятница',
    СБ: 'суббота',
    ВС: 'воскресенье'
};



// const authStore = useAuthStore()
// const { user, isAuth } = storeToRefs(authStore)


watch([isFetchedAfterMount, isSuccess], async () => {
    if (isFetchedAfterMount.value && isSuccess.value) {
        // Ждём, пока контент будет полностью отрендерен
        await nextTick();

        // Ждём завершения загрузки ресурсов и запускаем печать

        window.print();

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

    // Преобразуем Set в массивы
    const result = {};
    for (const day in daysIndexes) {
        result[day] = Array.from(daysIndexes[day]).sort((a: number, b: number) => a - b);
    }

    return result;
})

const { data: semester } = useSemesterShowQuery(semesterId)

</script>

<template>
    <div class="main">
        <div v-if="mainSchedules" class="top">
            <div class="flex justify-end">
                <div contenteditable class="text-right ">
                    УТВЕРЖДАЮ <br>
                    директор <br>
                    _________ Клочков А.Ю.
                </div>
            </div>

            <div class="info">
                <h1 contenteditable class="text-sm">Расписание учебных занятий на {{ semester?.index }} cеместр {{
                    semester?.years }} учебного года
                </h1>
                <h2 contenteditable class="text-xs"> {{ course }} курс Учебный корпус №{{ buildings?.toString() }}</h2>
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
                                        <template v-if="lesson?.lesson?.index === index">
                                            <div class="subject-name">
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

                                                <!-- <div class="teacher">
                                                    <span v-for="teacher in lesson?.['ЧИСЛ']?.teachers">
                                                        {{ teacher.name }}
                                                    </span>
                                                </div> -->

                                            </div>

                                            <div class="">
                                                <div class="subject-name">
                                                    {{ lesson?.['ЗНАМ']?.subject?.name }}
                                                </div>

                                                <!-- <div class="teacher">
                                                    <span v-for="teacher in lesson?.['ЗНАМ']?.teachers">
                                                        {{ teacher.name }}
                                                    </span>
                                                </div> -->
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
    .groups-row {
        page-break-inside: avoid;
        /* Не разрывать группу внутри */
        margin-bottom: 10px;
    }

    .groups-row.page-break {
        page-break-after: always;
        /* Разрывать страницу после каждого второго блока */
    }

    .main {
        page-break-after: always;
    }
}

* {
    font-family: 'Arial', Times, serif;
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
    padding: 1rem;
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
}

.teacher {
    text-align: center;

    font-size: 6px;
}

.cabinet {
    text-align: center;
    font-size: 6px;
    font-weight: bold;
}
</style>