<script setup lang="ts">
import { usePrintChangesSchedulesQuery } from '@/queries/schedules';
import { useDateFormat, useStorage } from '@vueuse/core';
import { computed, onMounted, ref, watch, onUpdated, nextTick } from 'vue';
import { useRoute, } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { storeToRefs } from 'pinia';

const route = useRoute();
const date = ref()

onMounted(() => {
    const [day, month, year] = (route.query.date as string).split('.').map(Number);
    date.value = new Date(year, month - 1, day)
})

const isoDate = computed(() => {
    return useDateFormat(date.value ? date.value : new Date(), 'DD.MM.YYYY').value;
});


// const selectedCourse = ref()
const { data: changesSchedules, isFetched, error, isError, isLoading, isSuccess, isFetchedAfterMount } = usePrintChangesSchedulesQuery(isoDate);

const blocks1_5 = computed(() => {
    const chunkSize = 4; // Размер подмассива
    const result = [];

    for (let i = 0; i < changesSchedules?.value?.['1-5'].schedules.length; i += chunkSize) {
        const chunk = changesSchedules?.value?.['1-5'].schedules.slice(i, i + chunkSize);

        // Добавление пустых объектов, если подмассив меньше чем 4 элемента
        while (chunk.length < chunkSize) {
            chunk.push({}); // или chunk.push(null); в зависимости от того, что должно быть в пустых местах
        }

        result.push(chunk);
    }

    return result;
}); const blocks6 = computed(() => {
    const chunkSize = 4; // Размер подмассива
    const result = [];

    for (let i = 0; i < changesSchedules?.value?.['6'].schedules.length; i += chunkSize) {
        const chunk = changesSchedules?.value?.['6'].schedules.slice(i, i + chunkSize);

        // Добавление пустых объектов, если подмассив меньше чем 4 элемента
        while (chunk.length < chunkSize) {
            chunk.push({}); // или chunk.push(null); в зависимости от того, что должно быть в пустых местах
        }

        result.push(chunk);
    }

    return result;
});


const dayNamesWithPreposition = {
    понедельник: 'понедельник',
    вторник: 'вторник',
    среда: 'среду',
    четверг: 'четверг',
    пятница: 'пятницу',
    суббота: 'субботу',
    воскресенье: 'воскресенье'
};



const authStore = useAuthStore()
const { user, isAuth } = storeToRefs(authStore)


watch([isFetchedAfterMount, isSuccess], async () => {
    if (isFetchedAfterMount.value && isSuccess.value) {
        // Ждём, пока контент будет полностью отрендерен
        await nextTick();

        // Ждём завершения загрузки ресурсов и запускаем печать

        window.print();

    }
});

const monthDeclensions = {
    'январь': 'января',
    'февраль': 'февраля',
    'март': 'марта',
    'апрель': 'апреля',
    'май': 'мая',
    'июнь': 'июня',
    'июль': 'июля',
    'август': 'августа',
    'сентябрь': 'сентября',
    'октябрь': 'октября',
    'ноябрь': 'ноября',
    'декабрь': 'декабря'
};




</script>

<template>
    <div class="main" v-if="changesSchedules?.['1-5']">
        <div class="top">
            <div class="flex justify-between">
                <div>Исполнитель: <span contenteditable class="underline">{{ user?.name
                        }}</span></div>
                <div contenteditable class="text-right">
                    СОГЛАСОВАНО <br>
                    Зам. директора по УМР <br>
                    _________ О.А. Толубаева
                </div>
            </div>

            <div class="info">
                <h1>ИЗМЕНЕНИЯ В РАСПИСАНИИ ЗАНЯТИЙ (1-5 корпус)</h1>
                <h2 class="italic uppercase">НА {{ dayNamesWithPreposition[useDateFormat(date, 'dddd', {
                    locales: 'ru-RU'
                }).value] }} {{ `${useDateFormat(date, 'DD', {
                        locales: 'ru-RU'
                    }).value} ${monthDeclensions[useDateFormat(date, 'MMMM', {
                        locales: 'ru-RU'
                    }).value]} ${useDateFormat(date, 'YYYY', {
                        locales: 'ru-RU'
                    }).value}`

                    }} года
                    ({{ changesSchedules?.week_type === 'ЗНАМ' ? 'знаменатель' : 'числитель' }})</h2>

            </div>
        </div>
        <div :class="{ 'page-break': (index + 1) % 2 === 0 }" class="groups-row" v-for="block, index in blocks1_5"
            :key="block[0]?.group.name">
            <div class=" bg-line"></div>
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th></th>
                        <th v-for="(group, groupIndex) in block" :key="groupIndex" :colspan="groupIndex == 0 ? 2 : 2"
                            class="group-header ">
                            <div class="group-name">{{ group?.group?.name }}</div>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                    <tr v-for="index in [0, 1, 2, 3, 4, 5, 6, 7]" :key="index">
                        <!-- Колонки расписания для каждой группы -->
                        <template v-for="(group, groupIndex) in block" :key="`row-${index}-group-${groupIndex}`">
                            <td v-if="groupIndex == 0 || !groupIndex" class="index text-center" width="10px">{{ index }}
                            </td>

                            <td class="subject-name ">

                                <template v-for="lesson in group?.schedule?.lessons">

                                    <template v-if="lesson?.index === index">
                                        <span class="">
                                            {{ lesson?.subject?.name }}

                                        </span>
                                        <span class="font-bold">
                                            {{ lesson?.message }}
                                        </span>
                                    </template>
                                </template>
                            </td>
                            <td class="cabinet text-center">

                                <template v-for="lesson in group?.schedule?.lessons">
                                    <template v-if="lesson?.index === index">
                                        {{ lesson?.cabinet }}
                                    </template>
                                </template>
                            </td>
                            <td v-if="groupIndex == 3" class="index text-center" width="10px">{{ index }}
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div v-if="changesSchedules?.['6']" class="main">
        <div class="top">
            <div class="flex justify-between">
                <div>Исполнитель: <span contenteditable class="underline">{{ user.name
                        }}</span></div>
                <div contenteditable class="text-right">
                    СОГЛАСОВАНО <br>
                    Зам. директора по УМР <br>
                    _________ О.А. Толубаева
                </div>
            </div>

            <div class="info">
                <h1>ИЗМЕНЕНИЯ В РАСПИСАНИИ ЗАНЯТИЙ (6 корпус)</h1>
                <h2 class="italic uppercase">НА {{ dayNamesWithPreposition[useDateFormat(date, 'dddd', {
                    locales: 'ru-RU'
                }).value] }} {{ `${useDateFormat(date, 'DD', {
                        locales: 'ru-RU'
                    }).value} ${monthDeclensions[useDateFormat(date, 'MMMM', {
                        locales: 'ru-RU'
                    }).value]} ${useDateFormat(date, 'YYYY', {
                        locales: 'ru-RU'
                    }).value}`

                    }} года
                    ({{ changesSchedules?.week_type === 'ЗНАМ' ? 'знаменатель' : 'числитель' }})</h2>

            </div>
        </div>
        <div :class="{ 'page-break': (index + 1) % 2 === 0 }" class="groups-row" v-for="block, index in blocks6"
            :key="block[0]?.group.name">
            <div class=" bg-line"></div>
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th></th>
                        <th v-for="(group, groupIndex) in block" :key="groupIndex" :colspan="groupIndex == 0 ? 2 : 2"
                            class="group-header ">
                            <div class="group-name">{{ group?.group?.name }}</div>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                    <tr v-for="index in [0, 1, 2, 3, 4, 5, 6, 7]" :key="index">
                        <!-- Колонки расписания для каждой группы -->
                        <template v-for="(group, groupIndex) in block" :key="`row-${index}-group-${groupIndex}`">
                            <td v-if="groupIndex == 0 || !groupIndex" class="index text-center" width="10px">{{ index }}
                            </td>

                            <td class="subject-name ">

                                <template v-for="lesson in group?.schedule?.lessons">

                                    <template v-if="lesson?.index === index">
                                        <span class="">
                                            {{ lesson?.subject?.name }}

                                        </span>
                                        <span class="font-bold">
                                            {{ lesson?.message }}
                                        </span>
                                    </template>
                                </template>
                            </td>
                            <td class="cabinet text-center">

                                <template v-for="lesson in group?.schedule?.lessons">
                                    <template v-if="lesson?.index === index">
                                        {{ lesson?.cabinet }}
                                    </template>
                                </template>
                            </td>
                            <td v-if="groupIndex == 3" class="index text-center" width="10px">{{ index }}
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
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
    font-family: 'Times New Roman', Times, serif;
    font-size: 1.2rem;
}

.bg-line {
    height: 2rem;

    background:
        /* Сверху */
        repeating-linear-gradient(45deg,

            #ffffff 1px,
            #959595 2px),
        /* Снизу */
        linear-gradient(to bottom,
            #ffffff,
            #959595);
}

.main {
    padding: 1rem;
}

.schedules {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
}

table {
    table-layout: auto;
    border-collapse: collapse;
    width: 100%;
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



.group-header {
    width: 25%;
    /* line-height: 100%; */
}

.group-name {
    text-align: left;
    font-weight: 700;
    font-size: 1.2rem;
}

.subject-name {
    text-align: left;
}

.schedules {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    /* row-gap: 2rem;    */
    align-items: start;
}

.cabinet {
    max-width: 15px;
    width: 4%;
    font-size: 0.9rem;
    text-align: center;
}

.info * {
    line-height: normal;
    font-size: 2rem;
    text-align: center;
    font-weight: bold;
}

.info {
    margin-bottom: 1rem;
}
</style>