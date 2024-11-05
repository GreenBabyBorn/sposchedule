<script setup lang="ts">
  import { usePrintChangesSchedulesQuery } from '@/queries/schedules';
  import { useDateFormat } from '@vueuse/core';
  import { computed, onMounted, ref, watch } from 'vue';
  import { useRoute } from 'vue-router';
  import { useAuthStore } from '@/stores/auth';
  import Button from 'primevue/button';
  import { storeToRefs } from 'pinia';
  import LoadingBar from '@/components/LoadingBar.vue';
  import DatePicker from 'primevue/datepicker';
  import router from '@/router';
  import {
    monthDeclensions,
    dayNamesWithPreposition,
  } from '@/composables/constants';

  const route = useRoute();
  const date = ref(null);

  onMounted(() => {
    const dateQuery = route.query?.date as string;

    if (dateQuery) {
      const [day, month, year] = dateQuery.split('.').map(Number);

      // Проверяем, что все значения (day, month, year) действительно числа
      if (day && month && year) {
        date.value = new Date(year, month - 1, day);
      } else {
        date.value = new Date(); // Если дата не валидна, устанавливаем текущую дату
      }
    } else {
      date.value = new Date(); // Если query параметр отсутствует, устанавливаем текущую дату
    }
  });

  const formattedDate = computed(() => {
    if (date.value) return useDateFormat(date.value, 'DD.MM.YYYY').value;
    return null;
  });

  const { data: changesSchedules, isSuccess } =
    usePrintChangesSchedulesQuery(formattedDate);

  const blocks1_5 = computed(() => {
    const chunkSize = 4; // Размер подмассива
    const result = [];

    for (
      let i = 0;
      i < changesSchedules?.value?.['1-5'].schedules.length;
      i += chunkSize
    ) {
      const chunk = changesSchedules?.value?.['1-5'].schedules.slice(
        i,
        i + chunkSize
      );

      // Добавление пустых объектов, если подмассив меньше чем 4 элемента
      while (chunk.length < chunkSize) {
        chunk.push({}); // или chunk.push(null); в зависимости от того, что должно быть в пустых местах
      }

      result.push(chunk);
    }

    return result;
  });

  const blocks6 = computed(() => {
    const chunkSize = 4; // Размер подмассива
    const result = [];

    for (
      let i = 0;
      i < changesSchedules?.value?.['6'].schedules.length;
      i += chunkSize
    ) {
      const chunk = changesSchedules?.value?.['6'].schedules.slice(
        i,
        i + chunkSize
      );

      // Добавление пустых объектов, если подмассив меньше чем 4 элемента
      while (chunk.length < chunkSize) {
        chunk.push({}); // или chunk.push(null); в зависимости от того, что должно быть в пустых местах
      }

      result.push(chunk);
    }

    return result;
  });

  const authStore = useAuthStore();
  const { user, isAuth } = storeToRefs(authStore);

  function printPage() {
    window.print();
  }

  const updateQueryParams = () => {
    router.replace({
      query: {
        ...route.query,
        date: formattedDate.value || undefined,
      },
    });
  };

  watch(
    [formattedDate],
    () => {
      updateQueryParams();
    },
    { deep: true }
  );
</script>

<template>
  <LoadingBar />
  <div class="controls flex flex-wrap items-center gap-2 py-2 pl-2">
    <DatePicker
      v-model="date"
      append-to="self"
      show-icon
      icon-display="input"
      date-format="dd.mm.yy"
      select-other-months
    >
    </DatePicker>
    <Button label="Печать" icon="pi pi-print" @click="printPage()" />
  </div>
  <div v-if="changesSchedules?.['1-5']" class="main">
    <div class="top">
      <div class="flex justify-between">
        <div>
          <span contenteditable class="underline"
            >Исполнитель: {{ user?.name }}</span
          >
        </div>
        <div :contenteditable="isAuth" class="text-right">
          СОГЛАСОВАНО <br />
          Зам. директора по УМР <br />
          _________ О.А. Толубаева
        </div>
      </div>

      <div class="info">
        <h1>ИЗМЕНЕНИЯ В РАСПИСАНИИ ЗАНЯТИЙ (1-5 корпус)</h1>
        <h2 class="uppercase italic">
          НА
          {{
            dayNamesWithPreposition[
              useDateFormat(date, 'dddd', {
                locales: 'ru-RU',
              }).value
            ]
          }}
          {{
            `${
              useDateFormat(date, 'DD', {
                locales: 'ru-RU',
              }).value
            } ${
              monthDeclensions[
                useDateFormat(date, 'MMMM', {
                  locales: 'ru-RU',
                }).value
              ]
            } ${
              useDateFormat(date, 'YYYY', {
                locales: 'ru-RU',
              }).value
            }`
          }}
          года ({{
            changesSchedules?.['1-5']?.week_type === 'ЗНАМ'
              ? 'знаменатель'
              : 'числитель'
          }})
        </h2>
      </div>
    </div>
    <div
      v-for="(block, index) in blocks1_5"
      :key="block[0]?.group?.name"
      :class="{ 'page-break': (index + 1) % 2 === 0 }"
      class="groups-row"
    >
      <div class="bg-line" />
      <table class="w-full border-collapse">
        <thead>
          <tr>
            <th />
            <th
              v-for="(group, groupIndex) in block"
              :key="groupIndex"
              :colspan="groupIndex == 0 ? 2 : 2"
              class="group-header"
            >
              <div class="group-name">
                {{ group?.group?.name }}
              </div>
            </th>
            <th />
          </tr>
        </thead>
        <tbody>
          <tr v-for="index in [0, 1, 2, 3, 4, 5, 6, 7]" :key="index">
            <template
              v-for="(group, groupIndex) in block"
              :key="`row-${index}-group-${groupIndex}`"
            >
              <td
                v-if="groupIndex == 0 || !groupIndex"
                class="index text-center"
                width="10px"
              >
                {{ index }}
              </td>

              <td class="subject-name">
                <template
                  v-for="lesson in group?.schedule?.lessons"
                  :key="lesson?.index"
                >
                  <template v-if="lesson?.index === index">
                    <span>
                      {{ lesson?.subject?.name }}
                    </span>
                    <span class="font-bold">
                      {{ lesson?.message }}
                    </span>
                  </template>
                </template>
              </td>
              <td class="cabinet text-center">
                <template
                  v-for="lesson in group?.schedule?.lessons"
                  :key="lesson.index"
                >
                  <template v-if="lesson?.index === index">
                    <span v-if="lesson?.cabinet?.includes('/')">
                      {{ lesson?.cabinet.split('/')[0] }}/<br />{{
                        lesson?.cabinet.split('/')[1]
                      }}
                    </span>
                    <span v-else>{{ lesson?.cabinet }}</span>
                  </template>
                </template>
              </td>
              <td v-if="groupIndex == 3" class="index text-center" width="10px">
                {{ index }}
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div v-if="changesSchedules?.['6']" class="main">
    <div class="top">
      <div class="flex justify-between">
        <div>
          <span contenteditable class="underline"
            >Исполнитель: {{ user?.name }}</span
          >
        </div>
        <div contenteditable class="text-right">
          СОГЛАСОВАНО <br />
          Зам. директора по УМР <br />
          _________ О.А. Толубаева
        </div>
      </div>

      <div class="info">
        <h1>ИЗМЕНЕНИЯ В РАСПИСАНИИ ЗАНЯТИЙ (6 корпус)</h1>
        <h2 class="uppercase italic">
          НА
          {{
            dayNamesWithPreposition[
              useDateFormat(date, 'dddd', {
                locales: 'ru-RU',
              }).value
            ]
          }}
          {{
            `${
              useDateFormat(date, 'DD', {
                locales: 'ru-RU',
              }).value
            } ${
              monthDeclensions[
                useDateFormat(date, 'MMMM', {
                  locales: 'ru-RU',
                }).value
              ]
            } ${
              useDateFormat(date, 'YYYY', {
                locales: 'ru-RU',
              }).value
            }`
          }}
          года ({{
            changesSchedules?.['6']?.week_type === 'ЗНАМ'
              ? 'знаменатель'
              : 'числитель'
          }})
        </h2>
      </div>
    </div>
    <div
      v-for="(block, index) in blocks6"
      :key="block[0]?.group.name"
      :class="{ 'page-break': (index + 1) % 2 === 0 }"
      class="groups-row"
    >
      <div class="bg-line" />
      <table class="w-full border-collapse">
        <thead>
          <tr>
            <th />
            <th
              v-for="(group, groupIndex) in block"
              :key="groupIndex"
              :colspan="groupIndex == 0 ? 2 : 2"
              class="group-header"
            >
              <div class="group-name">
                {{ group?.group?.name }}
              </div>
            </th>
            <th />
          </tr>
        </thead>
        <tbody>
          <tr v-for="index in [0, 1, 2, 3, 4, 5, 6, 7]" :key="index">
            <!-- Колонки расписания для каждой группы -->
            <template
              v-for="(group, groupIndex) in block"
              :key="`row-${index}-group-${groupIndex}`"
            >
              <td
                v-if="groupIndex == 0 || !groupIndex"
                class="index text-center"
                width="10px"
              >
                {{ index }}
              </td>

              <td class="subject-name">
                <template
                  v-for="lesson in group?.schedule?.lessons"
                  :key="lesson?.index"
                >
                  <template v-if="lesson?.index === index">
                    <span class="">
                      {{ lesson?.subject?.name }}
                    </span>
                    <span class="font-bold">
                      {{ lesson?.message }}
                    </span>
                  </template>
                </template>
              </td>
              <td class="cabinet text-center">
                <template
                  v-for="lesson in group?.schedule?.lessons"
                  :key="lesson?.index"
                >
                  <template v-if="lesson?.index === index">
                    <span v-if="lesson?.cabinet?.includes('/')">
                      {{ lesson?.cabinet.split('/')[0] }}/<br />{{
                        lesson?.cabinet.split('/')[1]
                      }}
                    </span>
                    <span v-else>{{ lesson?.cabinet }}</span>
                  </template>
                </template>
              </td>
              <td v-if="groupIndex == 3" class="index text-center" width="10px">
                {{ index }}
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div
    v-if="!changesSchedules?.['6'] && !changesSchedules?.['1-5'] && isSuccess"
    div
    class="p-2 text-lg"
  >
    На эту дату изменений в расписании не найдено
  </div>
</template>

<style scoped>
  @media print {
    @page {
      /* size: landscape; */
    }

    .controls {
      display: none;
    }

    .groups-row {
      page-break-inside: avoid;
      /* Не разрывать группу внутри */
      margin-bottom: 10px;
    }

    .groups-row.page-break {
      page-break-after: always;
      /* Разрывать страницу после каждого второго блока */
    }

    .main {
      page-break-after: always;
      overflow: visible !important;
    }
  }

  .bg-line {
    height: 2rem;
    width: 100%;
    background:
        /* Сверху */
      repeating-linear-gradient(45deg, #ffffff 1px, #959595 2px),
      /* Снизу */ linear-gradient(to bottom, #ffffff, #959595);
  }

  .main {
    padding: 1rem;
    overflow-x: hidden;
    font-family: 'Times New Roman', Times, serif;
    font-size: 1.2rem;
    overflow: auto;
    width: 1080px;
  }

  table {
    table-layout: auto;
    border-collapse: collapse;
  }

  tbody th {
    line-height: normal;
  }

  td {
    height: 10px;
  }

  th,
  td {
    border: 1px solid black;
    padding-right: 4px;
    padding-left: 4px;
    padding-top: 0;
    padding-bottom: 0;
    line-height: normal;

    /* padding: 5px; */
  }

  .group-header {
    width: 25%;
    /* line-height: 100%; */
  }

  .group-name {
    text-align: left;
    font-weight: 700;
    font-size: 1.2rem;
  }

  .subject-name {
    text-align: left;
  }

  .cabinet {
    max-width: 15px;
    width: 4%;
    font-size: 0.9rem;
    text-align: center;
    /* word-wrap: break-word; */
  }

  .info * {
    line-height: normal;
    font-size: 2rem;
    text-align: center;
    font-weight: bold;
  }

  .info {
    margin-bottom: 1rem;
  }
</style>
