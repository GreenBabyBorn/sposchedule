<script setup lang="ts">
  import DataTable from 'primevue/datatable';
  import Column from 'primevue/column';
  import DatePicker from 'primevue/datepicker';
  import Button from 'primevue/button';
  import { useGroupsQuery } from '@/queries/groups';
  import MultiSelect from 'primevue/multiselect';
  import { computed, ref } from 'vue';
  import { useAnalyticsSchedulesQuery } from '@/queries/schedules';
  import { useDateFormat } from '@vueuse/core';

  const { data: groups } = useGroupsQuery();

  const rangeDates = ref(null);
  const selectedGroups = ref(null);

  const start_date = computed(() =>
    rangeDates.value?.[0]
      ? useDateFormat(rangeDates?.value?.[0], 'DD.MM.YYYY').value
      : null
  );
  const end_date = computed(() =>
    rangeDates.value?.[1]
      ? useDateFormat(rangeDates?.value?.[1], 'DD.MM.YYYY').value
      : null
  );
  const groups_ids = computed(() =>
    selectedGroups.value?.map(group => group.id)
  );

  const { data, isLoading } = useAnalyticsSchedulesQuery(
    start_date,
    end_date,
    groups_ids
  );

  const dt = ref();

  const exportCSV = () => {
    // Формируем данные для экспорта
    const exportData = [];

    data.value.forEach(row => {
      Object.entries(row.subjects).forEach(([subject, hours]) => {
        exportData.push({
          group_name: row.group_name,
          subject,
          hours,
        });
      });
    });

    // Формируем CSV строку
    const csvContent = [
      ['Группа', 'Предмет', 'Часы'], // Заголовки
      ...exportData.map(item =>
        [item.group_name, item.subject, item.hours].join(',')
      ), // Данные
    ].join('\n');

    // Создаем ссылку для скачивания файла
    const BOM = '\uFEFF'; // Byte Order Mark
    const blob = new Blob([BOM + csvContent], {
      type: 'text/csv;charset=utf-8;',
    });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute(
      'download',
      `Экспорт ${start_date.value} - ${end_date.value}.csv`
    );
    link.click();
  };
</script>

<template>
  <div class="mx-auto flex flex-col gap-4 px-4 py-4">
    <div class="flex flex-wrap items-baseline justify-between">
      <h1 class="text-2xl">Аналитика</h1>
    </div>

    <div class="">
      <form
        class="flex flex-wrap items-center gap-2 rounded-lg bg-surface-100 p-4 dark:bg-surface-800"
      >
        <div class="flex flex-wrap items-center justify-start gap-2">
          <MultiSelect
            v-model="selectedGroups"
            :options="groups"
            option-label="name"
            filter
            placeholder="Выбрать группы"
            :max-selected-labels="3"
            class=""
          />
          <DatePicker
            v-model="rangeDates"
            append-to="self"
            placeholder="Период"
            date-format="dd.mm.yy"
            selection-mode="range"
            :manual-input="false"
            class="w-full md:w-60"
            select-other-months
          />
        </div>
      </form>
    </div>
    <div class="">
      <DataTable
        ref="dt"
        :loading="isLoading"
        :value="data"
        row-group-mode="rowspan"
      >
        <template #header>
          <div style="text-align: left">
            <Button
              :disabled="!data"
              icon="pi pi-external-link"
              label="Экспорт в CSV"
              @click="exportCSV()"
            />
          </div>
        </template>
        <Column field="group_name" header="Группа" style="min-width: 200px">
          <template #body="slotProps">
            {{ slotProps.data.group_name }}
          </template>
        </Column>
        <Column
          :exportable="true"
          :field="
            row =>
              Object.entries(row.subjects)
                .map(([subject, hours]) => `${subject} - ${hours} ак. ч.`)
                .join('\n ')
          "
          header="Предметы"
          style="min-width: 200px"
        >
          <template #body="slotProps">
            <div
              v-for="(value, key) in slotProps.data.subjects"
              :key="key"
              class=""
            >
              <p class="leading-normal">
                {{ key }} -
                <span class="text-lg">{{ value }} ак. ч.</span>
              </p>
            </div>
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>
