import { defineStore } from 'pinia';
import { ref, toRaw, watch } from 'vue';
import { useRoute } from 'vue-router';

export const useSchedulePublicStore = defineStore(
  'useSchedulePublicStore',
  () => {
    const route = useRoute();
    const selectedGroup: any = ref(null);
    const queryParams = ref(route.query);
    const schedules = ref();

    const schedulesChanges = ref();

    const date = ref(new Date());
    const course = ref(null);

    function setSchedules(scheduless) {
      schedules.value = toRaw(scheduless ?? []);
    }
    function setSchedulesChanges(scheduless) {
      schedulesChanges.value = toRaw(scheduless ?? []);
    }

    return {
      schedules,
      setSchedules,
      selectedGroup,

      queryParams,
      schedulesChanges,
      setSchedulesChanges,
      date,
      course,
    };
  }
);
