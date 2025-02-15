<script setup lang="ts">
  import { toRef } from 'vue';
  import MultiSelect from 'primevue/multiselect';
  import Select from 'primevue/select';
  import InputText from 'primevue/inputtext';
  import Textarea from 'primevue/textarea';
  import Button from 'primevue/button';
  import type { Lesson, Subject, Teacher } from './types';
  import InputNumber from 'primevue/inputnumber';

  interface Props {
    lesson: Lesson;
    teachers: Teacher[];
    subjects: Subject[];
    disabled: boolean;
  }

  const props = defineProps<Props>();
  const initialLesson = JSON.parse(JSON.stringify(props.lesson));

  const teachers = toRef(() => props.teachers);
  const subjects = toRef(() => props.subjects);
  const disabled = toRef(() => props.disabled);
  const lesson = toRef(() => props.lesson);

  const emit = defineEmits<{
    (e: 'removeLesson', lesson: Lesson): void;
    (e: 'editLesson', lesson: Lesson): void;
  }>();

  const removeLesson = (lesson: Lesson) => {
    emit('removeLesson', lesson);
  };

  const editLesson = (lesson: Lesson) => {
    if (JSON.stringify(initialLesson) === JSON.stringify(lesson)) return;
    emit('editLesson', lesson);
  };
</script>
<template>
  <tr v-if="lesson?.index >= 0">
    <td>
      <InputNumber
        v-model.trim.number="lesson.index"
        input-id="integeronly"
        input-class="w-full text-center"
        placeholder="№"
        :min="0"
        :max="10"
        size="small"
        @value-change="editLesson(lesson)"
      />
      <!-- <InputText
        v-else
        v-model="lesson.index"
        v-keyfilter="/^\d+$/"
        class="w-full text-center font-bold"
        placeholder="№"
        size="small"
        @change="editLesson(lesson)"
      /> -->
    </td>
    <td v-show="lesson?.message" colspan="2/1">
      <div class="table-subrow">
        <Textarea
          v-model.trim="lesson.message"
          placeholder="Введите сообщение для группы"
          class="w-full"
          @change="editLesson(lesson)"
        />
      </div>
    </td>
    <td v-if="!lesson?.message">
      <div v-if="lesson?.subject" class="text-left">
        <Select
          v-model.lazy="lesson.subject"
          data-key="name"
          filter
          class="w-full text-left"
          :options="subjects"
          size="small"
          option-label="name"
          @change="editLesson(lesson)"
        />
      </div>
      <div v-else class="text-red-400">Предмет не найден</div>
      <div v-if="lesson.teachers" class="text-left">
        <MultiSelect
          v-model.trim="lesson.teachers"
          data-key="name"
          filter
          placeholder="Преподаватель"
          :options="teachers"
          class="w-full"
          option-label="name"
          size="small"
          @change="editLesson(lesson)"
        />
      </div>
    </td>
    <td v-show="!lesson.message">
      <div v-if="lesson.id" class="text-right">
        <InputText
          v-model.trim="lesson.cabinet"
          class="w-full text-center"
          size="small"
          placeholder="Кабинет"
          @blur="editLesson(lesson)"
        />
      </div>
      <div v-if="lesson.id" class="text-right">
        <InputText
          v-model.trim="lesson.building"
          class="w-full text-center"
          placeholder="Корпус"
          size="small"
          @blur="editLesson(lesson)"
        />
      </div>
    </td>
    <!-- <td v-show="!lesson.message">
      
    </td> -->
    <td>
      <div class="table-subrow">
        <!-- <Button text :disabled="!lesson?.cabinet || !lesson?.building || !lesson?.subject" icon="pi pi-check"
                    v-if="!lesson?.id && isEdit" /> -->

        <Button
          v-if="lesson?.id"
          :disabled="disabled"
          text
          icon="pi pi-trash"
          severity="danger"
          @click="removeLesson(lesson)"
        />
      </div>
    </td>
  </tr>
</template>

<style scoped>
  .schedule-item {
    /* width: 450px; */
  }

  .schedule-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 0.8rem;
    /* Чтобы все столбцы имели фиксированную ширину */
  }

  .schedule-table th,
  .schedule-table td {
    /* border: 1px solid var(--p-surface-600); */
    /* padding: 5px; */
    text-align: center;
  }

  .schedule-table th {
  }

  .schedule-table th > div {
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
    width: 10%;
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
    width: 15%;
  }

  .schedule-table th:nth-child(5),
  .schedule-table td:nth-child(5) {
    width: 20%;
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
    /* background: rgba(255, 255, 255, 0.062); */
  }
</style>
