<script setup lang="ts">
import SelectButton from 'primevue/selectbutton';
import Select from 'primevue/select';
import { computed, onMounted, reactive, ref, watch, watchEffect } from 'vue';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';   // optional
import Row from 'primevue/row';
import { useApplyPreset, useBellsQuery, useDestroyBells, usePresetsBells, useStoreBell, useStorePeriod, useStorePresetBell, useUpdateBell } from '@/queries/bells';
import Button from 'primevue/button';
import { useCoursesQuery } from '@/queries/schedules';
import { useDateFormat } from '@vueuse/core';
import { useToast } from 'primevue/usetoast';
import RowPeriodBell from '../../components/bells/AdminRowPeriodBell.vue';
import { useBellsStore } from '@/stores/bells';
import { storeToRefs } from 'pinia';
import { useBuildingsQuery } from '@/queries/buildings';
import ToggleButton from 'primevue/togglebutton';
import Dialog from 'primevue/dialog';
import router from '@/router';
import { useRoute } from 'vue-router';

const toast = useToast();


const type = ref('Основное');
const typeOptions = ref(['Основное', 'Изменения']);


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


const { data: buildingsFethed, isFetched: buildingsFetched } = useBuildingsQuery()
const building = ref(null)
building.value = buildingsFethed.value?.[0].name

const buildings = computed(() => {
    return buildingsFethed.value?.map(building => ({
        value: building.name,
        label: `${building.name} корпус`,
    })) || [];
})


const formattedDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});

const { data, isSuccess } = useBellsQuery(type, building, weekDay, formattedDate)
const { data: bellsPresets } = usePresetsBells()

const bellsStore = useBellsStore()
const { bells } = storeToRefs(bellsStore)
const { setBells } = bellsStore

onMounted(() => {
    // building.value = buildingsFethed.value?.[0].name
})

watch(buildingsFethed, () => {
    building.value = buildingsFethed.value?.[0].name
})
watch(data, (newData) => {

    if (newData) {
        setBells(newData);
    }
});

let newPeriod = ref({
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
const { mutateAsync: storeBellPreset } = useStorePresetBell()

const isoDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});

const typeValues = {
    Основное: 'main',
    Изменения: 'changes',
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
                    building: building.value,
                    date: typeValues[type.value] === 'changes' ? isoDate.value : null,
                    week_day: typeValues[type.value] === 'main' ? weekDay.value : null,
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
        newPeriod = ref({
            index: Number(newPeriod.value.index) + 1,
            period_from: '',
            period_to: '',
            has_break: false,
            period_from_after: null,
            period_to_after: null
        })

    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message, life: 3000, closable: true });
        return
    }
}

const selectedBells = ref()
const { mutateAsync: destroyBells, isPending: isDestroyed } = useDestroyBells()
const deleteBells = async () => {
    if (!selectedBells.value.length) return;

    for (let i = 0; i < selectedBells.value.length; i++) {
        try {
            await destroyBells(selectedBells.value[i].id)
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }
    selectedBells.value = []
}
const { mutateAsync: updateBell } = useUpdateBell()

const showAddNewBellPeriod = ref(false)

const visible = ref(false);
const presetName = ref('')
function savePreset() {
    visible.value = false
    try {
        storeBellPreset({ bells_id: bells.value.id, name: presetName.value })
        presetName.value = ''
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message, life: 3000, closable: true });
        return
    }
}
const { mutateAsync: applyPreset, isSuccess: isSuccessApply } = useApplyPreset()
const selectedPreset = ref(null)
watch(selectedPreset, async () => {
    if (!selectedPreset.value) return
    if (!data.value?.id) {
        try {
            await storeBell({
                type: typeValues[type.value],
                building: building.value,
                date: typeValues[type.value] === 'changes' ? isoDate.value : null,
                week_day: typeValues[type.value] === 'main' ? weekDay.value : null,
            })

        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message, life: 3000, closable: true });
            return
        }
    }


    try {
        await applyPreset({
            bells_id: !data.value?.id ? newBell.value.data.id : bells.value.id,
            preset_id: selectedPreset.value.id,
        })

        selectedPreset.value = null

    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message, life: 3000, closable: true });
        return
    }
})



const published = ref(null)
watch(() => bells.value?.published, () => {
    published.value = bells.value.published
})

const onRowEditSave = async (event) => {
    let { newData, index } = event;
    try {
        await updateBell({ id: newData.id, body: newData })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
}

const editingRows = ref()

const route = useRoute();

function updateQueryParams() {
    router.replace({
        query: {
            ...route.query,
            type: type.value || undefined,
            building: building.value || undefined,
            weekDay: weekDay.value || undefined,
            date: isoDate.value || undefined,
        },
    });
};

watch([type, building, weekDay, date], () => {
    updateQueryParams();

}, { deep: true });



watchEffect(() => {
    if (buildingsFetched.value) {
        const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2])\.(\d{4})$/;
        if (route.query.date && dateRegex.test(route.query.date as string)) {
            const [day, month, year] = (route.query.date as string).split('.').map(Number);
            date.value = new Date(year, month - 1, day);
        }
        else {
            date.value = new Date();
        }

        if (route.query.building) {
            building.value = route.query.building;
        }

        if (route.query.weekDay) {
            weekDay.value = route.query.weekDay as any;
        }

        if (route.query.type) {
            type.value = route.query.type as any;
        }
    }
});
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Звонки</h1>

        </div>
        <div class="">
            <form class="flex flex-wrap items-center gap-2 p-4 rounded-lg dark:bg-surface-800">
                <div class="flex flex-wrap items-center gap-2">
                    <SelectButton :allowEmpty="false" v-model="type" :options="typeOptions" aria-labelledby="basic" />
                    <Select title="Корпус" optionValue="value" v-model="building" :options="buildings"
                        option-label="label" placeholder="Корпус"></Select>
                    <Select option-value="value" v-if="typeState" v-model="weekDay" :options="weekDaysOptions"
                        optionLabel="label" placeholder="День недели" class="w-full md:w-56" />

                    <DatePicker append-to="self" v-else dateFormat="dd.mm.yy" v-model="date" />
                    <ToggleButton :disabled="!data"
                        @change="updateBell({ id: bells?.id, body: { published: published } })" v-model="published"
                        class="text-sm" fluid onLabel="Снять с публикации" offLabel="Опубликовать" />
                    <!-- <Button @click="copyState = !copyState" text icon="pi pi-clone" title="Скопировать"></Button> -->
                    <div class="border-l border-surface-600 flex gap-2 pl-2">
                        <Button @click="visible = !visible" outlined icon="pi pi-clone" label="Сохранить заготовку"
                            title="Сохранить в заготовки"></Button>
                        <Select show-clear v-model="selectedPreset" placeholder="Применить заготовку"
                            option-label="name_preset" :options="bellsPresets"></Select>
                    </div>
                    <Button target="_blank" icon="pi pi-print" as="router-link" :to="{
                        path: '/print/bells',
                    }" />

                    <Dialog v-model:visible="visible" modal header="Создание заготовки" :style="{ width: '25rem' }">

                        <div class="flex items-center gap-4 mb-4">
                            <label for="name" class="font-semibold w-24">Название заготовки</label>
                            <InputText placeholder="Пример: сокр. пары" v-model="presetName" id="name"
                                class="flex-auto" />
                        </div>
                        <div class="flex justify-end gap-2">
                            <Button type="button" label="Отмена" severity="secondary" @click="visible = false"></Button>
                            <Button type="button" label="Сохранить" @click="savePreset"></Button>
                        </div>
                    </Dialog>
                </div>
            </form>

        </div>
        <div class="">
            <div class="rounded-md ">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th>
                                    №</th>
                                <th>
                                    Начало - Конец</th>

                                <th>
                                    С перерывом</th>
                                <th>
                                    Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <RowPeriodBell v-show="isSuccess" :key="period.id" v-for="period in bells?.periods"
                                :period="period">
                            </RowPeriodBell>

                            <tr v-show="showAddNewBellPeriod" class="border-t">
                                <td class="">
                                    <div class="max-w-12">
                                        <InputText class="text-center" fluid v-model="newPeriod.index"></InputText>
                                    </div>
                                </td>
                                <td class="">
                                    <div class="flex justify-center items-center flex-col gap-2 py-2">
                                        <div class="flex gap-2 items-center">
                                            <DatePicker v-model="newPeriod.period_from" id="datepicker-timeonly"
                                                timeOnly fluid />
                                            -
                                            <DatePicker v-model="newPeriod.period_to" id="datepicker-timeonly" timeOnly
                                                fluid />
                                        </div>
                                        <div class="flex gap-2 items-center" v-if="newPeriod.has_break">
                                            <DatePicker v-model="newPeriod.period_from_after" id="datepicker-timeonly"
                                                timeOnly fluid />
                                            -
                                            <DatePicker v-model="newPeriod.period_to_after" id="datepicker-timeonly"
                                                timeOnly fluid />
                                        </div>
                                    </div>


                                </td>

                                <td class="text-center">
                                    <Checkbox v-model="newPeriod.has_break" :binary="true" />
                                </td>
                                <td class="">
                                    <div class="px-6 flex justify-center">
                                        <Button outlined text @click="addPeriod" icon="pi pi-save"></Button>
                                    </div>
                                </td>

                            </tr>

                        </tbody>
                    </table>


                </div>

            </div>

            <div class="mt-2 flex items-center justify-center">
                <Button label="Новый звонок" title="Открыть форму для добавления звонка" size="small" outlined
                    severity="secondary" class="w-full" @click="showAddNewBellPeriod = !showAddNewBellPeriod"
                    :icon="!showAddNewBellPeriod ? 'pi pi-angle-down' : 'pi pi-angle-up'"></Button>
            </div>
        </div>
        <div class="flex flex-col gap-4">
            <h1 class="text-2xl">Заготовки звонков</h1>
            <DataTable v-model:editingRows="editingRows" :rows="10" editMode="row" @row-edit-save="onRowEditSave"
                v-model:selection="selectedBells" :value="bellsPresets" tableStyle="min-width: 50rem">
                <template #header>
                    <div class="flex flex-wrap items-center gap-2  justify-between">
                        <Button severity="danger" :disabled="!selectedBells?.length || !bellsPresets.length"
                            type="button" icon="pi pi-trash" label="Удалить" outlined @click="deleteBells" />

                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                <!-- <Column field="id" header="ID"></Column> -->
                <Column field="name_preset" header="Название">
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" />
                    </template>
                </Column>

                <Column :rowEditor="true" style="width: 10%; min-width: 8rem" bodyStyle="text-align:center"></Column>
            </DataTable>
        </div>
    </div>
</template>