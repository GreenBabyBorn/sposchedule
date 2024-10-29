<script setup lang="ts">
  import MultiSelect from 'primevue/multiselect';
  import Select from 'primevue/select';
  import InputText from 'primevue/inputtext';
  import Textarea from 'primevue/textarea';
  import Button from 'primevue/button';
  import { useSubjectsQuery } from '@/queries/subjects';
  import { useTeachersQuery } from '@/queries/teachers';
  import {
    useCreateScheduleWithChanges,
    // useFromMainToChangesSchedule,
    useStoreScheduleChange,
    useUpdateSchedule,
  } from '@/queries/schedules';
  import {
    useDestroyLesson,
    useStoreLesson,
    useUpdateLesson,
  } from '@/queries/lessons';
  import { useToast } from 'primevue/usetoast';
  import { computed, reactive, ref, toRef, watch } from 'vue';
  import ToggleButton from 'primevue/togglebutton';
  import AdminChangesScheduleItemRow from './AdminChangesScheduleItemRow.vue';
  import { useNow, useStorage } from '@vueuse/core';
  // import RCESelect from '../ui/RCESelect.vue';

  const toast = useToast();
  const props = defineProps({
    group: { required: false, type: [Object, null], default: null },
    date: { required: false },
    weekType: { required: false },
    type: { required: true },
    semester: { required: false, type: Object },
    lessons: { required: true },
    schedule: { required: true, type: Object },
    published: { required: false, type: Boolean },
    // teachers: { required: true },
    // subjects: { required: true }
  });
  const lessons: any = toRef<any>(() => props.lessons);
  // const teachers: any = toRef<any>(() => props.teachers)
  // const subjects: any = toRef<any>(() => props.subjects)
  const dateRef: any = toRef<any>(() => props.date);
  const semester: any = toRef<any>(() => props.semester);
  const group: any = toRef<any>(() => props.group);

  const published = ref(props.published);

  watch(
    () => props.published,
    newValue => {
      published.value = newValue;
    }
  );

  const { mutateAsync: updateLesson } = useUpdateLesson();

  const { mutateAsync: createScheduleWithChanges, data: newChanges } =
    useCreateScheduleWithChanges();
  async function editLesson(item) {
    if (!item.id) return;
    if (!item.message == !item.subject) return;

    if (props.type === 'main') {
      try {
        await createScheduleWithChanges({
          body: {
            group_id: props.group.id,
            lessons: props.schedule.lessons,
            date: props.date,
            semester_id: props.semester.id,
          },
        });
        return;
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
    try {
      await updateLesson({
        id:
          props.type === 'main'
            ? newChanges.value.data.lessons.find(x => x.index === item.index).id
            : item.id,
        body: {
          ...item,
          subject_id: item.subject?.id,
          id:
            props.type === 'main'
              ? newChanges.value.data.lessons.find(x => x.index === item.index)
                  .id
              : item.id,
          schedule_id:
            props.type === 'main'
              ? newChanges.value.data.lessons.find(x => x.index === item.index)
                  .schedule_id
              : item.schedule_id,
        },
      });
    } catch (e) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response?.data.message,
        life: 3000,
        closable: true,
      });
      return;
    }
  }

  type LessonWithWeekTypes = {
    index: number;
    subject: any | null;
    teachers: [] | null;
    building: string | null;
    cabinet: string | null;
    message?: string | null;
  };
  let newLesson = reactive<LessonWithWeekTypes>({
    index: props.schedule?.lessons?.at(-1)?.index + 1 || '',
    subject: null,
    teachers: [],
    building: props.schedule?.lessons?.at(-1)?.building,
    cabinet: null,
    message: null,
  });

  const { mutateAsync: storeSchedule, data: newSchedule } =
    useStoreScheduleChange();
  const { mutateAsync: storeLesson } = useStoreLesson();

  async function addNewLesson() {
    const loadedSchedule = toRef(() => props.schedule).value.id || false;
    // Если тип расписания 'main', конвертируем его в изменения
    if (props.schedule.type === 'main') {
      try {
        await createScheduleWithChanges({
          body: {
            group_id: props.group.id,
            lessons: props.schedule.lessons,
            date: props.date,
            semester_id: props.semester.id,
          },
        });
      } catch (e) {
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail:
            e?.response?.data?.message ||
            'Не удалось конвертировать основное расписание в изменения.',
          life: 3000,
          closable: true,
        });
        return;
      }
    }

    // Если расписание не загружено и тип 'changes', создаем новое расписание
    if (!loadedSchedule) {
      try {
        await storeSchedule({
          body: {
            group_id: group.value.id,
            semester_id: semester.value.id,
            type: 'changes',
            // view_mode: 'table',
            date: props.date,
          },
        });
      } catch (e) {
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail:
            e?.response?.data?.message || 'Не удалось сохранить расписание.',
          life: 3000,
          closable: true,
        });
        return;
      }
    }

    // Определяем правильный ID для использования в storeLesson
    let scheduleId;
    if (loadedSchedule && props.schedule.type !== 'main') {
      scheduleId = loadedSchedule;
    } else if (props.schedule.type === 'main') {
      scheduleId = newChanges.value?.data?.schedule_id; // Убедитесь, что newChanges имеет значение перед доступом к его свойствам
    } else {
      scheduleId = newSchedule.value?.data?.id; // Тоже необходимо проверить наличие значения
    }
    try {
      await storeLesson({
        body: {
          // ...newLesson,
          index: newLesson.index,
          building: newLesson.building,
          message: newLesson.message,
          cabinet: newLesson.cabinet,
          teachers: newLesson.teachers,
          subject_id: newLesson.subject?.id,
          schedule_id: scheduleId,
        },
      });
      newLesson = reactive<LessonWithWeekTypes>({
        index: Number(newLesson.index) + 1,
        subject: null,
        teachers: [],
        building: newLesson.building,
        cabinet: null,
        message: null,
      });
    } catch (e) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response?.data?.message || 'Не удалось сохранить пару.',
        life: 3000,
        closable: true,
      });
      return;
    }
  }

  const { mutateAsync: destroyLesson } = useDestroyLesson();
  async function removeLesson(id) {
    if (props.type === 'main') {
      try {
        let lesspnsForChanges = props.schedule.lessons.filter(
          (l: any) => l.id !== id
        );
        await createScheduleWithChanges({
          body: {
            group_id: props.group.id,
            lessons: lesspnsForChanges,
            date: props.date,
            semester_id: props.semester.id,
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
    } else {
      try {
        await destroyLesson({ id: id });
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
  }

  const { mutateAsync: updateChangesSchedule } = useUpdateSchedule();
  async function handlePublished() {
    try {
      await updateChangesSchedule({
        id: props.schedule.id,

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

  // const hideAddNewLesson = ref(false);

  const newLessonMessageState = ref(false);
  function handlenewLessonMessage() {
    newLessonMessageState.value = !newLessonMessageState.value;
    if (newLessonMessageState.value) {
      newLesson = reactive<LessonWithWeekTypes>({
        index: newLesson.index,
        subject: null,
        teachers: [],
        building: null,
        cabinet: null,
      });
    }
  }
  const isEdit = ref(false);

  const now = useNow();

  const parseDate = dateStr => {
    const [day, month, year] = dateStr.split('.').map(Number);
    return new Date(year, month - 1, day);
  };

  const isOneDayDifference = inputDate => {
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

  const searchTeacher = ref('');
  const queryTeacher = computed(() => {
    return { name: searchTeacher.value };
  });

  const { data: teachers } = useTeachersQuery(queryTeacher.value);
  const { data: subjects } = useSubjectsQuery({ teachers: true });

  const teachersFromSubject = computed(() => {
    const subjectTeachers = newLesson.subject?.teachers || [];
    if (searchTeacher.value) {
      return [...subjectTeachers, ...teachers.value];
    }
    return [...subjectTeachers];
  });

  function filterTeachers(e) {
    searchTeacher.value = e.value;
  }
  watch(
    () => newLesson.subject,
    () => {
      newLesson.teachers = [];
    }
  );
</script>

<template>
  <div
    :class="{
      'opacity-50': isOneDayDifference(dateRef) && !Boolean(enabledEdit),
    }"
    :title="
      isOneDayDifference(dateRef) && !Boolean(enabledEdit)
        ? 'Редактирование для прошедших дней отключено'
        : 'Редактировать'
    "
    class="schedule-item"
  >
    <div
      class="flex flex-wrap items-center justify-between rounded-t bg-surface-100 p-2 dark:bg-surface-800"
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
          :class="{ '!text-primary-500': isEdit }"
          @click="isEdit = !isEdit"
        />
      </div>

      <span>{{ props?.weekType }}</span>
      <div v-if="props.type !== 'main'">
        <ToggleButton
          v-model="published"
          :disabled="
            !lessons || (isOneDayDifference(dateRef) && !Boolean(enabledEdit))
          "
          class="text-sm"
          fluid
          on-label="Снять с публикации"
          off-label="Опубликовать"
          @change="handlePublished"
        />
      </div>
      <span
        :class="{
          'text-green-400': props?.type !== 'main',
          'text-surface-400': props?.type === 'main',
        }"
        class="rounded-lg px-2 py-1 text-right"
        >{{ props?.type === 'main' ? 'Основное' : 'Изменения' }}</span
      >
    </div>
    <table class="schedule-table rounded-b bg-surface-50 dark:bg-surface-900">
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
        <template v-for="lesson in lessons" :key="lesson.id">
          <AdminChangesScheduleItemRow
            :is-edit="isEdit"
            :subjects="subjects"
            :teachers="teachers"
            :lesson="lesson"
            @remove-lesson="removeLesson"
            @edit-lesson="editLesson"
          />
        </template>

        <tr v-if="isEdit" class="border-t-4 border-surface-600">
          <td>
            <InputText
              v-model="newLesson.index"
              v-keyfilter="/^\d+$/"
              size="small"
              class="w-full text-center"
              placeholder="№"
            />
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
              <Select
                v-model="newLesson.subject"
                filter
                data-key="name"
                editable
                placeholder="Предмет"
                class="w-full text-left"
                :options="subjects"
                option-label="name"
              />
            </div>
            <div class="table-subrow">
              <MultiSelect
                v-model="newLesson.teachers"
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
                v-model="newLesson.cabinet"
                size="small"
                class="w-full text-center"
                placeholder="Кабинет"
              />
            </div>
            <div class="table-subrow">
              <InputText
                v-model="newLesson.building"
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
                    (!newLesson.index ||
                      !newLesson.subject ||
                      typeof newLesson.subject === 'string')) ||
                  (newLessonMessageState && !newLesson.message)
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
    <!-- <div v-if="isEdit" class="mt-2 flex items-center justify-center">
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
    </div> -->
  </div>
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
