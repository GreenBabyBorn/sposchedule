import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed } from 'vue';

export function useMainSchedulesQuery(mainGroup, mainSemester) {
  const enabled = computed(() =>
    Boolean(mainGroup.value || mainSemester.value)
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

export function useChangesSchedulesQuery(date, course) {
  const enabled = computed(() => Boolean(date.value));

  return useQuery({
    enabled: enabled,
    queryKey: ['scheduleChanges', date, course],
    retry: 0,
    queryFn: async () =>
      (
        await axios.get(
          `/api/schedules/changes?date=${date.value}&course=${course.value || ''}`
        )
      ).data,
  });
}

export function useStoreScheduleChange() {
  const queryClient = useQueryClient();
  let updateSemesterMutation = useMutation({
    mutationFn: ({ body }: any) => axios.post(`/api/schedules`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });
  return updateSemesterMutation;
}
export function useFromMainToChangesSchedule() {
  const queryClient = useQueryClient();
  let updateSemesterMutation = useMutation({
    mutationFn: ({ id, body }: any) =>
      axios.patch(`/api/schedules/${id}/changes`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });
  return updateSemesterMutation;
}
export function useUpdateSchedule() {
  const queryClient = useQueryClient();
  let updateSemesterMutation = useMutation({
    mutationFn: ({ id, body }: any) =>
      axios.patch(`/api/schedules/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
    },
  });
  return updateSemesterMutation;
}

export function useCoursesQuery() {
  return useQuery({
    queryKey: ['courses'],
    queryFn: async () => (await axios.get(`/api/groups/courses`)).data,
  });
}

export function usePublicSchedulesQuery(date, course, selectedGroup) {
  const enabled = computed(() => Boolean(date.value));

  return useQuery({
    enabled: enabled,
    queryKey: ['scheduleChanges', date, course, selectedGroup],
    retry: 0,
    queryFn: async () =>
      (
        await axios.get(
          `/api/schedules/public?date=${date.value}&course=${course.value || ''}&group=${selectedGroup.value || ''}`
        )
      ).data || [],
  });
}
