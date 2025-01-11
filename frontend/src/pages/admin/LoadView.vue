<script setup lang="ts">
  import { useDateFormat } from '@vueuse/core';
  import DataTable, {
    type DataTableCellEditCompleteEvent,
  } from 'primevue/datatable';
  import Column from 'primevue/column';
  import { FilterMatchMode } from '@primevue/core/api';
  import { reactive, ref, watch } from 'vue';
  import MultiSelect from 'primevue/multiselect';
  import Button from 'primevue/button';
  import Textarea from 'primevue/textarea';
  import InputText from 'primevue/inputtext';
  import Select from 'primevue/select';
  import {
    useDestroyLoadQuery,
    useLoadsQuery,
    useStoreLoadQuery,
  } from '@/queries/loads';
  import { useTeachersQuery } from '@/queries/teachers';
  import { useSubjectsQuery } from '@/queries/subjects';
  import { useGroupsQuery } from '@/queries/groups';
  import { useSemestersQuery } from '@/queries/semesters';
  import type {
    Group,
    Semester,
    Subject,
    Teacher,
  } from '@/components/schedule/types';
  import { useToast } from 'primevue/usetoast';
  import { isAxiosError } from 'axios';
  import { useDebouncedSync } from '@/composables/functions';

  const toast = useToast();
  const selectedLoads = ref<{ id: number }[]>([]);
  const search = ref('');
  const searchQuery = ref('');
  const query = ref({
    search: searchQuery,
  });

  useDebouncedSync(search, searchQuery, 500, { maxWait: 1000 });
  const { data: loads, isLoading } = useLoadsQuery(query);
  const { data: teachers } = useTeachersQuery();
  const { data: subjects } = useSubjectsQuery();
  const { data: groups } = useGroupsQuery();
  const { data: semesters } = useSemestersQuery();

  const { mutateAsync: storeLoad } = useStoreLoadQuery();
  const { mutateAsync: destroyLoad } = useDestroyLoadQuery();

  const columns = ref([
    { field: 'semester', header: 'Семестр' },
    { field: 'teacher', header: 'Преподаватель' },
    { field: 'subject', header: 'Предмет' },
    { field: 'group', header: 'Группа' },
    { field: 'hours', header: 'Часы' },
  ]);

  const loadForm = reactive<{
    semester: Semester | null;
    teacher: Teacher | null;
    subject: Subject | null;
    group: Group | null;
    hours: string | null;
  }>({
    semester: null,
    teacher: null,
    subject: null,
    group: null,
    hours: null,
  });

  async function addLoad() {
    try {
      await storeLoad({
        body: {
          semester_id: loadForm.semester?.id,
          teacher_id: loadForm.teacher?.id,
          subject_id: loadForm.subject?.id,
          group_id: loadForm.group?.id,
          hours: loadForm.hours,
        },
      });
      loadForm.group = null;
      loadForm.subject = null;
      loadForm.teacher = null;
      loadForm.hours = null;
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

  const deleteLoads = async () => {
    if (selectedLoads.value.length === 0) return;

    for (let i = 0; i < selectedLoads.value.length; i++) {
      try {
        await destroyLoad(selectedLoads.value[i].id);
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
    selectedLoads.value = [];
  };

  const onCellEditComplete = (event: DataTableCellEditCompleteEvent) => {
    let { data, newValue, field } = event;
  };

  const fieldOptions: Record<string, any> = {
    teacher: teachers,
    subject: subjects,
    group: groups,
    semester: semesters,
  };
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-baseline justify-between">
      <h1 class="text-2xl">Нагрузка</h1>
    </div>
    <div class="flex flex-col gap-4">
      <form
        class="flex flex-wrap items-center gap-4 rounded-lg bg-surface-100 p-4 dark:bg-surface-900"
      >
        <Select
          v-model="loadForm.semester"
          option-label="name"
          :options="semesters"
          placeholder="Семестр"
        ></Select>
        <Select
          v-model="loadForm.teacher"
          option-label="name"
          :options="teachers"
          editable
          placeholder="Преподаватель"
        ></Select>
        <Select
          v-model="loadForm.subject"
          option-label="name"
          :options="subjects"
          editable
          placeholder="Предмет"
        ></Select>
        <Select
          v-model="loadForm.group"
          option-label="name"
          :options="groups"
          editable
          placeholder="Группа"
          fluid
        ></Select>
        <InputText
          v-model.number="loadForm.hours"
          class="max-w-20"
          placeholder="Часы"
        ></InputText>
        <Button
          class="flex-shrink-0"
          label="Добавить часы"
          @click.prevent="addLoad"
        ></Button>
      </form>
    </div>
    <div class="">
      <DataTable
        v-model:selection="selectedLoads"
        :loading="isLoading"
        :value="loads"
        edit-mode="cell"
        :pt="{
          table: { style: 'min-width: 50rem' },
          column: {
            bodycell: ({ state }: any) => ({
              // class: [{ '!py-0': state['d_editing'] }],
            }),
          },
        }"
        @cell-edit-complete="onCellEditComplete"
      >
        <template #header>
          <div class="flex flex-wrap items-center justify-between gap-2">
            <Button
              severity="danger"
              type="button"
              icon="pi pi-trash"
              label="Удалить"
              outlined
              @click.prevent="deleteLoads"
            />
            <InputText v-model="search" placeholder="Поиск" />
          </div>
        </template>
        <Column selection-mode="multiple" header-style="width: 3rem" />
        <Column
          v-for="col of columns"
          :key="col.field"
          :field="col.field"
          :header="col.header"
          :show-filter-menu="false"
        >
          <template #body="{ data, field }">
            {{ data[field].name || data[field] }}
          </template>
          <template #editor="{ data, field }">
            <template v-if="field !== 'hours'">
              <Select
                v-model="data[field]"
                fluid
                data-key="name"
                option-label="name"
                :options="fieldOptions[field]?.value"
              ></Select>
            </template>
            <template v-else>
              <InputText v-model="data[field]" class="max-w-20"> </InputText>
            </template>
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>
