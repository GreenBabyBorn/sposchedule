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
  import { reactive, ref, toRef } from 'vue';
  import ToggleSwitch from 'primevue/toggleswitch';
  import type {
    Group,
    Lesson,
    LessonMainSchedule,
    MainSchedule,
    Schedule,
    Subject,
    Teacher,
    WeekDays,
    weekType,
    WeekTypeLesson,
  } from './types';
  import InputNumber from 'primevue/inputnumber';
  // import ToggleButton from 'primevue/togglebutton';

  const toast = useToast();

  interface Props {
    weekDay: WeekDays;
    group: Group | undefined;
    semester: Record<string, any> | null;
    lessons: LessonMainSchedule[];
    published: boolean | undefined;
    schedule: MainSchedule;
  }

  const props = defineProps<Props>();

  const group = toRef(() => props.group);
  const semester = toRef(() => props.semester);
  const weekDay = toRef(() => props.weekDay);
  const lessons = toRef(() => props.lessons);
  // const scheduleId = toRef(() => props.scheduleId);
  const schedule = toRef(() => props.schedule);

  const published = ref(props.published || false);

  const { data: subjects } = useSubjectsQuery();

  const { data: teachers } = useTeachersQuery();

  const { mutateAsync: updateLesson } = useUpdateLesson();
  async function editLesson(lesson?: WeekTypeLesson) {
    if (!lesson?.id) return;

    try {
      await updateLesson({
        lesson: { ...lesson, subject_id: lesson.subject.id },
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
    index: number | null;
    ЧИСЛ: {
      subject: Subject | null;
      teachers: Teacher[] | null;
      building: string | null;
      cabinet: string | null;
    };
    ЗНАМ?: {
      subject: Subject | null;
      teachers: Teacher[] | null;
      building: string | null;
      cabinet: string | null;
    } | null;
  };

  let newLesson = reactive<LessonWithWeekTypes>({
    index: Number(lessons.value?.[lessons.value.length - 1]?.index) + 1 || null,
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
    if (!schedule.value.id) {
      try {
        await storeSchedule({
          body: {
            group_id: group.value?.id,
            semester_id: semester.value?.id,
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

    return schedule.value.id || newSchedule.value?.data.id;
  }

  async function addNewLesson() {
    const schedule_id = await addOrUpdateSchedule();
    if (!schedule_id) return;

    // Создаем первый урок 'ЧИСЛ'
    if (addRowAddNewLessonState.value) {
      await createLesson('ЧИСЛ', schedule_id);
    } else {
      await createLesson(null, schedule_id);
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

  async function createLesson(
    weekType: weekType,
    schedule_id: number,
    lesson?: any,
    index?: number
  ) {
    let lessonData =
      weekType === 'ЧИСЛ' || weekType === null
        ? newLesson['ЧИСЛ']
        : newLesson['ЗНАМ'];
    if (lesson) {
      lessonData = lesson;
    }
    if (!lessonData || Object.keys(lessonData).length === 0) {
      // showToast('Ошибка', 'Недозаполненно');
      return;
    }
    if (!lessonData.subject) return;
    try {
      await storeLesson({
        lesson: {
          ...lessonData,
          index: !lesson ? newLesson.index : index,
          teachers: lessonData.teachers,
          week_type: weekType,
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

  async function removeLesson(lesson: any) {
    try {
      await destroyLesson({ lesson });
    } catch (e) {
      showError(e);
      return;
    }
  }

  function showError(e: any) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка',
      detail: e?.response?.data.message || 'Произошла ошибка',
      life: 3000,
      closable: true,
    });
  }

  const { mutateAsync: updateChangesSchedule } = useUpdateSchedule();
  async function handlePublished() {
    try {
      await updateChangesSchedule({
        id: schedule.value.id,

        body: {
          published: published.value,
        },
      });
    } catch (e: any) {
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

  const hideAddNewLesson = ref(false);
</script>

<template>
  <div class="schedule-item relative overflow-x-auto py-1">
    <div class="">
      <div
        class="flex h-full items-center gap-4 rounded-t-md px-4 py-2 dark:bg-surface-900"
      >
        <span class="text-2xl font-medium">{{ props.weekDay }}</span>
        <!-- <ToggleButton
          v-model="published"
          :disabled="!props.item.length"
          class="text-sm"
          fluid
          on-label="Снять с публикации"
          off-label="Опубликовать"
          @change="handlePublished"
        /> -->
        <ToggleSwitch
          v-if="props.lessons.length"
          v-model="published"
          :disabled="!props.lessons.length"
          :title="published ? 'Снять с публикации' : 'Опубликовать'"
          @change="handlePublished"
        />
      </div>
      <table class="schedule-table dark:bg-surface-900">
        <!-- <caption class="text-2xl font-medium  mb-2">{{ props.weekDay }}</caption> -->
        <thead v-show="lessons.length > 0 || hideAddNewLesson">
          <tr>
            <th>
              <!-- № -->
            </th>
            <th>
              <!-- Предмет -->
            </th>
            <th>
              <!-- Преподаватели -->
            </th>
            <th>
              <!-- Корпус -->
            </th>
            <th>
              <!-- Кабинет -->
            </th>
            <th>
              <!-- Действия -->
            </th>
          </tr>
        </thead>
        <tbody>
          <template v-for="item in lessons" :key="item.index">
            <tr>
              <td>
                <span class="text-xl font-medium">
                  {{ item.index }}
                  <!-- <InputText
                    v-model="item.index"
                    class="w-full text-center"
                  /> -->
                </span>
              </td>
              <td>
                <template v-for="lesson in item.types" :key="lesson?.week_type">
                  <div class="table-subrow">
                    <Select
                      v-model="lesson.subject"
                      filter
                      class="w-full text-left"
                      :options="subjects"
                      option-label="name"
                      @change="editLesson(lesson)"
                    />
                  </div>
                </template>
              </td>
              <td>
                <template v-for="lesson in item.types" :key="lesson?.week_type">
                  <div class="table-subrow">
                    <MultiSelect
                      v-model="lesson.teachers"
                      filter
                      placeholder="Выберите преподавателя"
                      class="w-full"
                      :options="teachers"
                      option-label="name"
                      @change="editLesson(lesson)"
                    />
                  </div>
                </template>
              </td>
              <td>
                <template v-for="lesson in item.types" :key="lesson?.week_type">
                  <div class="table-subrow">
                    <InputText
                      v-model="lesson.building"
                      class="w-full text-center"
                      @change="editLesson(lesson)"
                    />
                  </div>
                </template>
              </td>
              <td>
                <template v-for="lesson in item.types" :key="lesson?.week_type">
                  <div class="table-subrow">
                    <InputText
                      v-model="lesson.cabinet"
                      class="w-full text-center"
                      @change="editLesson(lesson)"
                    />
                  </div>
                </template>
              </td>
              <td>
                <template v-for="lesson in item.types" :key="lesson?.week_type">
                  <div class="table-subrow">
                    <Button
                      v-if="!lesson?.id"
                      text
                      :disabled="!lesson?.subject"
                      icon="pi pi-check"
                      @click="
                        createLesson(
                          lesson?.week_type,
                          schedule.id,
                          lesson,
                          item.index
                        )
                      "
                    />

                    <Button
                      v-if="lesson?.id"
                      text
                      icon="pi pi-trash"
                      severity="danger"
                      @click="removeLesson(lesson)"
                    />
                  </div>
                </template>
              </td>
            </tr>
          </template>
          <tr v-show="hideAddNewLesson" class="new-schedule">
            <td>
              <InputNumber
                v-model="newLesson.index"
                input-id="integeronly"
                input-class="w-full min-w-10 text-center"
                placeholder="№"
                :min="0"
                :max="10"
                size="small"
              />
              <!-- <InputText
                v-model="newLesson.index"
                placeholder="№"
                size="small"
                class="w-full min-w-10 text-center"
              /> -->
            </td>
            <td>
              <div class="table-subrow">
                <Select
                  v-model="newLesson['ЧИСЛ'].subject"
                  :reset-filter-on-hide="true"
                  :focus-on-hover="false"
                  filter
                  class="w-full text-left"
                  placeholder="Предмет"
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
                  placeholder="Предмет"
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
                  placeholder="Преподаватель"
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
                  placeholder="Преподаватель"
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
                  placeholder="Корпус"
                />
              </div>
              <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                <InputText
                  v-model="newLesson['ЗНАМ'].building"
                  size="small"
                  class="w-full text-center"
                  placeholder="Корпус"
                />
              </div>
            </td>
            <td>
              <div class="table-subrow">
                <InputText
                  v-model="newLesson['ЧИСЛ'].cabinet"
                  size="small"
                  class="w-full text-center"
                  placeholder="Кабинет"
                />
              </div>
              <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                <InputText
                  v-model="newLesson['ЗНАМ'].cabinet"
                  size="small"
                  class="w-full text-center"
                  placeholder="Кабинет"
                />
              </div>
            </td>
            <td>
              <div class="table-subrow">
                <Button
                  :disabled="
                    !newLesson.index &&
                    !(newLesson?.ЗНАМ?.subject || newLesson?.ЧИСЛ?.subject)
                  "
                  text
                  icon="pi pi-save"
                  @click="addNewLesson()"
                />
                <Button
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
    border-bottom: 2px rgb(var(--p-surface-600)) solid;
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
