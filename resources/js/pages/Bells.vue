<script setup lang="ts">
import SelectButton from 'primevue/selectbutton';
import Select from 'primevue/select';
import { computed, ref } from 'vue';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import { useBellsQuery, useStoreBell, useStorePeriod } from '@/queries/bells';
import Button from 'primevue/button';
import { useCoursesQuery } from '@/queries/schedules';
import { useDateFormat } from '@vueuse/core';
import { useToast } from 'primevue/usetoast';
import RowPeriodBell from '../components/RowPeriodBell.vue';


const toast = useToast();


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

const date = ref(new Date())


const building = ref(1)
const buildings = ref([
    {
        value: 1,
    },
    {
        value: 2,
    },
    {
        value: 3,
    },
    {
        value: 4,
    },
    {
        value: 5,
    },
    {
        value: 6,
    },
])


const formattedDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});

const { data } = useBellsQuery(type, variant, building, weekDay, formattedDate)



const newPeriod = ref({
    index: 0,
    period_from: '',
    period_to: '',
    has_break: false,
    period_from_after: null,
    period_to_after: null
})

function formatTime(dateString) {
    const date = new Date(dateString);
    // Получаем часы и минуты и добавляем ведущий ноль, если значение меньше 10
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return `${hours}:${minutes}`;
}

const { mutateAsync: storePeriod } = useStorePeriod()
const { mutateAsync: storeBell, data: newBell } = useStoreBell()

const isoDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});

const typeValues = {
    Основное: 'main',
    Изменения: 'changes',
};
const variantValues = {
    Обычный: 'normal',
    Сокращенные: 'reduced',
};

const bodyBellPeriod = computed(() => {
    let body = {
        ...newPeriod.value,
        bells_id: data?.value?.id ? data.value.id : newBell.value.data.id,
        period_to: formatTime(newPeriod.value.period_to),
        period_from: formatTime(newPeriod.value.period_from),
    }
    if (newPeriod.value.period_from_after || newPeriod.value.period_to_after) {
        body.period_from_after = formatTime(newPeriod.value.period_from_after);
        body.period_to_after = formatTime(newPeriod.value.period_to_after);
    }
    return body
})

async function addPeriod() {
    if (!data?.value?.id) {
        if (date.value) {
            try {
                await storeBell({
                    type: typeValues[type.value],
                    variant: variantValues[variant.value],
                    building: building.value,
                    date: isoDate.value,
                    week_day: weekDay.value,
                })

            }
            catch (e) {
                toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message, life: 3000, closable: true });
                return
            }
        }
    }
    try {
        await storePeriod(bodyBellPeriod.value)
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message, life: 3000, closable: true });
        return
    }
}

const showAddNewBellPeriod = ref(false)
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Звонки</h1>
        </div>
        <div class="">
            <form class="flex flex-wrap items-center gap-4 p-4 rounded-lg dark:bg-surface-800">
                <SelectButton :allowEmpty="false" v-model="type" :options="typeOptions" aria-labelledby="basic" />
                <SelectButton :allowEmpty="false" v-model="variant" :options="variantOptions" aria-labelledby="basic" />
                <Select optionValue="value" v-model="building" :options="buildings" option-label="value"
                    placeholder="Корпус"></Select>
                <Select option-value="value" v-if="typeState" v-model="weekDay" :options="weekDaysOptions"
                    optionLabel="label" placeholder="День недели" class="w-full md:w-56" />

                <DatePicker v-else dateFormat="dd.mm.yy" v-model="date" />
            </form>
        </div>
        <div class="">
            <div class="rounded-md border border-surface-200 dark:border-surface-800 dark:bg-surface-950">
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
                            <RowPeriodBell v-for="period in data?.periods" :data="period"></RowPeriodBell>
                            <tr class="group">
                                <td
                                    class="border-b border-surface-200 px-6 py-4  text-sm text-surface-700 group-last:border-none dark:border-surface-800 dark:text-surface-400">
                                    <div class="max-w-12">
                                        <InputText fluid v-model="newPeriod.index"></InputText>
                                    </div>
                                </td>
                                <td
                                    class="border-b border-surface-200 px-6 py-4 align-top group-last:border-none dark:border-surface-800">
                                    <div class="mb-2">
                                        <DatePicker v-model="newPeriod.period_from" id="datepicker-timeonly" timeOnly
                                            fluid />
                                    </div>
                                    <div v-if="newPeriod.has_break">
                                        <DatePicker v-model="newPeriod.period_from_after" id="datepicker-timeonly"
                                            timeOnly fluid />
                                    </div>
                                </td>
                                <td
                                    class="border-b border-surface-200 px-6 py-4 align-top group-last:border-none dark:border-surface-800">
                                    <div class="mb-2">
                                        <DatePicker v-model="newPeriod.period_to" id="datepicker-timeonly" timeOnly
                                            fluid />
                                    </div>
                                    <div v-if="newPeriod.has_break">
                                        <DatePicker v-model="newPeriod.period_to_after" id="datepicker-timeonly"
                                            timeOnly fluid />
                                    </div>
                                </td>
                                <td
                                    class="border-b border-surface-200 px-6 py-4 align-top group-last:border-none dark:border-surface-800">
                                    <Checkbox v-model="newPeriod.has_break" :binary="true" />
                                </td>
                            </tr>

                        </tbody>
                    </table>
                    <div class="px-6 flex justify-end">
                        <Button @click="addPeriod" label="Добавить"></Button>
                    </div>

                </div>

            </div>
            <!-- <div class="mt-2 flex items-center justify-center">
                <Button label="Новый звонок" title="Открыть форму для добавления звонка" size="small" outlined
                    severity="secondary" class="w-full" @click="showAddNewBellPeriod = !showAddNewBellPeriod"
                    :class="{ 'pi pi-angle-down': !showAddNewBellPeriod, 'pi pi-angle-up': showAddNewBellPeriod }"></Button>
            </div> -->
        </div>
    </div>
</template>