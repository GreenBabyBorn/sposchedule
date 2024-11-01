import { useDateFormat } from '@vueuse/core';
import { defineStore } from 'pinia';
import { computed, ref, toRaw } from 'vue';
import { useRoute } from 'vue-router';

export const useScheduleStore = defineStore('useScheduleStore', () => {
  const route = useRoute();
  const selectedMainGroupName = ref(null);
  const selectedMainSemester = ref(null);
  const queryParams = ref(route.query);
  const schedules = ref();

  const schedulesChanges = ref();

  const date = ref(null);
  const formattedDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
  });
  const course = ref(null);
  const selectedCourse = computed(() => {
    return course.value;
  });

  function setSchedules(newSchedules) {
    schedules.value = toRaw(newSchedules ?? []);
  }
  function setSchedulesChanges(scheduless) {
    schedulesChanges.value = toRaw(scheduless ?? []);
  }
  const selectedGroup = ref();
  const building = ref(null);

  return {
    building,
    selectedGroup,
    schedules,
    formattedDate,
    setSchedules,
    selectedMainGroupName,
    selectedMainSemester,
    queryParams,
    schedulesChanges,
    setSchedulesChanges,
    date,
    course,
    selectedCourse,
  };
});
