<script setup lang="ts">
import { useDateFormat } from '@vueuse/core'
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Column from 'primevue/column';
import { FilterMatchMode } from '@primevue/core/api';
import Inplace from 'primevue/inplace';
import { useDestroyHistory, useHistoryQuery } from '@/queries/history';
import { ref, watch } from 'vue';
import { useToast } from 'primevue/usetoast';

const toast = useToast()
const selectedHistories = ref([]);
const currentPage = ref(1);  // Текущая страница
const rowsPerPage = ref(10); // Количество записей на странице

// Вызываем запрос с параметрами пагинации
const { data: histories, isLoading, refetch } = useHistoryQuery(currentPage, rowsPerPage);

// Следим за изменениями пагинации
watch([currentPage, rowsPerPage], () => {
    refetch();
});


const { mutateAsync: destroyHistory } = useDestroyHistory()
// const deleteHistories = async () => {
//     if (selectedHistories.value.length === 0) return;

//     for (let i = 0; i < selectedHistories.value.length; i++) {
//         try {
//             await destroyHistory(selectedHistories.value[i].id)
//         }
//         catch (e) {
//             toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
//             return
//         }
//     }
//     selectedHistories.value = []
// }

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    action: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    details: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    created_at: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
});

</script>

<template>
    <div class="flex flex-col gap-4">

        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">История</h1>
        </div>

        <div class="">

            <DataTable :loading="isLoading" v-model:selection="selectedHistories" paginator :rows="rowsPerPage"
                :first="(currentPage - 1) * rowsPerPage" :totalRecords="histories?.meta?.total" lazy
                :globalFilterFields="['action', 'details', 'created_at']" v-model:filters="filters"
                :value="histories?.data" :onPage="(event) => {

                    currentPage = event.page + 1;
                    rowsPerPage = event.rows;
                }" tableStyle="min-width: 50rem">
                <!-- <template #header>
                    <div class="flex flex-wrap items-center gap-2  justify-between">
                        <Button severity="danger" :disabled="!selectedHistories.length || !histories.length"
                            type="button" icon="pi pi-trash" label="Удалить" outlined @click="deleteHistories" />
                        <InputText v-model="filters['global'].value" placeholder="Поиск" />
                    </div>
                </template> -->
                <!-- <Column selectionMode="multiple" headerStyle="width: 3rem"></Column> -->
                <Column field="id" header="ID"></Column>
                <Column field="action" header="Действие"></Column>
                <!-- <Column field="details" header="Подробности">
                    <template #body="slotProps">
                        <Inplace>
                            <template #display>Показать</template>
                            <template #content>
                                <p class="m-0">
                                    {{ slotProps.data.details }}
                                </p>
                            </template>
                        </Inplace>
                    </template>
                </Column> -->
                <Column field="created_at" header="Дата">
                    <template #body="slotProps">
                        {{ useDateFormat(slotProps.data.created_at, 'DD.MM.YY HH:mm:ss') }}
                    </template>
                </Column>
                <Column field="user_name" header="Пользователь">
                </Column>
            </DataTable>
        </div>
    </div>
</template>