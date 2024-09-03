<script setup lang="ts">
import SelectButton from 'primevue/selectbutton';
import Select from 'primevue/select';
import { computed, ref } from 'vue';
import DatePicker from 'primevue/datepicker';

import Checkbox from 'primevue/checkbox';
import { useBellsQuery } from '@/queries/bells';


const type = ref('Основное');
const typeOptions = ref(['Основное', 'Изменения']);

const variant = ref('Обычный');
const variantOptions = ref(['Обычный', 'Сокращенные']);

const typeState = computed(() => {
    return type.value === typeOptions.value[0];
})


const weekDay = ref('ПН')
const weekDaysOptions = ref([
    {
        label: 'Понедельник',
        value: 'ПН',
    },
    {
        label: 'Вторник',
        value: 'ВТ',

    },
    {
        label: 'Среда',
        value: 'СР',

    },
    {
        label: 'Четверг',
        value: 'ЧТ',

    },
    {
        label: 'Пятница',
        value: 'ПТ',

    },
    {
        label: 'Суббота',
        value: 'СБ',

    },
    {
        label: 'Воскресенье',
        value: 'ВС',

    }
])

const date = ref()

const bellsPeriods = ref([
    {
        index: 0,
        period_from: '',
        period_to: '',
        has_break: false,
        period_from_after: '',
        period_to_after: ''
    },
    {
        index: 1,
        period_from: '',
        period_to: '',
        has_break: false,
        period_from_after: '',
        period_to_after: ''
    }, {
        index: 2,
        period_from: '',
        period_to: '',
        has_break: false,
        period_from_after: '',
        period_to_after: ''
    }, {
        index: 3,
        period_from: '',
        period_to: '',
        has_break: false,
        period_from_after: '',
        period_to_after: ''
    }, {
        index: 4,
        period_from: '',
        period_to: '',
        has_break: false,
        period_from_after: '',
        period_to_after: ''
    }, {
        index: 5,
        period_from: '',
        period_to: '',
        has_break: false,
        period_from_after: '',
        period_to_after: ''
    }, {
        index: 6,
        period_from: '',
        period_to: '',
        has_break: false,
        period_from_after: '',
        period_to_after: ''
    }, {
        index: 7,
        period_from: '',
        period_to: '',
        has_break: false,
        period_from_after: '',
        period_to_after: ''
    },
])


const { data } = useBellsQuery(type, variant, weekDay, date)


</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Звонки</h1>
            {{
                data }}
        </div>
        <div class="">
            <form class="flex flex-wrap items-center gap-4 p-4 rounded-lg dark:bg-surface-800">
                <SelectButton :allowEmpty="false" v-model="type" :options="typeOptions" aria-labelledby="basic" />
                <SelectButton :allowEmpty="false" v-model="variant" :options="variantOptions" aria-labelledby="basic" />

                <Select option-value="value" v-if="typeState" v-model="weekDay" :options="weekDaysOptions"
                    optionLabel="label" placeholder="День недели" class="w-full md:w-56" />

                <DatePicker v-else dateFormat="dd.mm.yy" v-model="date" />
            </form>
        </div>
        <div class="">
            <div class="rounded-md border border-surface-200 p-4 dark:border-surface-800 dark:bg-surface-950">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
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
                                <th
                                    class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                    С перерывом</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="period in bellsPeriods" class="group">
                                <td
                                    class="border-b border-surface-200 px-6 py-4 align- text-sm text-surface-700 group-last:border-none dark:border-surface-800 dark:text-surface-400">
                                    {{ period.index }}</td>
                                <td
                                    class="border-b border-surface-200 px-6 py-4 align-top group-last:border-none dark:border-surface-800">
                                    <div class="mb-2">
                                        <DatePicker v-model="period.period_from" id="datepicker-timeonly" timeOnly
                                            fluid />
                                    </div>
                                    <div v-if="period.has_break">
                                        <DatePicker v-model="period.period_from_after" id="datepicker-timeonly" timeOnly
                                            fluid />
                                    </div>
                                </td>
                                <td
                                    class="border-b border-surface-200 px-6 py-4 align-top group-last:border-none dark:border-surface-800">
                                    <div class="mb-2">
                                        <DatePicker v-model="period.period_to" id="datepicker-timeonly" timeOnly
                                            fluid />
                                    </div>
                                    <div v-if="period.has_break">
                                        <DatePicker v-model="period.period_to_after" id="datepicker-timeonly" timeOnly
                                            fluid />
                                    </div>
                                </td>
                                <td
                                    class="border-b border-surface-200 px-6 py-4 align-top group-last:border-none dark:border-surface-800">
                                    <Checkbox v-model="period.has_break" :binary="true" />
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <div class="flex items-center gap-4 border-t border-surface-200 px-2 pt-4 dark:border-surface-800">

                </div>
            </div>
        </div>
    </div>
</template>