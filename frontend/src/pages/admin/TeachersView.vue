<script setup lang="ts">
  import { ref } from 'vue';
  import DataTable from 'primevue/datatable';
  import Column from 'primevue/column';
  import InputText from 'primevue/inputtext';
  import MultiSelect from 'primevue/multiselect';
  import Button from 'primevue/button';
  import { useDateFormat } from '@vueuse/core';
  import { useToast } from 'primevue/usetoast';
  import {
    useTeachersQuery,
    useDestroyTeacher,
    useStoreTeacher,
    useUpdateTeacher,
    useMergeTeachers,
  } from '../../queries/teachers';
  import { useSubjectsQuery } from '../../queries/subjects';
  import Chip from 'primevue/chip';
  import { FilterMatchMode } from '@primevue/core/api';
  import Dialog from 'primevue/dialog';

  const { data: teachers } = useTeachersQuery({ subjects: true });

  const toast = useToast();

  const newTeacherName = ref('');

  const newTeacherError = ref(false);

  const editingRows = ref([]);
  const selectedTeachers = ref([]);

  const { mutateAsync: mergeTeachers } = useMergeTeachers();
  const mergeTeacherName = ref('');
  const firstMergeTeacher = ref('');
  const secondMergeTeacher = ref('');
  const visible = ref(false);

  async function handleMergeSubjects() {
    await mergeTeachers({
      teacher_ids: [firstMergeTeacher.value, secondMergeTeacher.value],
      target_name: mergeTeacherName.value,
    });
    visible.value = false;
  }

  const { mutateAsync: updateTeacher, isPending: isUpdated } =
    useUpdateTeacher();

  const onRowEditSave = async event => {
    let { newData } = event;
    try {
      await updateTeacher({
        id: newData.id,
        body: {
          ...newData,
        },
      });
    } catch (e) {
      if (newData.id !== e?.response.data.teacher_id) {
        firstMergeTeacher.value = newData.id;
        secondMergeTeacher.value = e?.response.data.teacher_id;
        mergeTeacherName.value = newData.name;
        visible.value = true;
        return;
      }
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

  const { mutateAsync: destroyTeacher, isPending: isDestroyed } =
    useDestroyTeacher();
  const deleteTeachers = async () => {
    if (!selectedTeachers.value.length) return;

    for (let i = 0; i < selectedTeachers.value.length; i++) {
      try {
        await destroyTeacher(selectedTeachers.value[i].id);
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
    selectedTeachers.value = [];
  };

  const { mutateAsync: storeTeacher, isPending: isStored } = useStoreTeacher();
  const addTeacher = async () => {
    try {
      await storeTeacher({
        name: newTeacherName.value,
      });
    } catch (e) {
      newTeacherError.value = true;
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response.data.message,
        life: 3000,
        closable: true,
      });
      newTeacherName.value = '';
      return;
    }
    newTeacherError.value = false;
    newTeacherName.value = '';
  };

  const { data: subjects } = useSubjectsQuery();

  const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  });
</script>

<template>
  <Dialog
    v-model:visible="visible"
    modal
    header="Объединение"
    :style="{ width: '25rem' }"
  >
    <span class="mb-8 block text-surface-500 dark:text-surface-400"
      >Был найден преподаватель с таким же ФИО, хотите объединить?</span
    >
    <div class="mb-4 flex items-center gap-4">
      <label for="subject_name" class="w-24 font-semibold">ФИО</label>
      <InputText
        id="subject_name"
        v-model="mergeTeacherName"
        class="flex-auto"
      />
    </div>
    <div class="flex justify-end gap-2">
      <Button
        type="button"
        label="Отмена"
        severity="secondary"
        @click="visible = false"
      ></Button>
      <Button
        type="button"
        label="Объединить"
        @click="handleMergeSubjects"
      ></Button>
    </div>
  </Dialog>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-baseline justify-between">
      <h1 class="text-2xl">Преподаватели</h1>
    </div>
    <div class="">
      <form
        class="flex flex-wrap items-center gap-4 rounded-lg bg-surface-100 p-4 dark:bg-surface-900"
      >
        <InputText
          v-model="newTeacherName"
          :invalid="newTeacherError"
          placeholder="ФИО"
          class="w-full md:w-60"
        />

        <Button
          type="submit"
          :disabled="!newTeacherName"
          @click.prevent="addTeacher"
        >
          Добавить преподавателя
        </Button>
      </form>
    </div>
    <div class="">
      <DataTable
        v-model:filters="filters"
        v-model:selection="selectedTeachers"
        v-model:editing-rows="editingRows"
        paginator
        :rows="10"
        :loading="isUpdated || isDestroyed || isStored"
        :value="teachers"
        edit-mode="row"
        data-key="id"
        :pt="{
          table: { style: 'min-width: 50rem' },
        }"
        @row-edit-save="onRowEditSave"
      >
        <template #header>
          <div class="flex flex-wrap justify-between gap-2">
            <Button
              severity="danger"
              :disabled="!selectedTeachers.length || !teachers.length"
              type="button"
              icon="pi pi-trash"
              label="Удалить"
              outlined
              @click="deleteTeachers"
            />
            <InputText v-model="filters['global'].value" placeholder="Поиск" />
          </div>
        </template>
        <Column selection-mode="multiple" header-style="width: 3rem" />

        <Column sortable field="name" header="ФИО">
          <template #editor="{ data, field }">
            <InputText v-model="data[field]" class="w-full" />
          </template>
        </Column>
        <Column field="subjects" header="Предметы">
          <template #body="slotProps">
            <div class="flex flex-wrap gap-2">
              <Chip
                v-for="subject in slotProps.data.subjects"
                :key="subject.name"
                :label="subject.name"
              />
            </div>
          </template>
          <template #editor="{ data }">
            <MultiSelect
              v-model="data.subjects"
              display="chip"
              :options="subjects"
              option-label="name"
              filter
              placeholder="Выберите предметы"
              :max-selected-labels="3"
              class="w-48"
            />
            <!-- <InputText class="w-full" v-model="data[field]" /> -->
          </template>
        </Column>

        <Column
          sortable
          field="updated_at"
          header="Дата изменения"
          style="width: 20%"
        >
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
