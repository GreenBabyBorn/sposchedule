import { defineStore } from 'pinia';
import { ref, toRaw } from 'vue';
import { useRoute } from 'vue-router';

export const useSchedulePublicStore = defineStore(
  'useSchedulePublicStore',
  () => {
    const route = useRoute();
    const queryParams = ref(route.query);

    const selectedGroup = ref<string | null>(null);
    const date = ref<Date>();
    const schedules = ref();
    const course = ref<number | null>(null);

    function setSchedules(newSchedules) {
      schedules.value = toRaw(newSchedules ?? []);
    }

    return {
      selectedGroup,
      queryParams,
      schedules,
      setSchedules,
      date,
      course,
    };
  }
);
