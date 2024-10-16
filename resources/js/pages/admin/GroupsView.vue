<script setup lang="ts">
  import { ref } from 'vue';
  import DataTable from 'primevue/datatable';
  import Column from 'primevue/column';
  import Select from 'primevue/select';
  import InputText from 'primevue/inputtext';
  import Button from 'primevue/button';
  import { useDateFormat } from '@vueuse/core';
  import { useToast } from 'primevue/usetoast';
  import Chip from 'primevue/chip';
  import MultiSelect from 'primevue/multiselect';
  import { FilterMatchMode } from '@primevue/core/api';
  import Textarea from 'primevue/textarea';
  import {
    useDestroyGroup,
    useGroupsQuery,
    useStoreGroup,
    useUpdateGroup,
  } from '../../queries/groups';
  import { useSemestersQuery } from '@/queries/semesters';
  import { useConfirm } from 'primevue/useconfirm';
  import { useBuildingsQuery } from '@/queries/buildings';

  const toast = useToast();
  const { data: groups } = useGroupsQuery();

  const newGroupName = ref('');
  const newGroupError = ref(false);

  const editingRows = ref([]);
  const selectedGroups = ref([]);
  const courses = [
    {
      label: 1,
      value: 1,
    },
    {
      label: 2,
      value: 2,
    },
    {
      label: 3,
      value: 3,
    },
    {
      label: 4,
      value: 4,
    },
  ];

  const { mutateAsync: updateGroup, isPending: isUpdated } = useUpdateGroup();

  const onRowEditSave = async event => {
    let { newData } = event;

    try {
      await updateGroup({
        id: newData.id,
        body: newData,
      });
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

  const regexGroup = /^[a-zA-Zа-яА-Я]{2,4}-[1-4]\d{1,4}$/;
  const { mutateAsync: storeSubject, isPending: isStored } = useStoreGroup();

  const addGroup = async () => {
    if (!regexGroup.test(newGroupName.value)) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: 'Неверный формат названия Группы. Пример: ИС-401',
        life: 3000,
        closable: true,
      });
      newGroupError.value = true;
      return;
    }

    try {
      await storeSubject({
        specialization: newGroupName.value.split('-')[0],
        course: newGroupName.value.split('-')[1][0],
        index: newGroupName.value.split('-')[1].slice(1),
        semesters: selectedSemesters.value,
        buildings: selectedBuildings.value,
      });
    } catch (e) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response.data.message,
        life: 3000,
        closable: true,
      });
      newGroupName.value = '';
    }

    newGroupError.value = false;
    newGroupName.value = '';
    selectedSemesters.value = [];
    selectedBuildings.value = [];
  };

  const confirm = useConfirm();

  const { mutateAsync: destroyGroup, isPending: isDestroyed } =
    useDestroyGroup();

  const confirmDelete = () => {
    confirm.require({
      message: 'Удаление групп может сломать расписание',
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
        await deleteGroups();
      },
      reject: () => {},
    });
  };

  const deleteGroups = async () => {
    if (selectedGroups.value.length === 0) return;

    for (let i = 0; i < selectedGroups.value.length; i++) {
      try {
        await destroyGroup(selectedGroups.value[i].id);
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
    selectedGroups.value = [];
  };

  const { data: semesters } = useSemestersQuery();

  const selectedSemesters = ref([]);

  const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    course: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
  });

  const importGroupsState = ref();
  const importingGroups = ref();

  const parseAndSendGroups = async () => {
    // Разделяем введенные группы на массив строк, убирая пустые строки и пробелы
    const groups = importingGroups.value
      .split('\n')
      .map(group => group.trim())
      .filter(group => group);

    // Проходим по каждой группе и отправляем её на сервер
    for (const group of groups) {
      if (!regexGroup.test(group)) {
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail: `Неверный формат названия группы: ${group}. Пример: ИС-401`,
          life: 3000,
          closable: true,
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
          buildings: selectedBuildings.value,
        });

        // Успешное добавление группы
        toast.add({
          severity: 'success',
          summary: 'Успех',
          detail: `Группа ${group} успешно добавлена`,
          life: 3000,
          closable: true,
        });
      } catch (e) {
        // Обработка ошибки при отправке
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail:
            e?.response?.data?.message ||
            `Ошибка при добавлении группы ${group}`,
          life: 3000,
          closable: true,
        });
      }
    }

    // Очистка поля после завершения отправки
    importingGroups.value = '';
    selectedSemesters.value = [];
    selectedBuildings.value = [];
  };

  const { data: buildings } = useBuildingsQuery();
  const selectedBuildings = ref();
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap justify-between items-baseline">
      <h1 class="text-2xl">Группы</h1>
    </div>
    <div class="">
      <form
        class="flex flex-wrap items-center gap-4 p-4 rounded-lg dark:bg-surface-800"
      >
        <InputText
          v-model="newGroupName"
          :invalid="newGroupError"
          placeholder="Пример: ИС-401"
          class="w-full md:w-56"
        />
        <MultiSelect
          v-model="selectedSemesters"
          display="chip"
          :options="semesters"
          option-label="name"
          filter
          :max-selected-labels="3"
          placeholder="Выбрать семестры"
          class="w-full md:w-60"
        />
        <MultiSelect
          v-model="selectedBuildings"
          filter
          auto-filter-focus
          option-label="name"
          :options="buildings"
          placeholder="Корпус"
          class="w-full md:w-36"
        />

        <Button
          type="submit"
          :disabled="!newGroupName"
          @click.prevent="addGroup"
        >
          Добавить группу
        </Button>
        <Button
          icon="pi pi-file-import"
          outlined
          type="submit"
          label="Импорт"
          @click.prevent="importGroupsState = !importGroupsState"
        />
        <div v-if="importGroupsState" class="flex flex-col gap-2">
          <Textarea
            v-model="importingGroups"
            placeholder="Введите в столбик название групп"
            rows="5"
            cols="30"
          />
          <Button type="submit" @click.prevent="parseAndSendGroups">
            Импортировать
          </Button>
        </div>
      </form>
    </div>
    <div class="">
      <DataTable
        v-model:filters="filters"
        v-model:selection="selectedGroups"
        v-model:editing-rows="editingRows"
        paginator
        :rows="10"
        :global-filter-fields="['name', 'course']"
        :loading="isUpdated || isDestroyed || isStored"
        :value="groups"
        edit-mode="row"
        data-key="id"
        :pt="{
          table: { style: 'min-width: 50rem' },
        }"
        @row-edit-save="onRowEditSave"
      >
        <template #header>
          <div class="flex flex-wrap items-center gap-2 justify-between">
            <Button
              severity="danger"
              :disabled="!selectedGroups.length || !groups.length"
              type="button"
              icon="pi pi-trash"
              label="Удалить"
              outlined
              @click="confirmDelete"
            />
            <InputText v-model="filters['global'].value" placeholder="Поиск" />
          </div>
        </template>
        <Column selection-mode="multiple" header-style="width: 3rem" />
        <Column field="name" header="Название группы" />
        <Column field="buildings" header="Корпус">
          <template #body="slotProps">
            <div class="flex gap-2 flex-wrap">
              <Chip
                v-for="building in slotProps.data.buildings"
                :label="building.name"
              />
            </div>
          </template>
          <template #editor="{ data, field }">
            <MultiSelect
              v-model="data.buildings"
              data-key="name"
              :options="buildings"
              display="chip"
              option-label="name"
              filter
              placeholder="Выберите корпуса"
              class=""
            />
          </template>
        </Column>
        <Column field="specialization" header="Специальность">
          <template #editor="{ data, field }">
            <InputText v-model="data[field]" fluid />
          </template>
        </Column>
        <Column field="course" header="Курс">
          <template #editor="{ data, field }">
            <Select
              v-model="data[field]"
              :options="courses"
              option-label="label"
              option-value="value"
            />
          </template>
        </Column>
        <Column field="semesters" header="Семестры">
          <template #body="slotProps">
            <div class="flex gap-2 flex-wrap">
              <Chip
                v-for="semester in slotProps.data.semesters"
                :label="semester.name"
              />
            </div>
          </template>
          <template #editor="{ data, field }">
            <MultiSelect
              v-model="data.semesters"
              :max-selected-labels="2"
              display="chip"
              :options="semesters"
              option-label="name"
              filter
              placeholder="Выберите семестры"
              class="w-48"
            />
          </template>
        </Column>
        <Column field="updated_at" header="Дата изменения" style="width: 20%">
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
