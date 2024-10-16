<script setup lang="ts">
  import ScheduleItem from '@/components/schedule/PublicScheduleItem.vue';
  import { dateRegex, reducedWeekDays } from '@/composables/constants';
  import { usePublicBellsQuery } from '@/queries/bells';
  import { useBuildingsQuery } from '@/queries/buildings';
  import { useGroupsPublicQuery } from '@/queries/groups';
  import {
    useCoursesQuery,
    usePublicSchedulesQuery,
  } from '@/queries/schedules';
  import router from '@/router';
  import { useAuthStore } from '@/stores/auth';
  import { useSchedulePublicStore } from '@/stores/schedulePublic';
  import { useDateFormat, useDebounceFn } from '@vueuse/core';
  import { storeToRefs } from 'pinia';
  import Button from 'primevue/button';
  import DatePicker from 'primevue/datepicker';
  import InputText from 'primevue/inputtext';
  import Select from 'primevue/select';
  import Skeleton from 'primevue/skeleton';
  import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
  import { useRoute } from 'vue-router';

  const route = useRoute();
  const scheduleStore = useSchedulePublicStore();
  const { course, date, schedulesChanges, selectedGroup } =
    storeToRefs(scheduleStore);
  const { setSchedulesChanges } = scheduleStore;

  const isoDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
  });

  const cabinet = ref('');
  const searchedCabinet = ref('');
  const teacher = ref('');
  const searchedTeacher = ref('');
  const subject = ref('');
  const searchedSubject = ref('');

  const debouncedCabinetFn = useDebounceFn(
    () => (searchedCabinet.value = cabinet.value),
    500,
    { maxWait: 1000 }
  );
  const debouncedTeacherFn = useDebounceFn(
    () => (searchedTeacher.value = teacher.value),
    500,
    { maxWait: 1000 }
  );
  const debouncedSubjectFn = useDebounceFn(
    () => (searchedSubject.value = subject.value),
    500,
    { maxWait: 1000 }
  );

  const coursesWithLabel = computed(() => {
    return (
      courses.value?.map(course => ({
        label: `${course.course} курс`,
        value: course.course,
      })) || []
    );
  });

  const selectedCourse = computed(() => {
    return course.value;
  });

  const building = ref(null);
  const { data: buildingsFethed } = useBuildingsQuery();
  const buildings = computed(() => {
    return (
      buildingsFethed.value?.map(building => ({
        value: building.name,
        label: `${building.name} корпус`,
      })) || []
    );
  });

  const { data: courses } = useCoursesQuery(building);

  const {
    data: changesSchedules,
    isFetched,
    isError,
    isLoading,
  } = usePublicSchedulesQuery(
    isoDate,
    building,
    selectedCourse,
    selectedGroup,
    searchedCabinet,
    searchedTeacher,
    searchedSubject
  );

  const { data: groups } = useGroupsPublicQuery(
    selectedGroup,
    building,
    course
  );

  const updateQueryParams = () => {
    router.replace({
      query: {
        ...route.query,
        date: isoDate.value || undefined,
        building: building.value || undefined,
        course: selectedCourse.value || undefined,
        group: selectedGroup.value || undefined,
      },
    });
  };

  watch(changesSchedules, setSchedulesChanges);

  watch(
    [isoDate, selectedCourse, selectedGroup, building],
    () => {
      updateQueryParams();
    },
    { deep: true }
  );

  watch(
    building,
    () => {
      course.value = null;
      selectedGroup.value = null;
    },
    { flush: 'sync' }
  );

  watch(
    course,
    () => {
      selectedGroup.value = null;
    },
    { flush: 'sync' }
  );

  onMounted(() => {
    const {
      date: queryDate,
      building: queryBuilding,
      course: queryCourse,
      group: queryGroup,
    } = route.query as Partial<{
      date: string;
      building: string;
      course: string;
      group: string;
    }>;

    if (queryDate && dateRegex.test(queryDate)) {
      const [day, month, year] = queryDate.split('.').map(Number);
      date.value = new Date(year, month - 1, day);
    } else {
      date.value = new Date();
    }

    building.value = queryBuilding || null;
    course.value = queryCourse ? Number(queryCourse) : null;

    selectedGroup.value = queryGroup || null;

    updateQueryParams();
  });

  const formattedDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
  });

  const { data: publicBells, isFetched: isFetchedBells } = usePublicBellsQuery(
    building,
    formattedDate
  );

  const authStore = useAuthStore();
  const { isAuth } = storeToRefs(authStore);

  const headerRef = ref(null);
  const headerHeight = ref(0);

  const updateHeaderHeight = () => {
    if (headerRef.value) {
      headerHeight.value = headerRef.value.offsetHeight;
    }
  };

  // Используем ResizeObserver для отслеживания изменений размеров header
  let resizeObserver = null;

  onMounted(() => {
    resizeObserver = new ResizeObserver(updateHeaderHeight);
    if (headerRef.value) {
      resizeObserver.observe(headerRef.value);
      // Обновляем высоту при первой загрузке
      updateHeaderHeight();
    }
  });

  onBeforeUnmount(() => {
    if (resizeObserver && headerRef.value) {
      resizeObserver.unobserve(headerRef.value);
    }
  });

  const showFilters = ref(false);

  function toggleFilters() {
    showFilters.value = !showFilters.value;
    searchedCabinet.value = '';
    cabinet.value = '';
    searchedTeacher.value = '';
    teacher.value = '';
  }

  const mergedBells = computed(() => {
    // Функция для сравнения periods между разными корпусами
    const periodsEqual = (periods1, periods2) => {
      if (periods1.length !== periods2.length) return false;
      return periods1.every((p1, index) => {
        const p2 = periods2[index];
        return (
          p1.index === p2.index &&
          p1.has_break === p2.has_break &&
          p1.period_from === p2.period_from &&
          p1.period_to === p2.period_to &&
          p1.period_from_after === p2.period_from_after &&
          p1.period_to_after === p2.period_to_after
        );
      });
    };

    // Группируем звонки по одинаковым периодам
    const grouped = [];

    publicBells.value?.forEach(bell => {
      // Находим группу, у которой совпадают periods
      let group = grouped.find(g =>
        periodsEqual(g.bells.periods, bell.periods)
      );

      if (group) {
        // Если такая группа найдена, добавляем туда здание
        group.building += `, ${bell.building}`;
      } else {
        // Если группа не найдена, создаем новую
        grouped.push({
          building: String(bell.building),
          bells: bell,
        });
      }
    });

    return grouped;
  });

  const getIndexesFromBells = computed(() => {
    const indexes = new Set<number>();
    mergedBells.value?.forEach(bell => {
      bell.bells.periods.forEach(period => {
        indexes.add(period.index);
      });
    });
    return Array.from(indexes).sort((a, b) => a - b);
  });

  const headerHidden = ref(false);

  function handleDatePickerBtns(day) {
    switch (day) {
      case 'today':
        date.value = new Date();
        break;

      case 'tomorrow':
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        date.value = tomorrow;
        break;
    }
  }
</script>

<template>
  <div
    class="relative max-w-screen-xl mx-auto px-4 py-4 flex flex-col gap-4 scroll-smooth"
  >
    <div class="fixed bottom-8 right-8 z-50 flex gap-2 flex-col">
      <a
        title="К звонкам"
        class="pi pi-bell text-white dark:text-surface-900 bg-primary-500 rounded-full p-4"
        href="#bells"
      />
      <RouterLink
        v-if="isAuth"
        replace
        title="В панель управления"
        class="pi pi-pen-to-square text-white dark:text-surface-900 bg-primary-500 rounded-full p-4"
        :to="{ path: '/admin/schedules/changes', query: route.query }"
      />
    </div>

    <nav
      ref="headerRef"
      :class="{ '-translate-y-full': headerHidden }"
      class="transition-transform fixed rounded-lg rounded-t-none max-w-screen-xl mx-auto z-50 top-0 left-0 right-0 flex flex-wrap justify-between gap-4 p-4 bg-surface-100 dark:bg-surface-800"
    >
      <div class="flex flex-col flex-wrap gap-4 justify-between w-full">
        <div class="flex flex-wrap gap-2 items-center">
          <div class="flex flex-col md:w-auto w-full">
            <DatePicker
              v-model="date"
              append-to="self"
              fluid
              show-icon
              icon-display="input"
              :invalid="isError"
              date-format="dd.mm.yy"
            >
              <template #inputicon="slotProps">
                <div
                  class="flex gap-2 justify-between items-center"
                  @click="slotProps.clickCallback"
                >
                  <small>{{
                    reducedWeekDays[
                      useDateFormat(date, 'dddd', {
                        locales: 'ru-RU',
                      }).value
                    ]
                  }}</small>
                  <small>{{ schedulesChanges?.week_type }}</small>
                </div>
              </template>
              <template #footer>
                <div class="flex justify-between pt-1">
                  <Button
                    severity="secondary"
                    size="small"
                    label="Сегодня"
                    @click="handleDatePickerBtns('today')"
                  />
                  <Button
                    severity="secondary"
                    size="small"
                    label="Завтра"
                    @click="handleDatePickerBtns('tomorrow')"
                  />
                </div>
              </template>
            </DatePicker>
          </div>
          <Select
            v-model="building"
            overlay-class="w-full"
            append-to="self"
            title="Корпус"
            show-clear
            :options="buildings"
            option-label="label"
            option-value="value"
            placeholder="Корпус"
          />
          <Select
            v-model="course"
            overlay-class="w-full"
            append-to="self"
            class=""
            show-clear
            :options="coursesWithLabel"
            option-label="label"
            option-value="value"
            placeholder="Курс"
          />
          <div class="flex gap-2">
            <Select
              v-model="selectedGroup"
              append-to="self"
              empty-filter-message="Группы не найдены"
              filter
              show-clear
              option-value="name"
              :options="groups"
              option-label="name"
              placeholder="Группа"
              class="w-full"
            />
            <Button
              title="Фильтры"
              severity="secondary"
              text
              icon="pi pi-sliders-h"
              @click="toggleFilters"
            />
          </div>
          <div class="ml-auto self-center">
            <div
              v-if="schedulesChanges?.last_updated"
              class="flex gap-1 flex-row items-center lg:flex-col lg:gap-0 lg:items-end flex-wrap"
            >
              <span class="text-xs text-surface-400 leading-none"
                >Последние обновление:</span
              >
              <time
                title="Последние обновление"
                class="text-sm text-right text-surface-400"
                :datetime="schedulesChanges?.last_updated"
                >{{
                  useDateFormat(
                    schedulesChanges?.last_updated,
                    'DD.MM.YYYY HH:mm'
                  )
                }}</time
              >
            </div>
          </div>
        </div>

        <div v-show="showFilters" class="flex flex-wrap gap-2 items-center">
          <InputText
            v-model="cabinet"
            class="w-full md:w-auto"
            placeholder="Поиск по кабинету"
            @input="debouncedCabinetFn"
          />
          <InputText
            v-model="teacher"
            class="w-full md:w-auto"
            placeholder="Поиск по преподавателю"
            @input="debouncedTeacherFn"
          />
          <InputText
            v-model="subject"
            class="w-full md:w-auto"
            placeholder="Поиск по предмету"
            @input="debouncedSubjectFn"
          />
          <Button
            size="small"
            severity="secondary"
            label="Основное"
            target="_blank"
            icon="pi pi-print"
            as="router-link"
            :to="{
              path: '/print/main',
            }"
          />
          <Button
            size="small"
            severity="secondary"
            label="Изменения"
            target="_blank"
            icon="pi pi-print"
            as="router-link"
            :to="{
              path: '/print/changes',
              query: {
                date: isoDate,
              },
            }"
          />
          <Button
            size="small"
            severity="secondary"
            label="Звонки"
            target="_blank"
            icon="pi pi-print"
            as="router-link"
            :to="{
              path: '/print/bells',
              query: {
                date: isoDate,
              },
            }"
          />
        </div>
      </div>

      <button
        class="absolute left-1/2 -translate-x-1/2"
        style="bottom: -24px"
        @click="headerHidden = !headerHidden"
      >
        <svg
          class="relative"
          width="112"
          height="24"
          viewBox="0 0 28 6"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M3.57628e-07 0L27.8028 0C20.64 0 20.1127 5.32394 13.883 5.32394C7.65323 5.32394 6.71518 0 3.57628e-07 0Z"
            class="fill-surface-100 dark:fill-surface-800"
          />
        </svg>
        <span
          class="pi absolute top-0 left-1/2 -translate-x-1/2"
          :class="{
            'pi-angle-down': headerHidden,
            'pi-angle-up': !headerHidden,
          }"
        />
      </button>
    </nav>
    <div
      :style="{ marginTop: `${headerHidden ? '20' : headerHeight + 20}px` }"
      class="flex flex-col gap-4"
    >
      <span
        v-if="isFetched && !schedulesChanges?.schedules.length"
        class="text-2xl text-center"
        >Ничего не найдено...</span
      >
      <span v-else-if="isError" class="text-2xl"
        >Расписание ещё не выложили, либо в расписании ошибка.</span
      >
      <div class="schedules">
        <template v-if="isLoading">
          <div v-for="item in 32" class="schedule">
            <Skeleton height="2rem" class="mb-4" />
            <Skeleton height="10rem" />
          </div>
        </template>

        <template v-else-if="schedulesChanges?.schedules && !isError">
          <ScheduleItem
            v-for="item in schedulesChanges?.schedules"
            :key="item?.id"
            class="schedule"
            :date="isoDate"
            :schedule="item?.schedule"
            :semester="item?.semester"
            :type="item?.schedule?.type"
            :group-name="item?.group_name"
            :lessons="item?.schedule?.lessons"
            :week-type="item?.week_type"
            :published="item?.schedule?.published"
          />
        </template>
      </div>
    </div>
    <div class="flex flex-col gap-2 items-center w-full">
      <h1 id="bells" class="text-2xl font-bold text-center py-2">Звонки</h1>
      <span
        v-if="publicBells?.type"
        :class="{
          'text-green-400 ': publicBells?.type !== 'main',
          'text-surface-400 ': publicBells?.type === 'main',
        }"
        class="text-sm text-right py-1 px-2 rounded-lg"
        >{{ publicBells?.type === 'main' ? 'Основное' : 'Изменения' }}</span
      >
      <div class="">
        <h2 v-if="!publicBells && isFetchedBells" class="text-2xl text-center">
          На эту дату расписание звонков не найдено
        </h2>
        <div v-if="publicBells" class="">
          <table class="bells-table dark:bg-surface-900 bg-surface-50 rounded">
            <thead>
              <tr>
                <th>
                  <div class="flex gap-2 flex-col text-xs p-2">
                    <span class="self-end">Корпус</span>
                    <span class="border rotate-12" />
                    <span class="self-start">№ пары</span>
                  </div>
                </th>
                <th v-for="bell in mergedBells" :key="bell?.building">
                  <div class="flex flex-col gap-1 items-center">
                    <span>
                      {{ bell?.building }}
                    </span>
                    <span
                      :class="{
                        'text-green-400 ': bell.bells?.type !== 'main',
                        'text-surface-400 ': bell.bells?.type === 'main',
                      }"
                      class="text-sm text-right rounded-lg"
                      >{{
                        bell.bells?.type === 'main' ? 'Основное' : 'Изменения'
                      }}</span
                    >
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="index in getIndexesFromBells" :key="index" class="">
                <td class="text-center py-4 font-bold">{{ index }} пара</td>
                <template v-for="bell in mergedBells" :key="bell?.building">
                  <template
                    v-for="period in bell.bells.periods"
                    :key="period.index"
                  >
                    <td v-if="period?.index === index">
                      <div>
                        {{ period.period_from }} - {{ period.period_to }}
                      </div>
                      <div v-if="period?.period_from_after">
                        {{ period.period_from_after }} -
                        {{ period.period_to_after }}
                      </div>
                    </td>
                  </template>
                  <td
                    v-if="
                      !bell.bells.periods.find(period => period.index === index)
                    "
                  />
                </template>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
  .schedules {
    display: grid;
    row-gap: 2rem;
    column-gap: 10px;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  }

  .schedules > *:only-child {
    justify-self: center;
    width: 300px;
  }

  .bells-table {
    border-collapse: collapse;
  }

  .bells-table td {
    padding: 0.75rem 1rem;
  }

  @media screen and (max-width: 768px) {
    .schedules > *:only-child {
      justify-self: center;
      width: 100%;
    }
  }
</style>
