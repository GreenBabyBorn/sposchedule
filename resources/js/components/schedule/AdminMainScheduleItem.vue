<script setup lang="ts">
  import InputText from 'primevue/inputtext';
  import Button from 'primevue/button';
  import MultiSelect from 'primevue/multiselect';
  import Select from 'primevue/select';
  import { useSubjectsQuery } from '@/queries/subjects';
  import { useTeachersQuery } from '@/queries/teachers';
  import { useStoreSchedule, useUpdateSchedule } from '@/queries/schedules';
  import {
    useDestroyLesson,
    useStoreLesson,
    useUpdateLesson,
  } from '@/queries/lessons';
  import { useToast } from 'primevue/usetoast';
  import { reactive, ref, toRef, watch } from 'vue';
  import ToggleButton from 'primevue/togglebutton';

  const toast = useToast();

  const props = defineProps({
    weekDay: { type: String, required: true },
    group: { required: false, type: Object },
    semester: { required: true, type: Object },
    item: { required: true, type: [Object] },
    published: { required: false, type: Boolean },
  });

  const group = toRef(() => props.group);
  const semester = toRef(() => props.semester);
  const weekDay = toRef(() => props.weekDay);
  const items = toRef(() => props.item);

  const { data: subjects } = useSubjectsQuery();

  const { data: teachers } = useTeachersQuery();

  const { mutateAsync: updateLesson } = useUpdateLesson();
  async function editLesson(item) {
    if (!item.id) return;

    try {
      await updateLesson({
        id: item.id,
        body: {
          ...item,
          subject_id: item.subject.id,
        },
      });
    } catch (e) {
      showError(e);
    }
  }

  const addRowAddNewLessonState = ref(false);

  function addRowAddNewLesson() {
    addRowAddNewLessonState.value = !addRowAddNewLessonState.value;
    newLesson.ЗНАМ = addRowAddNewLessonState.value
      ? {
          subject: null,
          teachers: [],
          building: null,
          cabinet: null,
        }
      : null;
  }

  type LessonWithWeekTypes = {
    index: number;
    ЧИСЛ: {
      subject: any | null;
      teachers: [] | null;
      building: string | null;
      cabinet: string | null;
    };
    ЗНАМ?: {
      subject: any | null;
      teachers: [] | null;
      building: string | null;
      cabinet: string | null;
    };
  };

  let newLesson = reactive<LessonWithWeekTypes>({
    index: null,
    ЧИСЛ: {
      subject: null,
      teachers: [],
      building: null,
      cabinet: null,
    },
  });

  const { mutateAsync: storeSchedule, data: newSchedule } = useStoreSchedule();
  const { mutateAsync: storeLesson, isSuccess } = useStoreLesson();
  const { mutateAsync: destroyLesson } = useDestroyLesson();

  async function addOrUpdateSchedule() {
    const loadedSchedule = items.value.find(item => item.schedule_id);

    if (!loadedSchedule?.schedule_id) {
      try {
        await storeSchedule({
          body: {
            group_id: group.value.id,
            semester_id: semester.value.id,
            type: 'main',
            week_day: weekDay.value,
            // view_mode: 'table',
          },
        });
      } catch (e) {
        showError(e);
        return;
      }
    }

    return loadedSchedule?.schedule_id || newSchedule.value.data.id;
  }

  async function addNewLesson() {
    const schedule_id = await addOrUpdateSchedule();
    if (!schedule_id) return;

    // Создаем первый урок 'ЧИСЛ'
    if (addRowAddNewLessonState.value) {
      await createLesson('ЧИСЛ', schedule_id);
    } else {
      await createLesson('', schedule_id);
    }

    // Если 'ЗНАМ' также добавляется
    if (
      addRowAddNewLessonState.value &&
      newLesson.ЗНАМ?.subject &&
      newLesson.index
    ) {
      await createLesson('ЗНАМ', schedule_id);
      newLesson.ЗНАМ = {
        subject: null,
        teachers: [],
        building: newLesson.ЗНАМ.building,
        cabinet: null,
      };
    }
    if (isSuccess.value) {
      newLesson = reactive<LessonWithWeekTypes>({
        index: Number(newLesson.index) + 1,
        ЧИСЛ: {
          subject: null,
          teachers: [],
          building: newLesson.ЧИСЛ.building,
          cabinet: null,
        },
        ЗНАМ: null,
      });
      addRowAddNewLessonState.value = false;
    }
  }

  async function createLesson(weekType, schedule_id, item?) {
    let lessonData =
      weekType === 'ЧИСЛ' || weekType === ''
        ? newLesson['ЧИСЛ']
        : newLesson['ЗНАМ'];
    if (item) {
      lessonData = item[weekType];
    }
    if (!lessonData || Object.keys(lessonData).length === 0) {
      // showToast('Ошибка', 'Недозаполненно');
      return;
    }
    if (!lessonData.subject) return;
    try {
      await storeLesson({
        body: {
          ...lessonData,
          teachers: lessonData.teachers,
          week_type: weekType,
          index: !item ? newLesson.index : item.index,
          subject_id: lessonData.subject?.id,
          schedule_id: schedule_id,
        },
      });
      // newLesson = {
      //     index: null,
      //     ЧИСЛ: {
      //         subject: null,
      //         teachers: [],
      //         building: null,
      //         cabinet: null,
      //     },
      // }
    } catch (e) {
      showError(e);
      return;
    }
  }

  async function removeLesson(id) {
    try {
      await destroyLesson({ id });
    } catch (e) {
      showError(e);
      return;
    }
  }

  function showError(e) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка',
      detail: e?.response?.data.message || 'Произошла ошибка',
      life: 3000,
      closable: true,
    });
  }

  function showToast(summary, detail) {
    toast.add({
      severity: 'error',
      summary: summary,
      detail: detail,
      life: 3000,
      closable: true,
    });
  }
  const published = ref(props.item?.[0]?.published || null);

  watch(
    () => props.published,
    newValue => {
      published.value = newValue;
    }
  );

  const { mutateAsync: updateChangesSchedule } = useUpdateSchedule();
  async function handlePublished() {
    try {
      await updateChangesSchedule({
        id: props.item?.[0].schedule_id,

        body: {
          published: published.value,
        },
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
  }

  const hideAddNewLesson = ref(false);
</script>

<template>
  <div class="relative overflow-x-auto schedule-item py-1">
    <div class="">
      <div class="flex dark:bg-surface-800 py-2 px-4 items-center h-full gap-4">
        <span class="text-2xl font-medium">{{ props.weekDay }}</span>
        <ToggleButton
          v-model="published"
          :disabled="!props.item.length"
          class="text-sm"
          fluid
          on-label="Снять с публикации"
          off-label="Опубликовать"
          @change="handlePublished"
        />
      </div>
      <table class="schedule-table dark:bg-surface-900">
        <!-- <caption class="text-2xl font-medium  mb-2">{{ props.weekDay }}</caption> -->
        <thead v-show="items.length > 0 || hideAddNewLesson">
          <tr>
            <th>№</th>
            <th>Предмет</th>
            <th>Преподаватели</th>
            <th>Корпус</th>
            <th>Кабинет</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="item in items">
            <tr>
              <td>
                <span class="text-xl font-medium">
                  {{ item.index }}
                </span>
              </td>
              <td>
                <div v-if="item['ЧИСЛ']" class="table-subrow">
                  <Select
                    v-model="item['ЧИСЛ'].subject"
                    filter
                    class="w-full text-left"
                    :options="subjects"
                    option-label="name"
                    @change="editLesson(item['ЧИСЛ'])"
                  />
                </div>

                <div v-if="item.lesson" class="table-subrow">
                  <Select
                    v-if="item.lesson.subject"
                    v-model="item.lesson.subject"
                    filter
                    class="w-full text-left"
                    :options="subjects"
                    option-label="name"
                    @change="editLesson(item.lesson)"
                  />
                  <span v-else class="text-red-400">Предмет был удален</span>
                </div>

                <div v-if="item['ЗНАМ']" class="table-subrow">
                  <Select
                    v-model="item['ЗНАМ'].subject"
                    filter
                    class="w-full text-left"
                    :options="subjects"
                    option-label="name"
                    @change="editLesson(item['ЗНАМ'])"
                  />
                </div>
              </td>
              <td>
                <div v-if="item['ЧИСЛ']" class="table-subrow">
                  <MultiSelect
                    v-model="item['ЧИСЛ'].teachers"
                    filter
                    placeholder="Выберите преподавателя"
                    class="w-full"
                    :options="teachers"
                    option-label="name"
                    @change="editLesson(item['ЧИСЛ'])"
                  />
                </div>
                <div v-if="item.lesson" class="table-subrow">
                  <MultiSelect
                    v-model="item.lesson.teachers"
                    filter
                    placeholder="Выберите преподавателя"
                    class="w-full"
                    :options="teachers"
                    option-label="name"
                    @change="editLesson(item.lesson)"
                  />
                </div>
                <div v-if="item['ЗНАМ']" class="table-subrow">
                  <MultiSelect
                    v-model="item['ЗНАМ'].teachers"
                    filter
                    placeholder="Выберите преподавателя"
                    class="w-full"
                    :options="teachers"
                    option-label="name"
                    @change="editLesson(item['ЗНАМ'])"
                  />
                </div>
              </td>
              <td>
                <div v-if="item['ЧИСЛ']" class="table-subrow">
                  <InputText
                    v-model="item['ЧИСЛ'].building"
                    class="w-full text-center"
                    @change="editLesson(item['ЧИСЛ'])"
                  />
                </div>
                <div v-if="item.lesson" class="table-subrow">
                  <InputText
                    v-model="item.lesson.building"
                    class="w-full text-center"
                    @change="editLesson(item.lesson)"
                  />
                </div>
                <div v-if="item['ЗНАМ']" class="table-subrow">
                  <InputText
                    v-model="item['ЗНАМ'].building"
                    class="w-full text-center"
                    @change="editLesson(item['ЗНАМ'])"
                  />
                </div>
              </td>
              <td>
                <div v-if="item['ЧИСЛ']" class="table-subrow">
                  <InputText
                    v-model="item['ЧИСЛ'].cabinet"
                    class="w-full text-center"
                    @change="editLesson(item['ЧИСЛ'])"
                  />
                </div>
                <div v-if="item.lesson" class="table-subrow">
                  <InputText
                    v-model="item.lesson.cabinet"
                    class="w-full text-center"
                    @change="editLesson(item.lesson)"
                  />
                </div>
                <div v-if="item['ЗНАМ']" class="table-subrow">
                  <InputText
                    v-model="item['ЗНАМ'].cabinet"
                    class="w-full text-center"
                    @change="editLesson(item['ЗНАМ'])"
                  />
                </div>
              </td>
              <td>
                <div v-if="item.lesson" class="table-subrow">
                  <!-- <Button v-if="!item['ЗНАМ']" @click="addRowLesson(item['ЧИСЛ'], 'ЗНАМ')" text
                                        :icon="`pi pi-percentage`"></Button> -->
                  <Button
                    text
                    icon="pi pi-trash"
                    severity="danger"
                    @click="removeLesson(item.lesson.id)"
                  />
                </div>
                <div v-if="item['ЧИСЛ']" class="table-subrow">
                  <Button
                    v-if="!item['ЧИСЛ'].id"
                    text
                    :disabled="!item['ЧИСЛ'].subject"
                    icon="pi pi-check"
                    @click="createLesson('ЧИСЛ', item.schedule_id, item)"
                  />

                  <Button
                    v-if="item['ЧИСЛ'].id"
                    text
                    icon="pi pi-trash"
                    severity="danger"
                    @click="removeLesson(item['ЧИСЛ'].id)"
                  />
                </div>

                <div v-if="item['ЗНАМ']" class="table-subrow">
                  <Button
                    v-if="!item['ЗНАМ'].id"
                    text
                    :disabled="!item['ЗНАМ'].subject"
                    icon="pi pi-check"
                    @click="createLesson('ЗНАМ', item.schedule_id, item)"
                  />

                  <Button
                    v-if="item['ЗНАМ'].id"
                    text
                    icon="pi pi-trash"
                    severity="danger"
                    @click="removeLesson(item['ЗНАМ'].id)"
                  />
                </div>
              </td>
            </tr>
          </template>
          <tr v-show="hideAddNewLesson" class="new-schedule">
            <td>
              <InputText
                v-model="newLesson.index"
                size="small"
                class="min-w-10 w-full text-center"
              />
            </td>
            <td>
              <div class="table-subrow">
                <Select
                  v-model="newLesson['ЧИСЛ'].subject"
                  :reset-filter-on-hide="true"
                  :focus-on-hover="false"
                  filter
                  class="w-full text-left"
                  :options="subjects"
                  option-label="name"
                />
              </div>
              <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                <Select
                  v-model="newLesson['ЗНАМ'].subject"
                  :reset-filter-on-hide="true"
                  :focus-on-hover="false"
                  filter
                  class="w-full text-left"
                  :options="subjects"
                  option-label="name"
                />
              </div>
            </td>
            <td>
              <div class="table-subrow">
                <MultiSelect
                  v-model="newLesson['ЧИСЛ'].teachers"
                  :reset-filter-on-hide="true"
                 
                  filter
                  placeholder="Выберите преподавателя"
                  class="w-full"
                  :options="teachers"
                  option-label="name"
                />
              </div>
              <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                <MultiSelect
                  v-model="newLesson['ЗНАМ'].teachers"
                  :reset-filter-on-hide="true"
                  :auto-filter-focus="true"
                  filter
                  placeholder="Выберите преподавателя"
                  class="w-full"
                  :options="teachers"
                  option-label="name"
                />
              </div>
            </td>
            <td>
              <div class="table-subrow">
                <InputText
                  v-model="newLesson['ЧИСЛ'].building"
                  size="small"
                  class="w-full text-center"
                />
              </div>
              <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                <InputText
                  v-model="newLesson['ЗНАМ'].building"
                  size="small"
                  class="w-full text-center"
                />
              </div>
            </td>
            <td>
              <div class="table-subrow">
                <InputText
                  v-model="newLesson['ЧИСЛ'].cabinet"
                  size="small"
                  class="w-full text-center"
                />
              </div>
              <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                <InputText
                  v-model="newLesson['ЗНАМ'].cabinet"
                  size="small"
                  class="w-full text-center"
                />
              </div>
            </td>
            <td>
              <div class="table-subrow">
                <Button text icon="pi pi-save" @click="addNewLesson()" /><Button
                  text
                  :title="`${addRowAddNewLessonState ? 'Не дробное' : 'Дробное'} `"
                  :icon="`pi ${addRowAddNewLessonState ? 'pi-arrows-h' : 'pi-percentage'} `"
                  @click="addRowAddNewLesson()"
                />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="mt-2 flex items-center justify-center">
        <Button
          label="Новая пара"
          title="Открыть форму для добавления пары"
          size="small"
          outlined
          severity="secondary"
          class="w-full text-surface-800 dark:text-white/80"
          :icon="!hideAddNewLesson ? 'pi pi-angle-down' : 'pi pi-angle-up'"
          @click="hideAddNewLesson = !hideAddNewLesson"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
  .new-schedule {
  }

  .schedule-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    /* font-size: 0.8rem; */
    position: relative;
    /* Чтобы все столбцы имели фиксированную ширину */
  }

  .add-btn {
    position: absolute;
    bottom: 0;
    left: 50%;
    color: red;
    cursor: pointer;
  }

  .schedule-table th,
  .schedule-table td {
    border: 1px solid var(--p-surface-600);
    /* padding: 10px; */
    text-align: center;
  }

  .schedule-table th {
    font-weight: bold;
  }

  /* Подстроки в ячейках */
  .table-subrow {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
  }

  .schedule-table th:first-child,
  .schedule-table td:first-child {
    width: 5%;
  }

  .schedule-table th:nth-child(4),
  .schedule-table td:nth-child(4) {
    width: 10%;
  }

  .schedule-table th:nth-child(5),
  .schedule-table td:nth-child(5) {
    width: 10%;
  }

  .schedule-table th:nth-child(6),
  .schedule-table td:nth-child(6) {
    width: 12%;
  }

  tbody tr {
    border-bottom: 2px var(--p-surface-600) solid;
    /* background: rgba(128, 128, 128, 0.243); */
  }

  tbody tr:last-child {
    border-bottom: none;
  }

  tbody > tr:last-child {
    /* border-top: 2px rgb(0, 153, 255) solid; */
    background: rgba(255, 255, 255, 0.062);
  }
</style>
