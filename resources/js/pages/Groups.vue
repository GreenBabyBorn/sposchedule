<script setup lang="ts">
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useDateFormat } from '@vueuse/core'
import { useToast } from 'primevue/usetoast';
import Chip from 'primevue/chip';
import MultiSelect from 'primevue/multiselect';
import { FilterMatchMode } from '@primevue/core/api';

import Textarea from 'primevue/textarea';



import { useDestroyGroup, useGroupsQuery, useStoreGroup, useUpdateGroup, useDestroySemesterForGroup, useStoreSemesterForGroup } from '../queries/groups'
import { useSemestersQuery } from '@/queries/semesters';
import { useConfirm } from 'primevue/useconfirm';

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
const { mutateAsync: storeSemesterForGroup } = useStoreSemesterForGroup()
const { mutateAsync: destroySemesterForGroup } = useDestroySemesterForGroup()


const onRowEditSave = async (event) => {
    let { newData, index } = event;
    let deletedSubjects = groups.value[index].semesters.filter(obj1 =>
        !newData.semesters.some(obj2 => obj2.id === obj1.id)
    );
    let newSubjects = newData.semesters.filter(obj2 =>
        !groups.value[index].semesters.some(obj1 => obj1.id === obj2.id)
    );
    if (deletedSubjects.length) {
        for (let i = 0; i < deletedSubjects.length; i++) {

            try {
                await destroySemesterForGroup({ id: groups.value[index].id, semester_id: deletedSubjects[i].id })
            }
            catch (e) {
                toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
                return
            }
        }
        // queryClient.invalidateQueries({ queryKey: ['teachers'] })
    }

    // console.log(deletedSubjects, newSubjects)
    if (newSubjects.length) {
        for (let i = 0; i < newSubjects.length; i++) {
            try {
                await storeSemesterForGroup({ id: groups.value[index].id, semester_id: newSubjects[i].id })
            }
            catch (e) {
                toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
                return
            }
        }
        // queryClient.invalidateQueries({ queryKey: ['teachers'] })
    }

    try {
        await updateGroup({
            id: newData.id, body: newData
        })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
    // let { newData, index } = event;

    // try {
    //     await updateGroup({
    //         id: newData.id, body: newData
    //     })
    // }
    // catch (e) {
    //     toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
    //     return
    // }
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
                index: newGroupName.value.split('-')[1].slice(1),
                semesters: selectedSemesters.value,
            }
        )
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        newGroupName.value = ''
    }

    newGroupError.value = false
    newGroupName.value = ''
    selectedSemesters.value = []
}


const confirm = useConfirm();


const { mutateAsync: destroySubject, isPending: isDestroyed } = useDestroyGroup()


const confirmDelete = () => {
    confirm.require({
        message: 'Удаление групп может сломать расписание',
        header: 'Вы уверены?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Отмена',
        rejectProps: {
            label: 'Отмена',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Удалить',
            severity: 'danger'
        },
        accept: async () => {
            await deleteGroups()
        },
        reject: () => {

        }
    });
};

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


const { data: semesters } = useSemestersQuery()

const selectedSemesters = ref([])


const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    course: { value: null, matchMode: FilterMatchMode.STARTS_WITH },

});

const importGroupsState = ref()
const importingGroups = ref()

const regexGroup = /^[a-zA-Zа-яА-Я]{2,4}-[1-4]\d{2}$/;
const parseAndSendGroups = async () => {
    // Разделяем введенные группы на массив строк, убирая пустые строки и пробелы
    const groups = importingGroups.value.split('\n').map(group => group.trim()).filter(group => group);

    // Проходим по каждой группе и отправляем её на сервер
    for (const group of groups) {
        if (!regexGroup.test(group)) {
            toast.add({
                severity: 'error',
                summary: 'Ошибка',
                detail: `Неверный формат названия группы: ${group}. Пример: ИС-401`,
                life: 3000,
                closable: true
            });
            continue; // Пропускаем группу с неверным форматом
        }

        try {
            // Отправляем данные на сервер
            await storeSubject({
                specialization: group.split('-')[0],
                course: group.split('-')[1][0],
                index: group.split('-')[1].slice(1),
                semesters: selectedSemesters.value,
            });

            // Успешное добавление группы
            toast.add({
                severity: 'success',
                summary: 'Успех',
                detail: `Группа ${group} успешно добавлена`,
                life: 3000,
                closable: true
            });
        } catch (e) {
            // Обработка ошибки при отправке
            toast.add({
                severity: 'error',
                summary: 'Ошибка',
                detail: e?.response?.data?.message || `Ошибка при добавлении группы ${group}`,
                life: 3000,
                closable: true
            });
        }
    }

    // Очистка поля после завершения отправки
    importingGroups.value = '';
    selectedSemesters.value = [];
};

</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Группы</h1>

        </div>
        <div class="">
            <form class="flex flex-wrap items-center gap-4 p-4 rounded-lg dark:bg-surface-800">
                <InputText :invalid="newGroupError" placeholder="Пример: ИС-401" v-model="newGroupName"></InputText>
                <MultiSelect v-model="selectedSemesters" display="chip" :options="semesters" optionLabel="name" filter
                    placeholder="Выбрать семестры" :maxSelectedLabels="3" class="" />
                <Button type="submit" @click.prevent="addGroup" :disabled="!newGroupName">Добавить группу</Button>
                <Button icon="pi pi-file-import" outlined type="submit"
                    @click.prevent="importGroupsState = !importGroupsState" label="Импорт"></Button>
                <div v-if="importGroupsState" class="flex flex-col gap-2">
                    <Textarea placeholder="Введите в столбик название групп" v-model="importingGroups" rows="5"
                        cols="30" />
                    <Button type="submit" @click.prevent="parseAndSendGroups">Импортировать</Button>
                </div>
            </form>
        </div>
        <div class="">
            <DataTable paginator :rows="10" :globalFilterFields="['name', 'course']" v-model:filters="filters"
                :loading="isUpdated || isDestroyed || isStored" v-model:selection="selectedGroups"
                v-model:editingRows="editingRows" :value="groups" editMode="row" dataKey="id"
                @row-edit-save="onRowEditSave" :pt="{
                    table: { style: 'min-width: 50rem' }
                }">
                <template #header>
                    <div class="flex justify-between">
                        <Button severity="danger" :disabled="!selectedGroups.length || !groups.length" type="button"
                            icon="pi pi-trash" label="Удалить" outlined @click="confirmDelete" />
                        <InputText v-model="filters['global'].value" placeholder="Поиск" />
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
                        <Select v-model="data[field]" :options="courses" optionLabel="label" optionValue="value">
                        </Select>
                    </template>
                </Column>
                <Column field="semesters" header="Семестры" style="width: 10%">
                    <template #body="slotProps">
                        <div class="flex gap-2 flex-wrap">
                            <Chip v-for="semester in slotProps.data.semesters" :label="semester.name" />
                        </div>
                    </template>
                    <template #editor="{ data, field }">

                        <MultiSelect v-model="data.semesters" display="chip" :options="semesters" optionLabel="name"
                            filter placeholder="Выберите семестры" :maxSelectedLabels="3" class="w-48" />

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