<script setup lang="ts">
  import ScheduleItem from '../../components/schedule/AdminMainScheduleItem.vue';
  import Select from 'primevue/select';
  import { computed, watch, watchEffect } from 'vue';
  import { useGroupsQuery } from '@/queries/groups';
  import { useMainSchedulesQuery } from '@/queries/schedules';
  import { useScheduleStore } from '@/stores/schedule';
  import { storeToRefs } from 'pinia';
  import { useRoute } from 'vue-router';
  import router from '@/router';
  import Button from 'primevue/button';

  const route = useRoute();

  const scheduleStore = useScheduleStore();
  const { schedulesMain, selectedMainGroup, selectedMainSemester } =
    storeToRefs(scheduleStore);
  const { setMainSchedules } = scheduleStore;

  const { data: groups, isFetched } = useGroupsQuery();
  const semesters = computed(() => selectedMainGroup.value?.semesters);
  const { data: mainSchedules } = useMainSchedulesQuery(
    selectedMainGroup,
    selectedMainSemester
  );

  const updateQueryParams = () => {
    const newQuery = {
      ...route.query,
      group: selectedMainGroup.value?.name || undefined,
      semester: selectedMainSemester.value?.id || undefined,
    };

    router.replace({ query: newQuery });
  };

  watch([selectedMainGroup, selectedMainSemester], updateQueryParams, {
    deep: true,
  });

  watch(mainSchedules, () => {
    setMainSchedules(mainSchedules.value!);
  });

  watchEffect(() => {
    if (isFetched.value) {
      if (
        route.query.group &&
        groups.value?.find(item => item.name === route.query.group) &&
        !selectedMainGroup.value
      ) {
        selectedMainGroup.value = groups.value.find(
          item => item.name === route.query.group
        );
        if (route.query.semester && selectedMainGroup.value?.semesters) {
          selectedMainSemester.value = selectedMainGroup.value.semesters.find(
            item => item.id === +route.query.semester!
          );
        }
      }
    }
  });
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-baseline justify-between">
      <h1 class="text-2xl">Расписание</h1>
    </div>
    <div
      class="flex flex-wrap items-center justify-between gap-4 rounded-lg bg-surface-100 p-4 dark:bg-surface-900"
    >
      <div class="flex flex-wrap items-center gap-2">
        <Select
          v-model="selectedMainGroup"
          fluid
          filter
          :options="groups"
          option-label="name"
          placeholder="Группа"
          class=""
        />
        <Select
          v-model="selectedMainSemester"
          fluid
          :options="semesters"
          option-label="name"
          placeholder="Семестр"
          class=""
        />
        <Button
          target="_blank"
          icon="pi pi-print"
          as="router-link"
          :to="{
            path: '/print/main',
          }"
        />
      </div>
    </div>
    <div class="flex flex-col gap-6">
      <ScheduleItem
        v-for="(schedule, index) in schedulesMain"
        :key="index"
        :group="selectedMainGroup"
        :semester="selectedMainSemester"
        :schedule="schedule"
        :lessons="schedule?.lessons"
        :published="schedule?.published"
        :week-day="schedule?.week_day"
      />
    </div>
    <div v-if="!schedulesMain" class="">
      <p class="text-lg">
        Здесь заполняется Основное расписание, которое в дальнейшем можно будет
        использовать при заполнении изменений. Оно заполняется один раз.
      </p>
      <p class="text-lg">
        Чтобы начать, выберите сначала <b>группу</b> и <b>семестр</b>.
      </p>
    </div>
  </div>
</template>
