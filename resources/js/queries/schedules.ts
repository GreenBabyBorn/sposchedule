import type {
  ChangesSchedules,
  MainSchedule,
} from '@/components/schedule/types';
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
          `/api/groups/${mainGroup.value?.id}/semester/${mainSemester.value?.id}/schedules/main/`
        )
      ).data as MainSchedule[],
  });
}
export function useStoreSchedule() {
  const queryClient = useQueryClient();
  const updateSemesterMutation = useMutation({
    mutationFn: ({ body }: any) => axios.post(`/api/schedules`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
    },
  });
  return updateSemesterMutation;
}

export function useChangesSchedulesQuery(
  date,
  building,
  course,
  selectedGroup
) {
  const enabled = computed(() => Boolean(date?.value || building?.value));

  return useQuery({
    enabled: enabled,
    queryKey: ['scheduleChanges', date, building, course, selectedGroup],
    queryFn: async () => {
      return (
        await axios.get(`/api/schedules/changes`, {
          params: {
            date: date?.value,
            building: building.value,
            course: course.value,
            group: selectedGroup.value,
          },
        })
      ).data as ChangesSchedules;
    },
  });
}

export function usePrintChangesSchedulesQuery(date) {
  const enabled = computed(() => Boolean(date.value));

  return useQuery({
    enabled: enabled,
    queryKey: ['PrintScheduleChanges', date],
    queryFn: async () =>
      (await axios.get(`/api/schedules/changes/print?date=${date.value}`)).data,
  });
}
export function usePrintMainSchedulesQuery(semester_id, course, buildings) {
  const enabled = computed(
    () =>
      Boolean(semester_id?.value) &&
      Boolean(course?.value) &&
      Boolean(buildings?.value.toString())
  );

  return useQuery({
    enabled: enabled,
    queryKey: ['PrintScheduleMain', semester_id, course, buildings],
    queryFn: async () => {
      const response = await axios.get(
        `/api/schedules/main/semester/${semester_id?.value}/print`,
        {
          params: {
            course: course?.value,
            buildings: buildings?.value?.toString(),
          },
        }
      );

      return response.data;
    },
  });
}

export function useStoreScheduleChange() {
  const queryClient = useQueryClient();
  const updateSemesterMutation = useMutation({
    mutationFn: ({ body }: any) => axios.post(`/api/schedules`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });
  return updateSemesterMutation;
}
export function useFromMainToChangesSchedule() {
  const queryClient = useQueryClient();
  const updateSemesterMutation = useMutation({
    mutationFn: ({ id, body }: any) =>
      axios.patch(`/api/schedules/${id}/changes`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });
  return updateSemesterMutation;
}
export function useCreateScheduleWithChanges() {
  const queryClient = useQueryClient();
  const updateSemesterMutation = useMutation({
    mutationFn: ({ body }: any) => axios.post(`/api/schedules/changes`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });
  return updateSemesterMutation;
}
// export function useUpdateSchedule() {
//   const queryClient = useQueryClient();
//   const updateSemesterMutation = useMutation({
//     mutationFn: ({ id, body }: any) =>
//       axios.patch(`/api/schedules/${id}`, body),
//     onSuccess: () => {
//       queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
//       queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
//     },
//   });
//   return updateSemesterMutation;
// }
export function useUpdateSchedule() {
  const queryClient = useQueryClient();

  const updateSemesterMutation = useMutation({
    mutationFn: ({ id, body }: any) =>
      axios.patch(`/api/schedules/${id}`, body),
    onMutate: async ({ id, body }) => {
      await queryClient.cancelQueries({ queryKey: ['scheduleChanges'] });
      const previousSchedule = queryClient.getQueryData(['scheduleChanges']);

      // Оптимистичное обновление кэша
      queryClient.setQueryData(['scheduleChanges'], (old: any) => {
        if (!old) return;
        return old.map((item: any) =>
          item.id === id ? { ...item, ...body } : item
        );
      });

      // Возвращаем "контекст", чтобы использовать его в случае ошибки
      return { previousSchedule };
    },
    onError: (err, item, context: any) => {
      // Восстанавливаем старое состояние в случае ошибки
      if (context?.previousSchedule) {
        queryClient.setQueryData(['scheduleChanges'], context.previousSchedule);
      }
      console.error('Ошибка обновления:', err);
    },
  });

  return updateSemesterMutation;
}

export function useCoursesQuery(building?) {
  // const enabled = computed(() => Boolean(building?.value));

  return useQuery({
    // enabled,
    queryKey: ['courses', building],
    queryFn: async () => {
      return (
        await axios.get(`/api/groups/courses`, {
          params: {
            building: building?.value,
          },
        })
      ).data;
    },
  });
}
export function usePublicSchedulesQuery(
  date,
  building,
  course,
  selectedGroup,
  searchedCabinet,
  searchedTeacher,
  searchedSubject
) {
  const enabled = computed(() => Boolean(date?.value || building?.value));

  return useQuery({
    enabled: enabled,
    queryKey: [
      'scheduleChanges',
      date,
      building,
      course,
      selectedGroup,
      searchedCabinet,
      searchedTeacher,
      searchedSubject,
    ],
    retry: 0,
    queryFn: async () => {
      return (
        await axios.get(`/api/schedules/public`, {
          params: {
            date: date.value,
            building: building.value,
            course: course.value,
            cabinet: searchedCabinet.value,
            teacher: searchedTeacher.value,
            subject: searchedSubject.value,
            group: selectedGroup.value,
          },
        })
      ).data;
    },
  });
}

export function useAnalyticsSchedulesQuery(start_date, end_date, groups_ids) {
  const enabled = computed(() => Boolean(end_date?.value && start_date?.value));

  return useQuery({
    enabled: enabled,

    queryKey: ['schedulesAnalytics', start_date, end_date, groups_ids],
    queryFn: async () => {
      return (
        await axios.get(`/api/groups/schedules/hours`, {
          params: {
            start_date: start_date.value,
            end_date: end_date.value,
            groups_ids: groups_ids.value,
          },
        })
      ).data;
    },
  });
}
