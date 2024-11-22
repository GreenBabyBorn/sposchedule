<script setup lang="ts">
  import { computed, toRef } from 'vue';
  import type { Lesson } from './types';
  import { useScheduleStore } from '@/stores/schedule';
  import { storeToRefs } from 'pinia';

  interface Props {
    lesson: Lesson;
    isEdit: boolean;
  }

  const scheduleStore = useScheduleStore();
  const { schedulesChanges } = storeToRefs(scheduleStore);

  const props = defineProps<Props>();

  const isEdit = toRef(() => props.isEdit);
  const lesson = toRef(() => props.lesson);

  const isDuplicateCabinet = computed(() => {
    for (let s of schedulesChanges.value!.schedules) {
      if (
        s.lessons?.find(
          l =>
            l.cabinet === lesson.value.cabinet &&
            l.index === lesson.value.index &&
            l.building === lesson.value.building &&
            l.schedule_id !== lesson.value.schedule_id &&
            l.cabinet !== null &&
            lesson.value.cabinet !== null &&
            l.cabinet.length > 1 &&
            lesson.value.cabinet.length > 1
        )
      ) {
        return true;
      }
    }
    return false;
  });
</script>
<template>
  <tr v-if="lesson?.index >= 0">
    <td>
      <span
        v-if="!isEdit"
        class="text-lg font-bold text-surface-800 dark:text-white/80"
      >
        {{ lesson?.index }}
      </span>
    </td>
    <td v-show="lesson?.message" colspan="2/1">
      <div class="table-subrow">
        <span v-if="!isEdit">{{ lesson.message }}</span>
      </div>
    </td>
    <td v-if="!lesson?.message">
      <div v-if="lesson?.subject" class="text-left">
        <span v-if="!isEdit">{{ lesson.subject.name }}</span>
      </div>
      <div v-else class="text-red-400">Предмет не найден</div>
      <div v-if="lesson.teachers" class="text-left">
        <div v-if="!isEdit" class="">
          <span
            v-for="teacher in lesson.teachers"
            :key="teacher.name"
            class="opacity-50"
            >{{ teacher.name + ' ' }}</span
          >
        </div>
      </div>
    </td>
    <td v-show="!lesson.message">
      <div v-if="lesson.id" class="text-right">
        <span
          v-if="!isEdit"
          :class="{ 'text-orange-400': isDuplicateCabinet }"
          :title="
            isDuplicateCabinet
              ? 'Кабинет на эту пару уже используется в другом расписании'
              : ''
          "
          class="p-1"
          >{{ lesson.cabinet }}</span
        >
      </div>
      <div v-if="lesson.id" class="text-right">
        <span v-if="!isEdit" class="p-1 opacity-50">{{
          `${lesson.building ? lesson.building + ' корпус' : ''}`
        }}</span>
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
