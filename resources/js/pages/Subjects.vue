<script setup lang="ts">
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useDateFormat } from '@vueuse/core'
import InputNumber from 'primevue/inputnumber';
import { useToast } from 'primevue/usetoast';
import { useSubjectsQuery, useDestroySubject, useStoreSubject, useUpdateSubject } from '../queries/subjects'
import Textarea from 'primevue/textarea';
import { FilterMatchMode } from '@primevue/core/api';

const { data: subjects } = useSubjectsQuery()

const toast = useToast();

const newSubjectName = ref('')
const newSubjectError = ref(false)

const editingRows = ref([]);
const selectedSubjects = ref([]);

const { mutateAsync, isPending: isUpdated } = useUpdateSubject()
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

const { mutateAsync: destroySubject, isPending: isDestroyed } = useDestroySubject()
const deleteSubjects = async () => {
    if (!selectedSubjects.value.length) return;

    for (let i = 0; i < selectedSubjects.value.length; i++) {
        try {
            await destroySubject(selectedSubjects.value[i].id)
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }
    selectedSubjects.value = []
}

const { mutateAsync: storeSubject, isPending: isStored } = useStoreSubject()
const addSubject = async () => {
    try {
        await storeSubject(newSubjectName.value)
    }
    catch (e) {
        newSubjectError.value = true
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        newSubjectName.value = ''
        return
    }
    newSubjectError.value = false
    newSubjectName.value = ''
}

const importSubjectsState = ref()
const importingSubjects = ref()

async function parseAndSendSubjects() {
    // Разделяем введенные предметы на массив строк, убирая пустые строки и пробелы
    const subjects = importingSubjects.value.split('\n').map(subject => subject.trim()).filter(subject => subject);

    // Проходим по каждому предмету и отправляем его на сервер
    for (const subject of subjects) {
        try {
            // Отправляем данные на сервер
            await storeSubject(subject);

            // Успешное добавление предмета
            toast.add({
                severity: 'success',
                summary: 'Успех',
                detail: `Предмет "${subject}" успешно добавлен`,
                life: 3000,
                closable: true
            });
        } catch (e) {
            // Обработка ошибки при отправке
            newSubjectError.value = true;
            toast.add({
                severity: 'error',
                summary: 'Ошибка',
                detail: e?.response?.data?.message || `Ошибка при добавлении предмета "${subject}"`,
                life: 3000,
                closable: true
            });
            continue; // Продолжаем с следующего предмета
        }
    }

    // Очистка поля после завершения отправки
    newSubjectError.value = false;
    importingSubjects.value = '';
}

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },

});
</script>

<template>
    <div class="flex flex-col gap-4">

        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Предметы</h1>
        </div>
        <div class="">
            <form class="flex flex-wrap  items-center gap-4 p-4 rounded-lg dark:bg-surface-800">
                <InputText :invalid="newSubjectError" placeholder="Пример: Математика" v-model="newSubjectName">
                </InputText>
                <Button type="submit" @click.prevent="addSubject" :disabled="!newSubjectName"
                    label="Добавить предмет"></Button>
                <Button label="Импорт" icon="pi pi-file-import" outlined type="submit"
                    @click.prevent="importSubjectsState = !importSubjectsState"></Button>
                <div v-if="importSubjectsState" class="flex flex-col gap-2">

                    <Textarea placeholder="Введите в столбик название предметов" v-model="importingSubjects" rows="5"
                        cols="30" />
                    <Button type="submit" @click.prevent="parseAndSendSubjects">Импортировать</Button>
                </div>
            </form>
        </div>
        <div class="">
            <DataTable paginator :rows="10" v-model:filters="filters" :globalFilterFields="['name']"
                :loading="isUpdated || isDestroyed || isStored" v-model:selection="selectedSubjects"
                v-model:editingRows="editingRows" :value="subjects" editMode="row" dataKey="id"
                @row-edit-save="onRowEditSave" :pt="{
                    table: { style: 'min-width: 50rem' }
                }">
                <template #header>
                    <div class="flex justify-between">
                        <Button severity="danger" :disabled="!selectedSubjects.length || !subjects.length" type="button"
                            icon="pi pi-trash" label="Удалить" outlined @click="deleteSubjects" />
                        <InputText v-model="filters['global'].value" placeholder="Поиск" />
                    </div>
                </template>
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                <Column field="name" header="Название предмета" style="width: 40%">
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" />
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