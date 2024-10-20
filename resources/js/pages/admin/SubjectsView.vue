<script setup lang="ts">
  import { ref } from 'vue';
  import DataTable from 'primevue/datatable';
  import Column from 'primevue/column';
  import InputText from 'primevue/inputtext';
  import Button from 'primevue/button';
  import { useDateFormat } from '@vueuse/core';
  import { useToast } from 'primevue/usetoast';
  import {
    useSubjectsQuery,
    useDestroySubject,
    useStoreSubject,
    useUpdateSubject,
  } from '../../queries/subjects';
  import Textarea from 'primevue/textarea';
  import { FilterMatchMode } from '@primevue/core/api';

  const { data: subjects } = useSubjectsQuery();

  const toast = useToast();

  const newSubjectName = ref('');
  const newSubjectError = ref(false);

  const editingRows = ref([]);
  const selectedSubjects = ref([]);

  const { mutateAsync, isPending: isUpdated } = useUpdateSubject();
  const onRowEditSave = async event => {
    let { newData } = event;
    try {
      await mutateAsync({ id: newData.id, body: newData });
    } catch (e) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response.data.message,
        life: 3000,
        closable: true,
      });
      return;
    }
  };

  const { mutateAsync: destroySubject, isPending: isDestroyed } =
    useDestroySubject();
  const deleteSubjects = async () => {
    if (!selectedSubjects.value.length) return;

    for (let i = 0; i < selectedSubjects.value.length; i++) {
      try {
        await destroySubject(selectedSubjects.value[i].id);
      } catch (e) {
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail: e?.response.data.message,
          life: 3000,
          closable: true,
        });
        return;
      }
    }
    selectedSubjects.value = [];
  };

  const { mutateAsync: storeSubject, isPending: isStored } = useStoreSubject();
  const addSubject = async () => {
    try {
      await storeSubject(newSubjectName.value);
    } catch (e) {
      newSubjectError.value = true;
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response.data.message,
        life: 3000,
        closable: true,
      });
      newSubjectName.value = '';
      return;
    }
    newSubjectError.value = false;
    newSubjectName.value = '';
  };

  const importSubjectsState = ref();
  const importingSubjects = ref();

  async function parseAndSendSubjects() {
    // Разделяем введенные предметы на массив строк, убирая пустые строки и пробелы
    const subjects = importingSubjects.value
      .split('\n')
      .map(subject => subject.trim())
      .filter(subject => subject);

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
          closable: true,
        });
      } catch (e) {
        // Обработка ошибки при отправке
        newSubjectError.value = true;
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail:
            e?.response?.data?.message ||
            `Ошибка при добавлении предмета "${subject}"`,
          life: 3000,
          closable: true,
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
    <div class="flex flex-wrap items-baseline justify-between">
      <h1 class="text-2xl">Предметы</h1>
    </div>
    <div class="">
      <form
        class="flex flex-wrap items-center gap-4 rounded-lg bg-surface-100 p-4 dark:bg-surface-800"
      >
        <InputText
          v-model="newSubjectName"
          :invalid="newSubjectError"
          placeholder="Пример: Математика"
          class="w-full md:w-60"
        />
        <Button
          type="submit"
          :disabled="!newSubjectName"
          label="Добавить предмет"
          @click.prevent="addSubject"
        />
        <Button
          label="Импорт"
          icon="pi pi-file-import"
          outlined
          type="submit"
          @click.prevent="importSubjectsState = !importSubjectsState"
        />
        <div v-if="importSubjectsState" class="flex flex-col gap-2">
          <Textarea
            v-model="importingSubjects"
            placeholder="Введите в столбик название предметов"
            rows="5"
            cols="30"
          />
          <Button type="submit" @click.prevent="parseAndSendSubjects">
            Импортировать
          </Button>
        </div>
      </form>
    </div>
    <div class="">
      <DataTable
        v-model:filters="filters"
        v-model:selection="selectedSubjects"
        v-model:editing-rows="editingRows"
        paginator
        :rows="10"
        :global-filter-fields="['name']"
        :loading="isUpdated || isDestroyed || isStored"
        :value="subjects"
        edit-mode="row"
        data-key="id"
        :pt="{
          table: { style: 'min-width: 50rem' },
        }"
        @row-edit-save="onRowEditSave"
      >
        <template #header>
          <div class="flex flex-wrap items-center justify-between gap-2">
            <Button
              severity="danger"
              :disabled="!selectedSubjects.length || !subjects.length"
              type="button"
              icon="pi pi-trash"
              label="Удалить"
              outlined
              @click="deleteSubjects"
            />
            <InputText v-model="filters['global'].value" placeholder="Поиск" />
          </div>
        </template>
        <Column selection-mode="multiple" header-style="width: 3rem" />
        <Column field="name" header="Название предмета">
          <template #editor="{ data, field }">
            <InputText v-model="data[field]" />
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
