<script setup lang="ts">
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import { useDateFormat } from '@vueuse/core'
import { useToast } from 'primevue/usetoast';

import { useDestroyGroup, useGroupsQuery, useStoreGroup, useUpdateGroup } from '../queries/groups'

const { data: groups } = useGroupsQuery()

const toast = useToast();

const newGroupName = ref('')
const newGroupError = ref(false)

const editingRows = ref([]);
const selectedGroups = ref([]);
const courses = [
    {
        label: 1,
        value: 1
    },
    {
        label: 2,
        value: 2
    },
    {
        label: 3,
        value: 3
    },
    {
        label: 4,
        value: 4
    },
]

const { mutateAsync: updateGroup, isPending: isUpdated } = useUpdateGroup()

const onRowEditSave = async (event) => {
    let { newData, index } = event;

    try {
        await updateGroup({
            id: newData.id, body: newData
        })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
};



const { mutateAsync: storeSubject, isPending: isStored } = useStoreGroup()

const addGroup = async () => {
    const regexGroup = /^[a-zA-Zа-яА-Я]{2,4}-[1-4]\d{2}$/;

    if (!regexGroup.test(newGroupName.value)) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: "Неверный формат названия Группы. Пример: ИС-401", life: 3000, closable: true });
        newGroupError.value = true
        return
    }

    try {
        await storeSubject(
            {
                specialization: newGroupName.value.split('-')[0],
                course: newGroupName.value.split('-')[1][0],
                index: newGroupName.value.split('-')[1].slice(1)
            }
        )
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        newGroupName.value = ''
    }

    newGroupError.value = false
    newGroupName.value = ''
}
const { mutateAsync: destroySubject, isPending: isDestroyed } = useDestroyGroup()

const deleteGroups = async () => {
    if (selectedGroups.value.length === 0) return;

    for (let i = 0; i < selectedGroups.value.length; i++) {
        try {
            await destroySubject(selectedGroups.value[i].id)
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }
    selectedGroups.value = []
}

</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Группы</h1>
        </div>
        <div class="">
            <form class="flex items-center gap-4 p-4 rounded-lg dark:bg-surface-800">
                <InputText :invalid="newGroupError" placeholder="Пример: ИС-401" v-model="newGroupName"></InputText>
                <Button type="submit" @click.prevent="addGroup" :disabled="!newGroupName">Добавить группу</Button>
            </form>
        </div>
        <div class="">
            <DataTable :loading="isUpdated || isDestroyed || isStored" v-model:selection="selectedGroups"
                v-model:editingRows="editingRows" :value="groups" editMode="row" dataKey="id"
                @row-edit-save="onRowEditSave" :pt="{
                    table: { style: 'min-width: 50rem' }
                }">
                <template #header>
                    <div class="flex justify-between">
                        <Button severity="danger" :disabled="!selectedGroups.length || !groups.length" type="button"
                            icon="pi pi-trash" label="Удалить" outlined @click="deleteGroups" />

                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                <Column field="name" header="Название группы" style="width: 20%">
                </Column>
                <Column field="specialization" header="Специальность" style="width: 20%">
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" />
                    </template>
                </Column>
                <Column field="course" header="Курс" style="width: 10%">
                    <template #editor="{ data, field }">
                        <Dropdown v-model="data[field]" :options="courses" optionLabel="label" optionValue="value">
                        </Dropdown>
                    </template>
                </Column>

                <Column field="updated_at" header="Дата изменения" style="width: 20%">
                    <template #body="slotProps">
                        {{ useDateFormat(slotProps.data.updated_at, 'DD.MM.YY HH:mm:ss') }}
                    </template>
                </Column>
                <Column :rowEditor="true" style="width: 10%; min-width: 8rem" bodyStyle="text-align:center"></Column>
            </DataTable>
        </div>
    </div>
</template>