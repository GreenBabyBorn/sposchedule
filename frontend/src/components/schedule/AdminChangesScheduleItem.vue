<script setup lang="ts">
  import MultiSelect from 'primevue/multiselect';
  // import Select from 'primevue/select';
  import CustomSelect from '../ui/custom/select/index';
  import InputText from 'primevue/inputtext';

  import InputNumber from 'primevue/inputnumber';

  import Textarea from 'primevue/textarea';
  import Button from 'primevue/button';
  import { useSubjectsQuery } from '@/queries/subjects';
  import {
    useTeachersFromSubjectQuery,
    useTeachersQuery,
  } from '@/queries/teachers';
  import {
    useCreateScheduleWithChanges,
    useStoreScheduleChange,
    useUpdateSchedule,
  } from '@/queries/schedules';
  import {
    useDestroyLesson,
    useStoreLesson,
    useUpdateLesson,
  } from '@/queries/lessons';
  import { useToast } from 'primevue/usetoast';
  import { computed, reactive, ref, toRef, watch, watchEffect } from 'vue';
  // import ToggleButton from 'primevue/togglebutton';
  import ToggleSwitch from 'primevue/toggleswitch';
  import BlockUI from 'primevue/blockui';
  import AdminChangesScheduleItemRow from './AdminChangesScheduleItemRow.vue';
  import { useNow, useStorage } from '@vueuse/core';
  import type {
    ChangesSchedules,
    Group,
    Lesson,
    Schedule,
    ScheduleType,
    SubjectWithTeachers,
    Teacher,
  } from './types';
  import { type SelectFilterEvent } from 'primevue';
  import AdminChangesScheduleItemRowPreview from './AdminChangesScheduleItemRowPreview.vue';
  import { useQueryClient } from '@tanstack/vue-query';
  import { useScheduleStore } from '@/stores/schedule';
  import { storeToRefs } from 'pinia';
  import axios from 'axios';
  // import RCESelect from '../ui/RCESelect.vue';

  interface Props {
    group: Group;
    date: string | Date;
    type: ScheduleType | undefined;
    semester: Record<string, any>;
    lessons: Lesson[];
    schedule: Schedule;
    published?: boolean;
    disabled?: boolean;
  }

  const props = defineProps<Props>();

  const schedule = toRef(() => props.schedule);
  const lessons = toRef(() => props.lessons);
  const dateRef = toRef(() => props.date);
  const semester = toRef(() => props.semester);
  const group = toRef(() => props.group);
  const disabled = toRef(() => props.disabled);
  const date = toRef(() => props.date);
  const type = toRef(() => props.type);

  const published = ref(props.published);
  const toast = useToast();

  const isEdit = ref(false);

  const queryClient = useQueryClient();

  const scheduleStore = useScheduleStore();
  const {
    formattedDate,
    selectedCourse,
    selectedGroup,
    building,
    schedulesChanges,
  } = storeToRefs(scheduleStore);

  function showError(e: any) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка',
      detail: e,
      life: 3000,
      closable: true,
    });
  }

  async function invalidateSchedule() {
    const updatedSchedule = await queryClient.fetchQuery({
      queryKey: ['scheduleChanges', group, date],
      queryFn: async () =>
        (
          await axios.get(`/api/groups/${group.value.id}/schedule`, {
            params: {
              date: date.value,
            },
          })
        ).data,
    });
    await queryClient.setQueryData(
      [
        'scheduleChanges',
        formattedDate,
        building,
        selectedCourse,
        selectedGroup,
      ],

      (oldData: unknown) => {
        if (!oldData) return;
        const newData: ChangesSchedules = JSON.parse(JSON.stringify(oldData));

        // Асинхронное обновление schedules с помощью for...of
        for (let i = 0; i < newData.schedules.length; i++) {
          const s = newData.schedules[i];
          if (s.group.id === group.value.id) {
            newData.schedules[i] = updatedSchedule; // Обновление schedule на полученное значение
          }
        }
        return newData;
      }
    );
  }

  watch(
    () => props.published,
    newValue => {
      published.value = newValue;
    }
  );

  type newLessonType = {
    index: number;
    subject: SubjectWithTeachers | null;
    teachers: Teacher[] | null;
    building: string | null;
    cabinet: string | null;
    message?: string | null;
  };
  let newLesson = reactive<newLessonType>({
    index: Number(lessons.value?.slice(-1)?.[0]?.index) + 1 || 0,
    subject: null,
    teachers: [],
    building: lessons.value.slice(-1)?.[0]?.building || null,
    cabinet: null,
    message: null,
  });

  watch(lessons, () => {
    newLesson.index =
      Number(lessons.value?.slice(-1)?.[0]?.index) + 1 || newLesson.index;
    newLesson.building = lessons.value?.slice(-1)?.[0]?.building || null;
  });

  const { mutateAsync: storeSchedule, data: newSchedule } =
    useStoreScheduleChange();
  const { mutateAsync: storeLesson, data: createdLesson } = useStoreLesson();

  async function addNewLesson() {
    if (lessons.value.find(l => l.index === newLesson.index)) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: `Пара с таким номером уже существует`,
        life: 3000,
      });
      return;
    }
    // Если тип расписания 'main', конвертируем его в изменения
    if (type.value === 'main') {
      try {
        await createScheduleWithChanges({
          body: {
            group_id: props.group.id,
            lessons: lessons.value,
            type: 'changes',
            date: props.date,
            semester_id: props.semester.id,
          },
        });
        // invalidateChange();
      } catch (e: any) {
        showError(
          e?.response?.data?.message ||
            'Не удалось конвертировать основное расписание в изменения.'
        );
        return;
      }
    }

    // Если расписание не загружено и тип 'changes', создаем новое расписание
    if (!schedule.value.id) {
      try {
        await storeSchedule({
          body: {
            group_id: group.value.id,
            semester_id: semester.value.id,
            type: 'changes',
            date: props.date,
          },
        });
        // invalidateChange();
      } catch (e: any) {
        showError(
          e?.response?.data?.message || 'Не удалось сохранить расписание.'
        );
        return;
      }
    }

    // Определяем правильный ID для использования в storeLesson
    let scheduleIdforLesson;
    if (schedule.value.id && type.value !== 'main') {
      scheduleIdforLesson = schedule.value.id;
    } else if (type.value === 'main') {
      scheduleIdforLesson = newChanges.value?.id; // Убедитесь, что newChanges имеет значение перед доступом к его свойствам
    } else {
      scheduleIdforLesson = newSchedule.value?.id; // Тоже необходимо проверить наличие значения
    }
    try {
      await storeLesson({
        lesson: {
          ...newLesson,
          schedule_id: scheduleIdforLesson,
          subject_id: newLesson.subject?.id,
          week_type: null,
        },
      });

      for (let s of schedulesChanges.value!.schedules) {
        if (
          s.lessons?.find(
            l =>
              l.cabinet === newLesson.cabinet &&
              l.index === newLesson.index &&
              l.schedule_id !== scheduleIdforLesson &&
              l.cabinet !== null &&
              newLesson.cabinet !== null &&
              l.cabinet.length > 1 &&
              newLesson.cabinet.length > 1
          )
        ) {
          toast.add({
            severity: 'warn',
            summary: 'Внимание',
            detail: `Кабинет ${newLesson.cabinet} уже используется в расписании ${s.group.name} ${newLesson.index} парой`,
            life: 5000,
          });
        }
      }

      await invalidateSchedule();

      newLesson.subject = null;
      newLesson.teachers = [];
      newLesson.building = lessons.value?.slice(-1)?.[0]?.building || null;
      newLesson.cabinet = null;
      newLesson.message = null;
    } catch (e: any) {
      showError(e?.response?.data?.message || 'Не удалось сохранить пару.');
      return;
    }
  }

  const { mutateAsync: updateLesson } = useUpdateLesson();

  const { mutateAsync: createScheduleWithChanges, data: newChanges } =
    useCreateScheduleWithChanges();

  async function editLesson(item: Lesson) {
    if (!item.id) return;

    if (lessons.value.find(l => l.index === item.index && l.id !== item.id)) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: `Пара с таким номером уже существует`,
        life: 3000,
      });
      return;
    }
    // if (!item.message == !item.subject) return;
    for (let s of schedulesChanges.value!.schedules) {
      if (
        s.lessons?.find(
          l =>
            l.cabinet === item.cabinet &&
            l.index === item.index &&
            l.schedule_id !== item.schedule_id &&
            l.cabinet !== null &&
            item.cabinet !== null &&
            l.cabinet.length > 1 &&
            item.cabinet.length > 1
        )
      ) {
        toast.add({
          severity: 'warn',
          summary: 'Внимание',
          detail: `Кабинет ${item.cabinet} уже используется в расписании ${s.group.name} ${item.index} парой`,
          life: 5000,
        });
      }
    }

    if (type.value === 'main') {
      try {
        await createScheduleWithChanges({
          body: {
            group_id: props.group.id,
            type: 'changes',
            lessons: schedule.value?.lessons,
            date: props.date,
            semester_id: props.semester.id,
            week_type: null,
          },
        });
        await invalidateSchedule();
        // queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
        return;
      } catch (e: any) {
        showError(e?.response.data.message);
        return;
      }
    }

    const updatingLesson: Lesson =
      (type.value as ScheduleType) === 'main'
        ? newChanges.value?.lessons.find(
            (lesson: Lesson) => lesson.index === item.index
          )
        : item;

    try {
      await updateLesson({
        lesson: updatingLesson,
      });
    } catch (e: any) {
      showError(e?.response?.data.message);
      return;
    }
  }

  const { mutateAsync: destroyLesson } = useDestroyLesson();
  async function removeLesson(lesson: Lesson) {
    if (type.value === 'main') {
      try {
        let lessonsForChanges = lessons.value?.filter(
          (l: any) => l.id !== lesson.id
        );
        await createScheduleWithChanges({
          body: {
            group_id: props.group.id,
            lessons: lessonsForChanges,
            type: 'changes',
            date: props.date,
            semester_id: props.semester.id,
            week_type: null,
          },
        });
        await invalidateSchedule();
        // queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
      } catch (e: any) {
        showError(e?.response.data.message);
        return;
      }
    } else {
      try {
        await destroyLesson({ lesson: lesson });
        if (schedule.value.lessons.length === 0) {
          await invalidateSchedule();
          //   queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
        }
      } catch (e: any) {
        showError(e?.response.data.message);
        return;
      }
    }
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
      showError(e?.response.data.message);
      return;
    }
  }

  // const hideAddNewLesson = ref(false);

  const newLessonMessageState = ref(false);
  function handlenewLessonMessage() {
    newLessonMessageState.value = !newLessonMessageState.value;
    if (newLessonMessageState.value) {
      newLesson = reactive<newLessonType>({
        index: newLesson.index,
        subject: null,
        teachers: [],
        building: null,
        cabinet: null,
      });
    }
  }

  const now = useNow();

  const parseDate = (dateStr: any) => {
    const [day, month, year] = dateStr.split('.').map(Number);
    return new Date(year, month - 1, day);
  };

  const isOneDayDifference = (inputDate: any) => {
    const parsedDate: any = parseDate(inputDate);
    const today: any = new Date(
      now.value.getFullYear(),
      now.value.getMonth(),
      now.value.getDate()
    );
    const differenceInMs = today - parsedDate;
    const differenceInDays = differenceInMs / (1000 * 60 * 60 * 24);
    return differenceInDays > 1 && parsedDate < today;
  };

  const enabledEdit = useStorage('enableEdit', false);

  const { data: subjects } = useSubjectsQuery({ teachers: true });

  const { data: teachers } = useTeachersQuery({ subjects: true });

  const subject = toRef(() => newLesson?.subject);

  const { data: teachersFromSubjectMain, isSuccess } =
    useTeachersFromSubjectQuery(subject, group, date);

  watchEffect(() => {
    if (isSuccess.value) {
      newLesson.teachers = teachersFromSubjectMain.value;
    }
  });
  const searchTeacher = ref('');

  function filterTeachers(e: SelectFilterEvent) {
    searchTeacher.value = e.value;
  }
  const teachersFromSubject = computed(() => {
    const subjectTeachers = newLesson.subject?.teachers || [];
    let allTeachers = [];

    if (searchTeacher.value) {
      allTeachers = [
        ...subjectTeachers,
        ...(teachers.value || []),
        ...(newLesson.teachers || []),
      ];
    } else if (subjectTeachers?.length === 0) {
      allTeachers = [...(teachers.value || []), ...(newLesson.teachers || [])];
    } else {
      allTeachers = [...subjectTeachers, ...(newLesson.teachers || [])];
    }

    const uniqueTeachers = allTeachers.filter(
      (teacher, index, self) =>
        index === self.findIndex(t => t.id === teacher.id)
    );

    return uniqueTeachers;
  });

  const scheduleTypes = {
    main: 'Основное',
    changes: 'Изменения',
  };
</script>

<template>
  <BlockUI :blocked="isOneDayDifference(dateRef) && !Boolean(enabledEdit)">
    <div class="schedule-item">
      <div
        class="flex items-center justify-between rounded-t bg-surface-0 p-2 dark:bg-surface-950"
      >
        <div class="flex items-center gap-2">
          <span
            class="text-left text-xl font-medium text-surface-800 dark:text-white/80"
          >
            {{ props?.group?.name }}
          </span>

          <Button
            :disabled="isOneDayDifference(dateRef) && !Boolean(enabledEdit)"
            severity="secondary"
            text
            icon="pi pi-pen-to-square"
            :class="{ '!text-indigo-500': isEdit }"
            :title="
              isOneDayDifference(dateRef) && !Boolean(enabledEdit)
                ? 'Редактирование для прошедших дней отключено'
                : 'Редактировать'
            "
            @click="isEdit = !isEdit"
          />
          <ToggleSwitch
            v-if="type === 'changes'"
            v-model="published"
            :disabled="
              !lessons || (isOneDayDifference(dateRef) && !Boolean(enabledEdit))
            "
            :title="published ? 'Снять с публикации' : 'Опубликовать'"
            @change="handlePublished"
          />
        </div>

        <span
          v-if="type"
          :class="{
            'text-green-400': type === 'changes',
            'text-surface-400': type === 'main',
          }"
          class="rounded-lg px-2 py-1 text-right text-sm"
          >{{ scheduleTypes[type] }}</span
        >
      </div>
      <table class="schedule-table rounded bg-surface-50 dark:bg-surface-900">
        <thead>
          <tr>
            <th>
              <!-- <div class="">№</div> -->
            </th>
            <th>
              <!-- <div class="">Предмет / Преподаватели</div> -->
            </th>

            <th>
              <!-- <div class="">Корпус</div> -->
            </th>
            <!-- <th> -->
            <!-- <div class="">Кабинет</div> -->
            <!-- </th> -->
            <th v-if="isEdit">
              <!-- <div class="">Действия</div> -->
            </th>
          </tr>
        </thead>
        <tbody>
          <template v-if="isEdit">
            <template v-for="lesson in lessons" :key="lesson.id">
              <AdminChangesScheduleItemRow
                :subjects="subjects || []"
                :teachers="teachers || []"
                :lesson="lesson"
                :disabled="disabled"
                @remove-lesson="removeLesson"
                @edit-lesson="editLesson"
              />
            </template>
          </template>
          <template v-else>
            <template v-for="lesson in lessons" :key="lesson.id">
              <AdminChangesScheduleItemRowPreview
                :is-edit="isEdit"
                :lesson="lesson"
              />
            </template>
          </template>
          <tr v-if="isEdit" class="border-t-4 border-surface-600">
            <td>
              <InputNumber
                v-model.trim="newLesson.index"
                input-id="integeronly"
                input-class="w-full text-center"
                placeholder="№"
                :min="0"
                :max="10"
                size="small"
              />
              <!-- <InputText
                v-model="newLesson.index"
                v-keyfilter="/^\d+$/"
                size="small"
                class="w-full text-center"
                placeholder="№"
              /> -->
            </td>

            <td v-show="newLessonMessageState" colspan="2/1">
              <div class="table-subrow">
                <Textarea
                  v-model="newLesson.message"
                  placeholder="Введите сообщение для группы"
                  class="w-full"
                />
              </div>
            </td>

            <td v-show="!newLessonMessageState">
              <div class="table-subrow flex flex-col">
                <CustomSelect
                  v-model="newLesson.subject"
                  data-key="name"
                  editable
                  placeholder="Предмет"
                  class="w-full text-left"
                  :options="subjects"
                  option-label="name"
                ></CustomSelect>
                <!-- <Select
                v-model="newLesson.subject"
                data-key="name"
                editable
                placeholder="Предмет"
                class="w-full text-left"
                :options="subjects"
                option-label="name"
              /> -->
              </div>
              <div class="table-subrow">
                <MultiSelect
                  v-model.trim="newLesson.teachers"
                  data-key="name"
                  :reset-filter-on-hide="true"
                  :auto-filter-focus="true"
                  filter
                  placeholder="Преподаватели"
                  class="w-full"
                  :options="teachersFromSubject"
                  option-label="name"
                  @filter="filterTeachers"
                >
                  <template #footer>
                    <div class="px-3 py-2">Для остальных начните поиск</div>
                  </template>
                </MultiSelect>
              </div>
            </td>

            <td v-show="!newLessonMessageState">
              <div class="table-subrow">
                <InputText
                  v-model.trim="newLesson.cabinet"
                  size="small"
                  class="w-full text-center"
                  placeholder="Кабинет"
                />
              </div>
              <div class="table-subrow">
                <InputText
                  v-model.trim="newLesson.building"
                  size="small"
                  class="w-full text-center"
                  placeholder="Корпус"
                />
              </div>
            </td>

            <!-- <td v-show="!newLessonMessageState"></td> -->

            <td>
              <div class="table-subrow">
                <Button
                  :disabled="
                    (!newLessonMessageState &&
                      (newLesson.index === null ||
                        !newLesson.subject ||
                        typeof newLesson.subject === 'string')) ||
                    (newLessonMessageState && !newLesson.message) ||
                    disabled
                  "
                  icon="pi pi-save"
                  @click="addNewLesson()"
                />
                <Button
                  :title="`${newLessonMessageState ? 'Переключиться на обычную пару' : 'Переключиться на комментарий'}`"
                  text
                  :icon="`pi ${newLessonMessageState ? 'pi-table' : 'pi-comment'}`"
                  @click="handlenewLessonMessage"
                />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </BlockUI>
</template>

<style scoped>
  .schedule-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    /* font-size: 0.8rem; */
    /* Чтобы все столбцы имели фиксированную ширину */
  }

  .schedule-table th,
  .schedule-table td {
    /* border: 1px solid var(--p-surface-600);*/
    /* padding: 5px; */
    text-align: center;
  }

  /* Подстроки в ячейках */
  .table-subrow {
    display: flex;
    align-items: center;
    justify-content: center;
    /* padding: 5px; */
  }

  .schedule-table th:first-child,
  .schedule-table td:first-child {
    width: 5%;
  }

  .schedule-table th:nth-child(2),
  .schedule-table td:nth-child(2) {
    width: 40%;
  }

  .schedule-table th:nth-child(3),
  .schedule-table td:nth-child(3) {
    width: 15%;
  }

  .schedule-table th:nth-child(4),
  .schedule-table td:nth-child(4) {
    width: 10%;
  }

  .schedule-table th:nth-child(5),
  .schedule-table td:nth-child(5) {
    width: 20%;
  }

  tbody tr {
    border-bottom: 1px rgb(var(--p-surface-500)) solid;
    /* background: rgba(128, 128, 128, 0.243); */
  }

  tbody tr:last-child {
    border-bottom: none;
  }
</style>
