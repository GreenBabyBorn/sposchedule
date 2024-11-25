<script setup lang="ts">
  import { reactive, ref } from 'vue';
  import DataTable, {
    type DataTableRowEditSaveEvent,
  } from 'primevue/datatable';
  import Column from 'primevue/column';
  import InputText from 'primevue/inputtext';
  import Button from 'primevue/button';
  import { useDateFormat } from '@vueuse/core';
  import { useToast } from 'primevue/usetoast';
  import { FilterMatchMode } from '@primevue/core/api';
  import {
    useBuildingsQuery,
    useDestroyBuilding,
    useStoreBuilding,
    useUpdateBuilding,
    type Building,
  } from '@/queries/buildings';
  import { isAxiosError } from 'axios';

  const toast = useToast();

  const editingRows = ref([]);
  const selectedBuildings = ref<Building[] | null>([]);

  const { data: buildings } = useBuildingsQuery();

  const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    location: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  });
  const { mutateAsync: updateBuilding } = useUpdateBuilding();
  const onRowEditSave = async (event: DataTableRowEditSaveEvent) => {
    let { newData, index } = event;
    try {
      await updateBuilding({
        name: buildings.value?.[index].name,
        body: newData,
      });
    } catch (e) {
      if (isAxiosError(e)) {
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail: e.response?.data.message,
          life: 3000,
          closable: true,
        });
      }
      return;
    }
  };

  const { mutateAsync: storeSubject } = useStoreBuilding();
  const addSubject = async () => {
    try {
      await storeSubject(newBuilding);
    } catch (e) {
      if (isAxiosError(e)) {
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail: e.response?.data.message,
          life: 3000,
          closable: true,
        });
        newBuilding.name = '';
        newBuilding.location = '';
      }
      return;
    }
    // newSubjectError.value = false
    newBuilding.name = '';
    newBuilding.location = '';
  };

  const { mutateAsync: destroyBuilding } = useDestroyBuilding();
  const deleteBuildings = async () => {
    if (!selectedBuildings.value?.length) return;

    for (let i = 0; i < selectedBuildings.value.length; i++) {
      try {
        await destroyBuilding(selectedBuildings.value[i].name);
      } catch (e) {
        if (isAxiosError(e))
          toast.add({
            severity: 'error',
            summary: 'Ошибка',
            detail: e.response?.data.message,
            life: 3000,
            closable: true,
          });
        return;
      }
    }
    selectedBuildings.value = [];
  };

  const newBuilding = reactive({
    name: '',
    location: '',
  });
</script>
<template>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-baseline justify-between">
      <h1 class="text-2xl">Корпуса</h1>
    </div>
    <div class="">
      <form
        class="flex flex-wrap items-center gap-4 rounded-lg bg-surface-100 p-4 dark:bg-surface-900"
      >
        <InputText
          v-model="newBuilding.name"
          placeholder="Название корпуса"
          class="w-full md:w-60"
        />
        <InputText
          v-model="newBuilding.location"
          placeholder="Адрес"
          class="w-full md:w-60"
        />

        <Button
          :disabled="!newBuilding.name"
          type="submit"
          @click.prevent="addSubject"
        >
          Добавить корпус
        </Button>
      </form>
    </div>
    <div class="">
      <DataTable
        v-model:filters="filters"
        v-model:selection="selectedBuildings"
        v-model:editing-rows="editingRows"
        :value="buildings"
        paginator
        :rows="10"
        edit-mode="row"
        data-key="name"
        :pt="{
          table: { style: 'min-width: 50rem' },
        }"
        @row-edit-save="onRowEditSave"
      >
        <template #header>
          <div class="flex flex-wrap justify-between gap-2">
            <Button
              :disabled="!selectedBuildings?.length || !buildings?.length"
              severity="danger"
              type="button"
              icon="pi pi-trash"
              label="Удалить"
              outlined
              @click="deleteBuildings"
            />
            <InputText v-model="filters['global'].value" placeholder="Поиск" />
          </div>
        </template>
        <Column selection-mode="multiple" header-style="width: 3rem" />

        <Column field="name" header="Название корпуса">
          <!-- <template #editor="{ data, field }">
                            <InputText class="w-full" v-model="data[field]" />
                        </template> -->
        </Column>
        <Column field="location" header="Адрес">
          <template #editor="{ data, field }">
            <InputText v-model="data[field]" class="w-full" />
          </template>
        </Column>

        <Column field="updated_at" header="Дата изменения">
          <template #body="slotProps">
            {{ useDateFormat(slotProps.data.updated_at, 'DD.MM.YY HH:mm:ss') }}
          </template>
        </Column>
        <Column
          :row-editor="true"
          style="width: 10%; min-width: 8rem"
          body-style="text-align:center"
        />
      </DataTable>
    </div>
  </div>
</template>
