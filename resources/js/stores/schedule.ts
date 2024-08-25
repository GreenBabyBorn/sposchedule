import router from '@/router';
import { defineStore } from 'pinia';
import { ref, toRaw, watch } from 'vue';
import { useRoute } from 'vue-router';

export const useScheduleStore = defineStore('useScheduleStore', () => {
  const route = useRoute();
  const selectedMainGroup: any = ref(null);
  const selectedMainSemester: any = ref(null);
  const queryParams = ref(route.query);
  const schedules = ref();

  function setSchedules(scheduless) {
    schedules.value = toRaw(scheduless ?? []);
  }

  return {
    schedules,
    setSchedules,
    selectedMainGroup,
    selectedMainSemester,
    queryParams,
  };
});
