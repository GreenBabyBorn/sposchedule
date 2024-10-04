import { defineStore } from 'pinia';
import { ref, toRaw, watch } from 'vue';
import { useRoute } from 'vue-router';

export const useScheduleStore = defineStore('useScheduleStore', () => {
  const route = useRoute();
  const selectedMainGroupName: any = ref(null);
  const selectedMainSemester: any = ref(null);
  const queryParams = ref(route.query);
  const schedules = ref();

  const schedulesChanges = ref();

  const date = ref(null);
  const course = ref(null);

  function setSchedules(newSchedules) {
    schedules.value = toRaw(newSchedules ?? []);
  }
  function setSchedulesChanges(scheduless) {
    schedulesChanges.value = toRaw(scheduless ?? []);
  }

  return {
    schedules,
    setSchedules,
    selectedMainGroupName,
    selectedMainSemester,
    queryParams,
    schedulesChanges,
    setSchedulesChanges,
    date,
    course,
  };
});
