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

export function usePrintChangesSchedulesQuery(date) {
  const enabled = computed(() => Boolean(date.value));

  return useQuery({
    enabled: enabled,
    queryKey: ['PrintScheduleChanges', date],
    retry: 0,
    queryFn: async () =>
      (await axios.get(`/api/schedules/changes/print?date=${date.value}`)).data,
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

export function useCoursesQuery(building?) {
  // Условие enabled всегда true, если building не передается, или true если building задан
  const enabled = computed(() => Boolean(building?.value || true));

  return useQuery({
    enabled,
    queryKey: ['courses', building],
    queryFn: async () => {
      // Формируем параметры запроса в зависимости от наличия building
      const queryParams = building?.value ? `?building=${building.value}` : '';

      // Выполняем запрос с параметрами или без них
      return (await axios.get(`/api/groups/courses${queryParams}`)).data;
    },
  });
}
export function usePublicSchedulesQuery(date, building, course, selectedGroup) {
  // Условие enabled проверяет только наличие параметра date
  const enabled = computed(() => Boolean(date?.value || building?.value));

  return useQuery({
    enabled: enabled,
    queryKey: ['scheduleChanges', date, building, course, selectedGroup],
    retry: 0,
    queryFn: async () => {
      // Формируем параметры запроса в зависимости от их наличия
      const queryParams = new URLSearchParams();
      if (date?.value) queryParams.append('date', date.value);
      if (building?.value) queryParams.append('building', building.value);
      if (course?.value) queryParams.append('course', course.value);
      if (selectedGroup?.value)
        queryParams.append('group', selectedGroup.value);

      // Выполняем запрос с параметрами
      return (
        (await axios.get(`/api/schedules/public?${queryParams.toString()}`))
          .data || []
      );
    },
  });
}
