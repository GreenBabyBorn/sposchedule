import type {
  ChangesSchedules,
  Group,
  MainSchedule,
} from '@/components/schedule/types';
import { useDateFormat } from '@vueuse/core';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';

export const useScheduleStore = defineStore('useScheduleStore', () => {
  const route = useRoute();
  const queryParams = ref(route.query);
  const schedulesMain = ref<MainSchedule[]>([]);
  const selectedMainGroup = ref<Group>(null);
  const selectedMainSemester = ref(null);

  function setMainSchedules(newSchedules) {
    schedulesMain.value = JSON.parse(JSON.stringify(newSchedules ?? []));
  }

  const schedulesChanges = ref<ChangesSchedules>();
  const date = ref(null);
  const formattedDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
  });
  const course = ref(null);
  const selectedCourse = computed(() => {
    return course.value;
  });
  const selectedGroup = ref();
  const building = ref(null);

  function setSchedulesChanges(newSchedules: ChangesSchedules) {
    schedulesChanges.value = JSON.parse(JSON.stringify(newSchedules ?? []));
  }

  return {
    building,
    selectedGroup,
    schedulesMain,
    formattedDate,
    setMainSchedules,
    selectedMainGroup,
    selectedMainSemester,
    queryParams,
    schedulesChanges,
    setSchedulesChanges,
    date,
    course,
    selectedCourse,
  };
});
