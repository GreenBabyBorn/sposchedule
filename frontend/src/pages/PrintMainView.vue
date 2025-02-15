<script setup lang="ts">
  import {
    useCoursesQuery,
    usePrintMainSchedulesQuery,
  } from '@/queries/schedules';
  import { computed, ref, watch, watchEffect } from 'vue';
  import { useRoute } from 'vue-router';
  import { useAuthStore } from '@/stores/auth';
  import { storeToRefs } from 'pinia';
  import { useSemesterShowQuery, useSemestersQuery } from '@/queries/semesters';
  import { useBuildingsQuery } from '@/queries/buildings';
  import MultiSelect from 'primevue/multiselect';
  import Select from 'primevue/select';
  import Button from 'primevue/button';
  import router from '@/router';
  import LoadingBar from '@/components/LoadingBar.vue';
  import type { Semester } from '@/components/schedule/types';

  const route = useRoute();
  const {
    buildings: queryBuildings,
    semester: querySemester,
    course: queryCourse,
  } = route.query as Partial<{
    buildings: string;
    semester: string;
    course: string;
  }>;

  const selectedSemester = ref<Semester | null>(null);
  const { data: semesters, isFetched: semestersFetched } = useSemestersQuery();

  const course = ref<number | null>(null);
  const { data: courses } = useCoursesQuery();

  const coursesWithLabel = computed(() => {
    return (
      courses.value?.map(course => ({
        label: `${course.course} курс`,
        value: course.course,
      })) || []
    );
  });

  const { data: buildingsData, isFetched: buildingsFetched } =
    useBuildingsQuery();
  const selectedBuildings = ref<{ value: string; label: string }[] | null>(
    null
  );
  const buildings = computed(() => {
    return (
      buildingsData.value?.map(building => ({
        value: building.name,
        label: `${building.name} корпус`,
      })) || []
    );
  });

  const semesterId = computed(() => {
    return selectedSemester.value?.id;
  });

  const buildingsArray = computed(() => {
    return selectedBuildings.value?.map(obj => obj.value) || [];
  });

  const { data: mainSchedules, isSuccess } = usePrintMainSchedulesQuery(
    semesterId,
    course,
    buildingsArray
  );

  const dayNamesWithPreposition = {
    ПН: 'понедельник',
    ВТ: 'вторник',
    СР: 'среда',
    ЧТ: 'четверг',
    ПТ: 'пятница',
    СБ: 'суббота',
    ВС: 'воскресенье',
  };

  const authStore = useAuthStore();
  const { isAuth } = storeToRefs(authStore);

  const daysOfWeek = ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'] as const;
  type DayOfWeek = (typeof daysOfWeek)[number];

  const getIndexesFromWeekdays = computed(() => {
    const daysIndexes: Record<DayOfWeek, Set<number>> = {
      ПН: new Set(),
      ВТ: new Set(),
      СР: new Set(),
      ЧТ: new Set(),
      ПТ: new Set(),
      СБ: new Set(),
    };

    // eslint-disable-next-line no-unsafe-optional-chaining
    for (const item of mainSchedules?.value) {
      for (const day of daysOfWeek) {
        for (const index of item.schedule[day]) {
          daysIndexes[day].add(index.index);
        }
      }
    }
    const result: Record<DayOfWeek, number[]> = {} as Record<
      DayOfWeek,
      number[]
    >;
    for (const day in daysIndexes) {
      result[day as DayOfWeek] = Array.from(daysIndexes[day as DayOfWeek]).sort(
        (a: number, b: number) => a - b
      );
    }

    return result;
  });

  const { data: semester } = useSemesterShowQuery(semesterId);

  function printPage() {
    window.print();
  }

  function updateQueryParams() {
    router.replace({
      query: {
        ...route.query,
        semester: semesterId.value || undefined,
        buildings: buildingsArray.value || undefined,
        course: course.value || undefined,
      },
    });
  }

  watch(
    [semesterId, course, selectedBuildings],
    () => {
      updateQueryParams();
    },
    { deep: true }
  );

  watchEffect(() => {
    if (semestersFetched.value && buildingsFetched.value) {
      if (querySemester) {
        selectedSemester.value =
          semesters.value?.find(item => item.id === Number(querySemester)) ||
          null;
      }

      if (queryBuildings) {
        const buildingNames = queryBuildings.toString();
        selectedBuildings.value = buildings.value?.filter(building =>
          buildingNames.includes(building.value)
        );
      }

      if (queryCourse) {
        course.value = Number(queryCourse);
      }
    }
  });
</script>

<template>
  <LoadingBar />
  <div class="controls flex flex-wrap items-center gap-2 py-2 pl-2">
    <Select
      v-model="selectedSemester"
      show-clear
      :options="semesters"
      placeholder="Семестры"
      option-label="name"
      class=""
    />
    <MultiSelect
      v-model="selectedBuildings"
      :max-selected-labels="2"
      :selected-items-label="'{0} выбрано'"
      :options="buildings"
      placeholder="Корпуса"
      option-label="label"
    />
    <Select
      v-model="course"
      show-clear
      :options="coursesWithLabel"
      option-label="label"
      option-value="value"
      placeholder="Курс"
    />
    <Button
      label="Печать"
      :disabled="
        !course || !selectedBuildings || !selectedSemester || !isSuccess
      "
      icon="pi pi-print"
      @click="printPage()"
    />
  </div>
  <div v-if="mainSchedules" class="main">
    <div class="top">
      <div class="flex justify-end">
        <div :contenteditable="isAuth" class="text-right">
          УТВЕРЖДАЮ <br />
          директор <br />
          _________ Клочков А.Ю.
        </div>
      </div>

      <div class="info">
        <h1 :contenteditable="isAuth" class="text-sm">
          Расписание учебных занятий на {{ semester?.index }} cеместр
          {{ semester?.years }} учебного года
        </h1>
        <h2 :contenteditable="isAuth" class="text-xs">
          {{ course }} курс Учебный корпус №{{ buildingsArray?.toString() }}
        </h2>
      </div>
    </div>

    <div>
      <div v-for="weekDay in daysOfWeek" :key="weekDay">
        <table :width="mainSchedules?.length * 150 + 'px'">
          <tr v-if="weekDay === 'ПН'">
            <template
              v-for="group_schedule in mainSchedules"
              :key="group_schedule?.group.name"
            >
              <th width="10px" class="bg-yellow-300" />
              <th width="" colspan="3" class="bg-red-100">
                <div v-if="weekDay === 'ПН'" class="group-name">
                  {{ group_schedule?.group?.name }}
                </div>
              </th>
            </template>
          </tr>
        </table>
        <table
          :width="mainSchedules?.length * 150 + 'px'"
          :class="{ 'border-t-8 border-blue-400': weekDay !== 'ПН' }"
          class="border-collapse"
        >
          <thead>
            <tr>
              <template
                v-for="group_schedule in mainSchedules"
                :key="group_schedule?.group.name"
              >
                <th width="10px" class="bg-yellow-300">
                  <div
                    v-if="weekDay === 'ПН'"
                    style="
                      display: flex;
                      justify-content: center;
                      align-items: center;
                      padding: 5px 0px;
                    "
                  >
                    <div
                      style="
                        writing-mode: vertical-lr;
                        transform: rotate(180deg);
                        font-size: 5px;
                      "
                    >
                      недели
                      <br />
                      день
                    </div>
                  </div>
                </th>
                <th width="12px">
                  <div
                    v-if="weekDay === 'ПН'"
                    style="
                      display: flex;
                      justify-content: center;
                      align-items: center;
                      padding: 5px 0px;
                    "
                  >
                    <div
                      style="
                        writing-mode: vertical-lr;
                        transform: rotate(180deg);
                        font-size: 5px;
                      "
                    >
                      пары
                      <br />
                      номер
                    </div>
                  </div>
                </th>
                <th width="100px">
                  <div
                    v-if="weekDay === 'ПН'"
                    style="font-size: 6px; line-height: 150%"
                  >
                    Предмет, преподаватель <br />
                    числитель /знаменатель
                  </div>
                </th>
                <th>
                  <div
                    v-if="weekDay === 'ПН'"
                    style="
                      display: flex;
                      justify-content: center;
                      align-items: center;
                      padding: 5px 0px;
                    "
                  >
                    <div
                      style="
                        writing-mode: vertical-lr;
                        transform: rotate(180deg);
                        font-size: 5px;
                      "
                    >
                      кабинет
                    </div>
                  </div>
                </th>
              </template>
            </tr>
          </thead>
          <tbody v-if="mainSchedules">
            <tr v-for="index in getIndexesFromWeekdays?.[weekDay]" :key="index">
              <template
                v-for="group_schedule in mainSchedules"
                :key="group_schedule?.group.name"
              >
                <td
                  v-if="getIndexesFromWeekdays?.[weekDay][0] === index"
                  class="bg-yellow-300 font-bold"
                  :rowspan="getIndexesFromWeekdays?.[weekDay]?.length"
                >
                  <div
                    style="
                      display: flex;
                      justify-content: center;
                      align-items: center;
                      padding: 5px 0px;
                    "
                  >
                    <div
                      style="
                        writing-mode: vertical-lr;
                        transform: rotate(180deg);
                        font-size: 5px;
                      "
                    >
                      {{ dayNamesWithPreposition[weekDay] }}
                    </div>
                  </div>
                </td>
                <td class="index text-center" style="font-size: 5px">
                  {{ index }}
                </td>
                <td>
                  <template
                    v-for="lesson in group_schedule?.schedule?.[weekDay]"
                    :key="lesson?.index"
                  >
                    <div class="" />
                    <template v-if="lesson?.lesson?.index === index">
                      <div class="subject-name">
                        {{ lesson?.lesson?.subject?.name }}
                      </div>

                      <div class="teacher">
                        <span
                          v-for="teacher in lesson?.lesson?.teachers"
                          :key="teacher.name"
                        >
                          {{ teacher.name }}
                        </span>
                      </div>
                    </template>
                    <template
                      v-if="
                        lesson?.['ЗНАМ']?.index === index ||
                        lesson?.['ЧИСЛ']?.index === index
                      "
                    >
                      <div class="">
                        <div class="subject-name">
                          {{ lesson?.['ЧИСЛ']?.subject?.name }} /
                        </div>
                      </div>

                      <div class="">
                        <div class="subject-name">
                          {{ lesson?.['ЗНАМ']?.subject?.name }}
                        </div>
                      </div>
                    </template>
                  </template>
                </td>
                <td>
                  <template
                    v-for="lesson in group_schedule?.schedule?.[weekDay]"
                    :key="lesson?.index"
                  >
                    <template v-if="lesson?.lesson?.index === index">
                      <div class="cabinet">
                        {{ lesson?.lesson?.cabinet }}
                      </div>
                    </template>
                    <template
                      v-if="
                        lesson?.['ЗНАМ']?.index === index ||
                        lesson?.['ЧИСЛ']?.index === index
                      "
                    >
                      <div class="">
                        <div class="cabinet">
                          {{ lesson?.['ЧИСЛ']?.cabinet }} /
                        </div>
                      </div>

                      <div class="">
                        <div class="cabinet">
                          {{ lesson?.['ЗНАМ']?.cabinet }}
                        </div>
                      </div>
                    </template>
                  </template>
                </td>
              </template>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
  @media print {
    .controls {
      display: none;
    }

    .main {
      overflow: visible !important;
      /* Убираем возможные разрывы страниц и переносы */
      /* page-break-inside: avoid; */
    }
  }

  .group-header {
    width: 150px;
  }

  .bg-line {
    height: 1rem;
    background: rgba(45, 116, 209, 0.582);
  }

  .main {
    font-family: 'Arial', Times, serif;
    padding: 1rem;
    overflow: auto;
  }

  table {
    table-layout: fixed;
    border-collapse: collapse;
    /* width: 100%; */
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

  .info * {
    line-height: normal;
    /* font-size: 2rem; */
    text-align: center;
    font-weight: bold;
  }

  .info {
    margin-bottom: 1rem;
  }

  .group-name {
    font-size: 10px;
  }

  .subject-name {
    text-align: center;
    text-transform: uppercase;
    font-size: 6px;

    padding-top: 5px;
  }

  .teacher {
    text-align: center;

    font-size: 6px;
    padding-bottom: 5px;
  }

  .cabinet {
    text-align: center;
    font-size: 6px;
    font-weight: bold;
  }
</style>
