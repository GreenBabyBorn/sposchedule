<script setup lang="ts">
  import { toRef } from 'vue';

  const props = defineProps({
    groupName: { required: true, type: [String, null], default: null },
    date: { required: false },
    weekType: { required: false },
    type: { required: true },
    semester: { required: false, type: [Object, null] }, // разрешаем Object или null
    lessons: { required: true },
    schedule: { required: true, type: [Object, null] }, // разрешаем Object или null
    published: { required: false, type: Boolean },
  });
  const lessons: any = toRef<any>(() => props.lessons);
</script>

<template>
  <article class="schedule-item">
    <div class="mb-2 flex flex-wrap justify-between items-center">
      <h2
        class="text-xl text-left font-medium text-surface-800 dark:text-white/80"
      >
        {{ props.groupName }}
      </h2>
      <span>{{ props.weekType }}</span>
      <span
        :class="{
          'text-green-400 ': props.type !== 'main',
          'text-surface-400 ': props.type === 'main',
        }"
        class="text-sm text-right py-1 px-2 rounded-lg"
        >{{ props.type === 'main' ? 'Основное' : 'Изменения' }}</span
      >
    </div>
    <table
      v-if="lessons"
      class="schedule-table dark:bg-surface-900 bg-surface-50 rounded"
    >
      <tbody>
        <template v-for="item in lessons" :key="item.index">
          <tr>
            <td class="p-2">
              <span class="text-surface-800 dark:text-white/80">
                {{ item.index
                }}<span
                  v-if="item.weekType === 'ЗНАМ' || item.weekType === 'ЧИСЛ'"
                  title="Дробная пара"
                  class="absolute text-sm"
                  >{{
                    item.weekType === 'ЗНАМ' || item.weekType === 'ЧИСЛ'
                      ? '*'
                      : ''
                  }}</span
                >
              </span>
            </td>
            <td v-if="item.message" class="p-1" colspan="3/1">
              <div class="table-subrow">
                <p>{{ item.message }}</p>
              </div>
            </td>
            <td v-if="!item.message" class="pl-0 p-1">
              <div
                v-if="item.id"
                :class="{
                  'border-b border-surface-200 dark:border-surface-700':
                    item.teachers?.length,
                }"
                class="flex justify-between gap-1 items-center"
              >
                <span v-if="item.subject_name" class="text-sm text-left">{{
                  item.subject_name
                }}</span>
                <div v-else class="">
                  <span class="text-sm text-left text-red-400"
                    >Предмет был удален</span
                  >
                </div>
                <span>{{ item.cabinet }}</span>
              </div>

              <div
                v-if="item.id"
                class="dark:text-surface-500 flex justify-between items-center"
              >
                <div v-if="item.id" class="flex flex-wrap gap-1 justify-start">
                  <span
                    v-for="teacher in item.teachers"
                    class="dark:text-surface-500 text-sm"
                    >{{ teacher.name }}</span
                  >
                </div>
                <span v-if="item.building" class="text-sm"
                  >{{ item.building }} корпус</span
                >
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
    <div v-else class="flex justify-center items-center">
      <span class="text-xl"> Расписание не найдено </span>
    </div>
  </article>
</template>

<style scoped>
  .schedule-item {
    width: 100%;
  }

  .schedule-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    /* font-size: 0.8rem; */
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
    width: 5%;
  }

  .schedule-table th:nth-child(2),
  .schedule-table td:nth-child(2) {
    width: 97%;
  }

  /* .schedule-table th:nth-child(3),
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
} */

  tbody tr {
    border-bottom: 1px var(--p-surface-500) solid;
    /* background: rgba(128, 128, 128, 0.243); */
  }

  tbody tr:last-child {
    border-bottom: none;
  }
</style>
