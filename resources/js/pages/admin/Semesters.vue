<script setup lang="ts">
import { reactive, ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import { useDateFormat } from '@vueuse/core'
import { useToast } from 'primevue/usetoast';
import DatePicker from 'primevue/datepicker';
import { useStoreSemester, useSemestersQuery, useUpdateSemester, useDestroySemester, } from '@/queries/semesters';

const { data: semesters } = useSemestersQuery()

const toast = useToast();

const newSemesterError = ref(false)

const editingRows = ref([]);
const selectedSemesters = ref([]);

const dates = ref();
const years = ref();

const indexSemester = ref(1)
// const yearsSemester = ref(new Date(years.value[0]).getFullYear() + new Date(years.value[1]).getFullYear())
// const startSemester = ref(dates.value[0])
// const endSemester = ref(dates.value[1])

const { mutateAsync: storeSemester, isPending: isStored } = useStoreSemester()
const addSemester = async () => {
    try {
        await storeSemester({

            years: `${new Date(years.value[0]).getFullYear()}/${new Date(years.value[1]).getFullYear()}`,
            index: indexSemester.value,
            start: dates.value[0],
            end: dates.value[1]

        })
    }
    catch (e) {
        newSemesterError.value = true
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        // inputSemester = { years: '', index: 1 }
        return
    }
    newSemesterError.value = false
    // inputSemester = { index: 1, years: '' }
}

const { mutateAsync, isPending: isUpdated } = useUpdateSemester()
const onRowEditSave = async (event) => {
    let { newData, index } = event;
    try {
        await mutateAsync({ id: newData.id, body: newData })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
};

const { mutateAsync: destroySemester, isPending: isDestroyed } = useDestroySemester()
const deleteSemesters = async () => {
    if (!selectedSemesters.value.length) return;

    for (let i = 0; i < selectedSemesters.value.length; i++) {
        try {
            await destroySemester(selectedSemesters.value[i].id)
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }
    selectedSemesters.value = []
}


const minDate = ref(new Date());



</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Семестры</h1>
        </div>
        <div class="">
            <form class="flex flex-wrap items-end gap-4 p-4 rounded-lg dark:bg-surface-800">
                <div class="">
                    <label for="years" class=" block mb-1">Учебный год</label>
                    <!-- <InputText id="years" v-model="inputSemester.years" placeholder="2023/2024"></InputText> -->
                    <DatePicker append-to="self" placeholder="Учебный год" :min-date="minDate" view="year"
                        input-id="dates" date-format="yy" v-model="years" selectionMode="range" :manualInput="false" />
                </div>
                <div class="">
                    <label for=" semester" class=" block mb-1">Номер семестра</label>
                    <InputNumber placeholder="Номер семестра" v-model="indexSemester" inputId="semester" mode="decimal"
                        :min="1" :max="100" fluid>
                    </InputNumber>
                </div>
                <div class="">
                    <label for="dates" class="block mb-1">Начало - Конец семестра</label>
                    <DatePicker append-to="self" placeholder="Начало - Конец семестра" input-id="dates"
                        date-format="dd.mm.yy" v-model="dates" selectionMode="range" :manualInput="false" />
                </div>

                <Button :disabled="!years || !indexSemester || dates" @click.prevent="addSemester">Добавить</Button>
            </form>
        </div>
        <div class="">
            <DataTable :loading="isUpdated || isDestroyed || isStored" v-model:selection="selectedSemesters"
                v-model:editingRows="editingRows" :value="semesters" editMode="row" dataKey="id"
                @row-edit-save="onRowEditSave" :pt="{
                    table: { style: 'min-width: 50rem' }
                }">
                <template #header>
                    <div class="flex justify-between">
                        <Button severity="danger" :disabled="!selectedSemesters.length || !semesters.length"
                            type="button" icon="pi pi-trash" label="Удалить" outlined @click="deleteSemesters" />
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                <Column field="years" header="Учебный год">
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" />
                    </template>
                </Column>
                <Column field="index" header="Семестр">
                    <template #editor="{ data, field }">
                        <InputNumber v-model="data[field]" inputId="minmax-buttons" mode="decimal" showButtons :min="0"
                            :max="100" fluid />
                    </template>
                </Column>
                <Column field="start" header="Начало семестра">
                    <template #body="slotProps">
                        {{ useDateFormat(slotProps.data.start, 'DD.MM.YY') }}
                    </template>
                </Column>
                <Column field="end" header="Конец семестра">
                    <template #body="slotProps">
                        {{ useDateFormat(slotProps.data.end, 'DD.MM.YY') }}
                    </template>
                </Column>
                <Column :rowEditor="true" style="width: 10%; min-width: 8rem" bodyStyle="text-align:center"></Column>
            </DataTable>
        </div>
    </div>




</template>