import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { computed } from 'vue';

export function useMainSchedulesQuery(mainGroup, mainSemester) {
  const enabled = computed(() =>
    Boolean(mainGroup.value && mainSemester.value)
  );

  return useQuery({
    enabled: enabled,
    queryKey: ['scheduleMain', mainGroup, mainSemester],
    queryFn: async () =>
      (
        await axios.get(
          `/api/groups/${mainGroup.value.id}/semester/${mainSemester.value.id}/schedules/main/`
        )
      ).data,
  });
}

export function useStoreSchedule() {
  const queryClient = useQueryClient();
  let updateSemesterMutation = useMutation({
    mutationFn: ({ body }: any) => axios.post(`/api/schedules`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
    },
  });
  return updateSemesterMutation;
}
