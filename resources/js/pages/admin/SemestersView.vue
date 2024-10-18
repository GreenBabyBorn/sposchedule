<script setup lang="ts">
  import { ref } from 'vue';
  import DataTable from 'primevue/datatable';
  import Column from 'primevue/column';
  import InputText from 'primevue/inputtext';
  import Button from 'primevue/button';
  import { useDateFormat } from '@vueuse/core';
  import { useToast } from 'primevue/usetoast';
  import DatePicker from 'primevue/datepicker';
  import {
    useStoreSemester,
    useSemestersQuery,
    useUpdateSemester,
    useDestroySemester,
  } from '@/queries/semesters';
  import { useConfirm } from 'primevue/useconfirm';

  const { data: semesters } = useSemestersQuery();

  const toast = useToast();

  const newSemesterError = ref(false);

  const editingRows = ref([]);
  const selectedSemesters = ref([]);

  const dates = ref();
  const years = ref();

  const indexSemester = ref(1);
  // const yearsSemester = ref(new Date(years.value[0]).getFullYear() + new Date(years.value[1]).getFullYear())
  // const startSemester = ref(dates.value[0])
  // const endSemester = ref(dates.value[1])

  const { mutateAsync: storeSemester, isPending: isStored } =
    useStoreSemester();
  const addSemester = async () => {
    try {
      await storeSemester({
        years: `${new Date(years.value[0]).getFullYear()}/${new Date(years.value[1]).getFullYear()}`,
        index: indexSemester.value,
        start: dates.value[0],
        end: dates.value[1],
      });
    } catch (e) {
      newSemesterError.value = true;
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response.data.message,
        life: 3000,
        closable: true,
      });
      // inputSemester = { years: '', index: 1 }
      return;
    }
    newSemesterError.value = false;
    // inputSemester = { index: 1, years: '' }
  };

  const { mutateAsync, isPending: isUpdated } = useUpdateSemester();
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

  const { mutateAsync: destroySemester, isPending: isDestroyed } =
    useDestroySemester();
  const deleteSemesters = async () => {
    if (!selectedSemesters.value.length) return;

    for (let i = 0; i < selectedSemesters.value.length; i++) {
      try {
        await destroySemester(selectedSemesters.value[i].id);
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
    selectedSemesters.value = [];
  };

  const minDate = ref(new Date());

  const confirm = useConfirm();
  const confirmDelete = () => {
    confirm.require({
      message: 'Удаление семестра может сломать расписание',
      header: 'Вы уверены?',
      icon: 'pi pi-info-circle',
      rejectLabel: 'Отмена',
      rejectProps: {
        label: 'Отмена',
        severity: 'secondary',
        outlined: true,
      },
      acceptProps: {
        label: 'Удалить',
        severity: 'danger',
      },
      accept: async () => {
        await deleteSemesters();
      },
      reject: () => {},
    });
  };
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap justify-between items-baseline">
      <h1 class="text-2xl">Семестры</h1>
    </div>
    <div class="">
      <form
        class="flex flex-wrap items-end gap-4 p-4 rounded-lg bg-surface-100 dark:bg-surface-800"
      >
        <div class="">
          <label for="years" class="block mb-1">Учебный год</label>
          <!-- <InputText id="years" v-model="inputSemester.years" placeholder="2023/2024"></InputText> -->
          <DatePicker
            v-model="years"
            append-to="self"
            placeholder="Учебный год"
            :min-date="minDate"
            view="year"
            input-id="dates"
            date-format="yy"
            selection-mode="range"
            :manual-input="false"
          />
        </div>
        <div class="">
          <label for=" semester" class="block mb-1">Номер семестра</label>
          <InputText
            v-model="indexSemester"
            v-keyfilter.int
            placeholder="Номер семестра"
            input-id="semester"
            fluid
            class=""
          />
        </div>
        <div class="">
          <label for="dates" class="block mb-1">Начало - Конец семестра</label>
          <DatePicker
            v-model="dates"
            append-to="self"
            placeholder="Начало - Конец семестра"
            input-id="dates"
            date-format="dd.mm.yy"
            selection-mode="range"
            :manual-input="false"
          />
        </div>

        <Button
          :disabled="!years || !indexSemester || !dates"
          @click.prevent="addSemester"
        >
          Добавить
        </Button>
      </form>
    </div>
    <div class="">
      <DataTable
        v-model:selection="selectedSemesters"
        v-model:editing-rows="editingRows"
        paginator
        :rows="10"
        :loading="isUpdated || isDestroyed || isStored"
        :value="semesters"
        edit-mode="row"
        data-key="id"
        :pt="{
          table: { style: 'min-width: 50rem' },
        }"
        @row-edit-save="onRowEditSave"
      >
        <template #header>
          <div class="flex justify-between">
            <Button
              severity="danger"
              :disabled="!selectedSemesters.length || !semesters.length"
              type="button"
              icon="pi pi-trash"
              label="Удалить"
              outlined
              @click="confirmDelete"
            />
          </div>
        </template>
        <Column selection-mode="multiple" header-style="width: 3rem" />
        <Column field="years" header="Учебный год">
          <template #editor="{ data, field }">
            <InputText v-model="data[field]" />
          </template>
        </Column>
        <Column field="index" header="Семестр">
          <template #editor="{ data, field }">
            <InputText
              v-model="data[field]"
              v-keyfilter.int
              input-id="minmax-buttons"
            />
          </template>
        </Column>
        <Column field="start" header="Начало семестра">
          <template #body="slotProps">
            {{ useDateFormat(slotProps.data.start, 'DD.MM.YY') }}
          </template>
        </Column>
        <Column field="end" header="Конец семестра">
          <template #body="slotProps">
            {{ useDateFormat(slotProps.data.end, 'DD.MM.YY') }}
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
