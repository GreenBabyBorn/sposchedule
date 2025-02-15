<script setup lang="ts">
  import DatePicker from 'primevue/datepicker';
  import ChangesScheduleItem from '@/components/schedule/AdminChangesScheduleItem.vue';
  import { onMounted, watch } from 'vue';
  import {
    useChangesSchedulesQuery,
    useCoursesQuery,
  } from '@/queries/schedules';
  import { useDateFormat } from '@vueuse/core';
  import { useScheduleStore } from '@/stores/schedule';
  import { storeToRefs } from 'pinia';
  import router from '@/router';
  import { useRoute } from 'vue-router';
  import Select from 'primevue/select';
  import Button from 'primevue/button';
  import { useBuildingsQuery } from '@/queries/buildings';
  import { useGroupsPublicQuery } from '@/queries/groups';
  import {
    reducedWeekDays,
    dateRegex,
    type FullWeekDays,
  } from '@/composables/constants';

  const route = useRoute();
  const scheduleStore = useScheduleStore();
  const {
    course,
    date,
    formattedDate,
    selectedCourse,
    schedulesChanges,
    selectedGroup,
    building,
  } = storeToRefs(scheduleStore);
  const { setSchedulesChanges } = scheduleStore;

  const { data: courses } = useCoursesQuery(building);

  const updateQueryParams = () => {
    router.replace({
      query: {
        ...route.query,
        date: formattedDate.value || undefined,
        building: building.value || undefined,
        course: selectedCourse.value || undefined,
        group: selectedGroup.value || undefined,
      },
    });
  };

  const {
    data: changesSchedules,
    isError,
    isFetching,
    isFetched,
    isSuccess,
  } = useChangesSchedulesQuery(
    formattedDate,
    building,
    selectedCourse,
    selectedGroup
  );

  watch(changesSchedules, setSchedulesChanges, { deep: true });

  watch(
    [formattedDate, building, selectedCourse, selectedGroup],
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
    if (route.query.date && dateRegex.test(route.query.date as string)) {
      const [day, month, year] = (route.query.date as string)
        .split('.')
        .map(Number);
      date.value = new Date(year, month - 1, day);
    } else {
      date.value = new Date();
    }

    if (route.query.building) {
      building.value = route.query.building as string;
    }

    if (route.query.course) {
      course.value = Number(route.query.course as string);
    }

    if (route.query.group) {
      selectedGroup.value = route.query.group as string;
    }

    updateQueryParams();
  });

  const { data: groups } = useGroupsPublicQuery(null, building, course);

  const { data: buildingsFethed } = useBuildingsQuery();

  const handleDatePickerBtns = {
    today: () => {
      date.value = new Date();
    },
    tomorrow: () => {
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      date.value = tomorrow;
    },
  };
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <h1 class="text-2xl">Изменения</h1>
      <div class="ml-auto self-center">
        <div
          v-if="schedulesChanges?.last_updated"
          class="flex flex-row flex-wrap items-center gap-1 lg:flex-col lg:items-end lg:gap-0"
        >
          <span class="text-xs leading-none text-surface-400"
            >Последние обновление:</span
          >
          <time
            title="Последние обновление"
            class="text-right text-sm text-surface-400"
            :datetime="schedulesChanges?.last_updated"
            >{{
              useDateFormat(
                schedulesChanges?.last_updated,
                'DD.MM.YYYY HH:mm:ss'
              )
            }}</time
          >
        </div>
      </div>
    </div>
    <div
      class="flex items-center justify-between gap-4 rounded-lg bg-surface-100 p-4 dark:bg-surface-900"
    >
      <div class="flex w-full flex-wrap items-center gap-2">
        <DatePicker
          v-model="date"
          append-to="self"
          class="shrink-0"
          show-icon
          icon-display="input"
          :invalid="isError"
          date-format="dd.mm.yy"
          select-other-months
        >
          <template #inputicon="slotProps">
            <div
              class="flex items-center justify-between gap-2"
              @click="slotProps.clickCallback"
            >
              <small>{{
                reducedWeekDays[
                  useDateFormat(date as Date, 'dddd', {
                    locales: 'ru-RU',
                  }).value as FullWeekDays
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
                @click="handleDatePickerBtns.today"
              />
              <Button
                severity="secondary"
                size="small"
                label="Завтра"
                @click="handleDatePickerBtns.tomorrow"
              />
            </div>
          </template>
        </DatePicker>
        <Select
          v-model="building"
          title="Корпус"
          show-clear
          :options="
            buildingsFethed?.map(building => ({
              value: building.name,
              label: `${building.name} корпус`,
            })) || []
          "
          option-label="label"
          option-value="value"
          placeholder="Корпус"
        />
        <Select
          v-model="course"
          show-clear
          :options="
            courses?.map(course => ({
              label: `${course.course} курс`,
              value: course.course,
            })) || []
          "
          option-label="label"
          option-value="value"
          placeholder="Курс"
        />
        <Select
          v-model="selectedGroup"
          empty-filter-message="Группы не найдены"
          filter
          show-clear
          option-value="name"
          :options="groups"
          option-label="name"
          placeholder="Группа"
          filter-placeholder="Поиск группы"
        />
        <Button
          target="_blank"
          icon="pi pi-print"
          as="router-link"
          :to="{
            path: '/print/changes',
            query: {
              date: formattedDate,
            },
          }"
        />
      </div>
    </div>

    <span v-if="isError"
      >Семестра на данную дату не найдено, чтобы добавить перейдите на экран
      добавления
      <RouterLink class="underline" to="/admin/semesters">семестра</RouterLink>
    </span>
    <div
      v-if="isSuccess && isFetched && schedulesChanges?.schedules"
      class="schedules z-50"
    >
      <ChangesScheduleItem
        v-for="(schedule, index) in schedulesChanges?.schedules"
        :key="index"
        :disabled="isFetching"
        class="schedule"
        :date="formattedDate as string"
        :semester="schedulesChanges?.semester"
        :schedule="schedule"
        :type="schedule?.type || undefined"
        :group="schedule?.group"
        :lessons="schedule?.lessons || []"
        :published="schedule?.published"
      />
    </div>
  </div>
</template>

<style scoped>
  .schedules {
    /* display: flex;
    flex-wrap: wrap;
    row-gap: 2rem;
    column-gap: 10px;
    justify-content: space-between; */
    display: grid;
    row-gap: 2rem;
    column-gap: 10px;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  }
  .schedules > *:only-child {
    justify-self: center;
    width: 500px;
  }
  @media screen and (max-width: 440px) {
    .schedules {
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
  }
  @media screen and (max-width: 768px) {
    .schedules > *:only-child {
      justify-self: center;
      width: 100%;
    }
  }
</style>
