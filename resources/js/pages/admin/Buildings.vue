<script setup lang="ts">
import { reactive, ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Button from 'primevue/button';
import { useDateFormat } from '@vueuse/core'
import { useToast } from 'primevue/usetoast';
import { useQueryClient } from '@tanstack/vue-query';
import { FilterMatchMode } from '@primevue/core/api';
import { useBuildingsQuery, useDestroyBuilding, useStoreBuilding, useUpdateBuilding } from '@/queries/buildings';


const toast = useToast();

const editingRows = ref([]);
const selectedBuildings = ref([])

const { data: buildings } = useBuildingsQuery();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    location: { value: null, matchMode: FilterMatchMode.STARTS_WITH },

});
const { mutateAsync: updateBuilding, isPending: isUpdated } = useUpdateBuilding()
async function onRowEditSave(event) {
    let { newData, index } = event;
    try {
        await updateBuilding({ name: buildings.value[index].name, body: newData })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
}

const { mutateAsync: storeSubject, isPending: isStored } = useStoreBuilding()
const addSubject = async () => {
    try {
        await storeSubject(newBuilding)
    }
    catch (e) {
        // newSubjectError.value = true
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        newBuilding.name = ''
        newBuilding.location = ''
        return
    }
    // newSubjectError.value = false
    newBuilding.name = ''
    newBuilding.location = ''
}

const { mutateAsync: destroyBuilding, isPending: isDestroyed } = useDestroyBuilding()
const deleteBuildings = async () => {
    if (!selectedBuildings.value.length) return;

    for (let i = 0; i < selectedBuildings.value.length; i++) {
        try {
            await destroyBuilding(selectedBuildings.value[i].name)
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }
    selectedBuildings.value = []
}

const newBuilding = reactive({
    name: '',
    location: ''
});


</script>
<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Корпуса</h1>
        </div>
        <div class="">
            <form class="flex flex-wrap items-center gap-4 p-4 rounded-lg dark:bg-surface-800">
                <InputText v-model="newBuilding.name" placeholder="Название корпуса">
                </InputText>
                <InputText v-model="newBuilding.location" placeholder="Адрес">
                </InputText>

                <Button :disabled="!newBuilding.name" type="submit" @click.prevent="addSubject">Добавить
                    корпус</Button>
            </form>
        </div>
        <div class="">
            <DataTable :value="buildings" v-model:filters="filters" paginator :rows="10"
                v-model:selection="selectedBuildings" v-model:editingRows="editingRows" editMode="row" dataKey="name"
                @row-edit-save="onRowEditSave" :pt="{
                    table: { style: 'min-width: 50rem' }
                }">
                <template #header>
                    <div class="flex justify-between flex-wrap gap-2">
                        <Button :disabled="!selectedBuildings.length || !buildings.length" severity="danger"
                            type="button" icon="pi pi-trash" label="Удалить" outlined @click="deleteBuildings" />
                        <InputText v-model="filters['global'].value" placeholder="Поиск" />
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>

                <Column field="name" header="Название корпуса">
                    <!-- <template #editor="{ data, field }">
                            <InputText class="w-full" v-model="data[field]" />
                        </template> -->
                </Column>
                <Column field="location" header="Адрес">
                    <template #editor="{ data, field }">
                        <InputText class="w-full" v-model="data[field]" />
                    </template>
                </Column>


                <Column field="updated_at" header="Дата изменения">
                    <template #body="slotProps">
                        {{ useDateFormat(slotProps.data.updated_at, 'DD.MM.YY HH:mm:ss') }}
                    </template>
                </Column>
                <Column :rowEditor="true" style="width: 10%; min-width: 8rem" bodyStyle="text-align:center"></Column>
            </DataTable>
        </div>
    </div>
</template>