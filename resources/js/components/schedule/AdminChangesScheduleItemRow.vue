<script setup lang="ts">
  import { toRef } from 'vue';
  import MultiSelect from 'primevue/multiselect';

  import Select from 'primevue/select';
  // import Select from '../ui/Select.vue';

  import InputText from 'primevue/inputtext';
  import Textarea from 'primevue/textarea';
  import Button from 'primevue/button';

  const props = defineProps<{
    lesson: any;
    teachers: any;
    subjects: any;
    isEdit: boolean;
  }>();

  const teachers: any = toRef<any>(() => props.teachers);
  const subjects: any = toRef<any>(() => props.subjects);
  const isEdit: any = toRef<any>(() => props.isEdit);

  const lesson = toRef(() => props.lesson);

  const emit = defineEmits<{
    (e: 'removeLesson', id: number): void;
    (e: 'editLesson', lesson: any): void;
  }>();

  const removeLesson = (id: number) => {
    emit('removeLesson', id);
  };

  const editLesson = (lesson: any) => {
    emit('editLesson', lesson);
  };
</script>
<template>
  <tr v-if="lesson?.index >= 0">
    <td>
      <span class="text-xl font-medium text-surface-800 dark:text-white/80">
        {{ lesson?.index }}
      </span>
    </td>
    <td v-show="lesson?.message" colspan="3/1">
      <div class="table-subrow">
        <span v-if="!isEdit">{{ lesson.message }}</span>
        <Textarea
          v-else
          v-model="lesson.message"
          placeholder="Введите сообщение для группы"
          class="w-full"
          @change="editLesson(lesson)"
        />
      </div>
    </td>
    <td v-if="!lesson?.message">
      <div v-if="lesson?.subject" class="table-subrow">
        <span v-if="!isEdit">{{ lesson.subject.name }}</span>
        <Select
          v-else
          v-model="lesson.subject"
          data-key="name"
          filter
          class="w-full text-left"
          :options="subjects"
          option-label="name"
          @change="editLesson(lesson)"
        />
      </div>
      <div v-else class="text-red-400">Предмет не найден</div>
      <div v-if="lesson.teachers" class="table-subrow">
        <div v-if="!isEdit" class="">
          <span
            v-for="teacher in lesson.teachers"
            :key="teacher.name"
            class="opacity-50"
            >{{ teacher.name + ' ' }}</span
          >
        </div>
        <MultiSelect
          v-else
          v-model="lesson.teachers"
          data-key="name"
          filter
          placeholder="Выберите преподавателя"
          :options="teachers"
          class="w-full"
          option-label="name"
          @change="editLesson(lesson)"
        />
      </div>
    </td>
    <td v-show="!lesson.message">
      <div v-if="lesson.id" class="table-subrow">
        <span v-if="!isEdit">{{ lesson.building }}</span>
        <InputText
          v-else
          v-model="lesson.building"
          class="w-full"
          @change="editLesson(lesson)"
        />
      </div>
    </td>
    <td v-show="!lesson.message">
      <div v-if="lesson.id" class="table-subrow">
        <span v-if="!isEdit">{{ lesson.cabinet }}</span>
        <InputText
          v-else
          v-model="lesson.cabinet"
          class="w-full"
          @change="editLesson(lesson)"
        />
      </div>
    </td>
    <td v-if="isEdit">
      <div class="table-subrow">
        <!-- <Button text :disabled="!lesson?.cabinet || !lesson?.building || !lesson?.subject" icon="pi pi-check"
                    v-if="!lesson?.id && isEdit" /> -->

        <Button
          v-if="lesson?.id && isEdit"
          text
          icon="pi pi-trash"
          severity="danger"
          @click="removeLesson(lesson?.id)"
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
