<script setup lang="ts">
import DatePicker from 'primevue/datepicker';
import { computed, ref, watch, onUpdated, nextTick, watchEffect } from 'vue';
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
import { usePublicBellsPrintQuery, usePublicBellsQuery } from '@/queries/bells';
import { useDateFormat } from '@vueuse/core';

const route = useRoute();

const date = ref(null)
const formattedDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});


const { data: buildingsData, isFetched: buildingsFetched } = useBuildingsQuery()
const selectedBuildings = ref(null)
const buildings = computed(() => {
    return buildingsData.value?.map(building => ({
        value: building.name,
        label: `${building.name} корпус`,
    })) || [];
})


const buildingsArray = computed(() => {
    return [selectedBuildings.value?.map(obj => obj.value)]
})

const authStore = useAuthStore()
const { user, isAuth } = storeToRefs(authStore)

function printPage() {
    window.print();
}

function updateQueryParams() {
    router.replace({
        query: {
            ...route.query,
            date: formattedDate.value || undefined,
            buildings: buildingsArray.value || undefined,
        },
    });
};

watch([date, selectedBuildings], () => {
    updateQueryParams();

}, { deep: true });



watchEffect(() => {

    if (buildingsFetched.value) {

        const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2])\.(\d{4})$/;

        if (route.query.date && dateRegex.test(route.query.date as string)) {
            const [day, month, year] = (route.query.date as string).split('.').map(Number);
            date.value = new Date(year, month - 1, day);
        }
        if (route.query.buildings) {
            const buildingNames = route.query.buildings.toString(); // если строка, разбиваем на массив

            selectedBuildings.value = buildings.value?.filter(building => buildingNames.includes(building.value));
        }

    }
});


const { data: publicBells, isFetched: isFetchedBells } = usePublicBellsPrintQuery(buildingsArray, formattedDate)


const mergedBells = computed(() => {
    // Функция для сравнения periods между разными корпусами
    const periodsEqual = (periods1, periods2) => {
        if (periods1.length !== periods2.length) return false;
        return periods1.every((p1, index) => {
            const p2 = periods2[index];
            return (
                p1.index === p2.index &&
                p1.has_break === p2.has_break &&
                p1.period_from === p2.period_from &&
                p1.period_to === p2.period_to &&
                p1.period_from_after === p2.period_from_after &&
                p1.period_to_after === p2.period_to_after
            );
        });
    };

    // Группируем звонки по одинаковым периодам
    const grouped = [];

    publicBells.value?.forEach(bell => {
        // Находим группу, у которой совпадают periods
        let group = grouped.find(g => periodsEqual(g.bells.periods, bell.periods));

        if (group) {
            // Если такая группа найдена, добавляем туда здание
            group.building += `, ${bell.building}`;
        } else {
            // Если группа не найдена, создаем новую
            grouped.push({
                building: String(bell.building),
                bells: bell
            });
        }
    });

    return grouped;
})

const getIndexesFromBells = computed(() => {
    const indexes = new Set<number>();
    mergedBells.value?.forEach(bell => {
        bell.bells.periods.forEach(period => {
            indexes.add(period.index);
        });
    });
    return Array.from(indexes).sort((a, b) => a - b);

})

const dayNamesWithPreposition = {
    понедельник: 'понедельник',
    вторник: 'вторник',
    среда: 'среду',
    четверг: 'четверг',
    пятница: 'пятницу',
    суббота: 'субботу',
    воскресенье: 'воскресенье'
};

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
    <LoadingBar />
    <div class="controls py-2 flex  flex-wrap gap-2 items-center  pl-2">
        <DatePicker fluid showIcon iconDisplay="input" dateFormat="dd.mm.yy" v-model="date">
        </DatePicker>
        <MultiSelect :max-selected-labels="2" :selectedItemsLabel="'{0} выбрано'" v-model="selectedBuildings"
            :options="buildings" placeholder="Корпуса" option-label="label" />
        <Button label="Печать" @click="printPage()" :disabled="!date || !selectedBuildings" icon="pi pi-print" />
    </div>
    <div class="main">
        <div class="flex flex-col gap-2 items-center w-full ">

            <h1 v-if="publicBells" class=" font-bold text-center py-2">
                <span class="uppercase">Расписание звонков</span> <br>
                на
                {{ dayNamesWithPreposition[useDateFormat(date, 'dddd', {
                    locales: 'ru-RU'
                }).value] }} {{ `${useDateFormat(date, 'DD', {
                    locales: 'ru-RU'
                }).value} ${monthDeclensions[useDateFormat(date, 'MMMM', {
                    locales: 'ru-RU'
                }).value]} ${useDateFormat(date, 'YYYY', {
                    locales: 'ru-RU'
                }).value}`

                }} года
            </h1>
            <span v-if="publicBells?.type" :class="{
                'text-green-400 ': publicBells?.type
                    !== 'main',
                'text-surface-400 ': publicBells?.type
                    === 'main'
            }" class="text-sm text-right  py-1 px-2 rounded-lg ">{{
                publicBells?.type
                    === 'main' ? 'Основное' : 'Изменения' }}</span>
            <div class="">
                <h2 class="text-2xl text-center " v-if="!publicBells && isFetchedBells">На эту дату расписание
                    звонков не
                    найдено
                </h2>
                <div v-if="publicBells" class="">
                    <table class="bells-table rounded">
                        <thead>
                            <tr>
                                <th>
                                    <div class="flex gap-2 flex-col text-lg p-2">
                                        <span class="self-end">Корпус</span>
                                        <span class="border rotate-12"></span>
                                        <span class="self-start">№ пары</span>
                                    </div>
                                </th>
                                <th v-for="bell in mergedBells" :key="bell?.building">
                                    <div class="flex flex-col gap-1 items-center">
                                        <span>

                                            {{ bell?.building }}
                                        </span>
                                        <span :class="{
                                            'text-green-400 ': bell.bells?.type
                                                !== 'main',
                                            'text-surface-400 ': bell.bells?.type
                                                === 'main'
                                        }" class="text-sm text-right rounded-lg ">{{
                                            bell.bells?.type
                                                === 'main' ? 'Основное' : 'Изменения' }}</span>
                                    </div>

                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="" v-for="index in getIndexesFromBells" :key="index">
                                <td class="text-center py-4 font-bold">{{ index }} пара</td>
                                <template v-for="bell in mergedBells" :key="bell?.building">
                                    <template v-for="period in bell.bells.periods" :key="period.index">
                                        <td v-if="period?.index === index">
                                            <div>
                                                {{ period.period_from }} - {{ period.period_to }}
                                            </div>
                                            <div v-if="period?.period_from_after">
                                                {{ period.period_from_after }} - {{ period.period_to_after }}
                                            </div>
                                        </td>

                                    </template>
                                    <td v-if="!bell.bells.periods.find((period) => period.index === index)"> </td>
                                </template>


                            </tr>
                        </tbody>
                    </table>
                </div>

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

.bells-table {

    border-collapse: collapse;
    /* width: 100%; */


}

.bells-table td {
    padding: 0.75rem 1rem;

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
    font-size: 1.5rem;
    padding: 1.2rem;
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
</style>