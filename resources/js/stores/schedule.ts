import { defineStore } from 'pinia';
import { ref, toRaw } from 'vue';

export const useScheduleStore = defineStore('useScheduleStore', () => {
  const schedules = ref();
  function setSchedules(scheduless) {
    schedules.value = toRaw(scheduless ?? []);
  }

  return { schedules, setSchedules };
});
