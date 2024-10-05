<script setup lang="ts">
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Button from 'primevue/button';
import { useDateFormat } from '@vueuse/core'
import { useToast } from 'primevue/usetoast';
import { useTeachersQuery, useDestroyTeacher, useStoreTeacher, useUpdateTeacher, useStoreSubjectForTeacher, useDestroySubjectForTeacher } from '../../queries/teachers'
import { useSubjectsQuery, } from '../../queries/subjects'
import Chip from 'primevue/chip';
import { useQueryClient } from '@tanstack/vue-query';
import { FilterMatchMode } from '@primevue/core/api';

const { data: teachers } = useTeachersQuery()

const toast = useToast();

const newTeacherName = ref('')

const newTeacherError = ref(false)

const editingRows = ref([]);
const selectedTeachers = ref([]);

const { mutateAsync: updateTeacher, isPending: isUpdated } = useUpdateTeacher()
const { mutateAsync: storeSubjectForTeacher } = useStoreSubjectForTeacher()
const { mutateAsync: destroySubjectForTeacher } = useDestroySubjectForTeacher()
const queryClient = useQueryClient();
const onRowEditSave = async (event) => {
    let { newData, index } = event;

    let deletedSubjects = teachers.value[index].subjects.filter(obj1 =>
        !newData.subjects.some(obj2 => obj2.id === obj1.id)
    );
    let newSubjects = newData.subjects.filter(obj2 =>
        !teachers.value[index].subjects.some(obj1 => obj1.id === obj2.id)
    );
    if (deletedSubjects.length) {
        for (let i = 0; i < deletedSubjects.length; i++) {

            try {
                await destroySubjectForTeacher({ id: teachers.value[index].id, subject_id: deletedSubjects[i].id })
            }
            catch (e) {
                toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
                return
            }
        }
        queryClient.invalidateQueries({ queryKey: ['teachers'] })
    }

    // console.log(deletedSubjects, newSubjects)
    if (newSubjects.length) {
        for (let i = 0; i < newSubjects.length; i++) {
            console.log({ id: teachers.value[index].id, subject_id: newSubjects[i].id })
            try {
                await storeSubjectForTeacher({ id: teachers.value[index].id, subject_id: newSubjects[i].id })
            }
            catch (e) {
                toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
                return
            }
        }
        queryClient.invalidateQueries({ queryKey: ['teachers'] })
    }

    try {
        await updateTeacher({ id: newData.id, body: newData })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
};

const { mutateAsync: destroyTeacher, isPending: isDestroyed } = useDestroyTeacher()
const deleteTeachers = async () => {
    if (!selectedTeachers.value.length) return;

    for (let i = 0; i < selectedTeachers.value.length; i++) {
        try {
            await destroyTeacher(selectedTeachers.value[i].id)
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }
    selectedTeachers.value = []
}

const { mutateAsync: storeTeacher, isPending: isStored } = useStoreTeacher()
const addTeacher = async () => {
    try {
        await storeTeacher({
            name: newTeacherName.value,
        })
    }
    catch (e) {
        newTeacherError.value = true
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        newTeacherName.value = ''
        return
    }
    newTeacherError.value = false
    newTeacherName.value = ''
}

const { data: subjects } = useSubjectsQuery()

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },

});
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Преподаватели</h1>
        </div>
        <div class="">
            <form class="flex flex-wrap items-center gap-4 p-4 rounded-lg dark:bg-surface-800">
                <InputText :invalid="newTeacherError" placeholder="ФИО" v-model="newTeacherName" class="w-full md:w-60">
                </InputText>

                <Button type="submit" @click.prevent="addTeacher" :disabled="!newTeacherName">Добавить
                    преподавателя</Button>
            </form>
        </div>
        <div class="">
            <DataTable v-model:filters="filters" paginator :rows="10" :loading="isUpdated || isDestroyed || isStored"
                v-model:selection="selectedTeachers" v-model:editingRows="editingRows" :value="teachers" editMode="row"
                dataKey="id" @row-edit-save="onRowEditSave" :pt="{
                    table: { style: 'min-width: 50rem' }
                }">
                <template #header>
                    <div class="flex justify-between flex-wrap gap-2">
                        <Button severity="danger" :disabled="!selectedTeachers.length || !teachers.length" type="button"
                            icon="pi pi-trash" label="Удалить" outlined @click="deleteTeachers" />
                        <InputText v-model="filters['global'].value" placeholder="Поиск" />
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>

                <Column field="name" header="ФИО">
                    <template #editor="{ data, field }">
                        <InputText class="w-full" v-model="data[field]" />
                    </template>
                </Column>
                <Column field="subjects" header="Предметы">
                    <template #body="slotProps">
                        <div class="flex gap-2 flex-wrap">
                            <Chip v-for="subject in slotProps.data.subjects" :label="subject.name" />
                        </div>
                    </template>
                    <template #editor="{ data, field }">

                        <MultiSelect v-model="data.subjects" display="chip" :options="subjects" optionLabel="name"
                            filter placeholder="Выберите предметы" :maxSelectedLabels="3" class="w-48" />
                        <!-- <InputText class="w-full" v-model="data[field]" /> -->
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