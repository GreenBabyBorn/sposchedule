import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
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
        (await axios.get(`/api/schedules/changes?${queryParams.toString()}`))
          .data || []
      );
    },
  });
  // return useQuery({
  //   enabled: enabled,
  //   queryKey: ['scheduleChanges', date, course],
  //   retry: 0,
  //   // staleTime: 300000,
  //   queryFn: async () =>
  //     (
  //       await axios.get(
  //         `/api/schedules/changes?date=${date.value}&course=${course.value || ''}`
  //       )
  //     ).data,
  // });
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
      // Выполнение запроса, если все параметры валидны
      const response = await axios.get(
        `/api/schedules/main/semester/${semester_id?.value}/print?course=${course?.value}&buildings=${buildings?.value?.toString()}`
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
export function useUpdateSchedule() {
  const queryClient = useQueryClient();
  const updateSemesterMutation = useMutation({
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
export function usePublicSchedulesQuery(
  date,
  building,
  course,
  selectedGroup,
  searchedCabinet,
  searchedTeacher,
  searchedSubject
) {
  // Условие enabled проверяет только наличие параметра date
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
      // Формируем параметры запроса в зависимости от их наличия
      const queryParams = new URLSearchParams();
      if (date?.value) queryParams.append('date', date.value);
      if (building?.value) queryParams.append('building', building.value);
      if (course?.value) queryParams.append('course', course.value);
      if (searchedCabinet?.value)
        queryParams.append('cabinet', searchedCabinet.value);
      if (searchedTeacher?.value)
        queryParams.append('teacher', searchedTeacher.value);
      if (searchedSubject?.value)
        queryParams.append('subject', searchedSubject.value);
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

export function useAnalyticsSchedulesQuery(start_date, end_date, groups_ids) {
  // Условие enabled проверяет только наличие параметра date
  const enabled = computed(() => Boolean(end_date?.value && start_date?.value));

  return useQuery({
    enabled: enabled,

    queryKey: ['schedulesAnalytics', start_date, end_date, groups_ids],
    queryFn: async () => {
      // Формируем параметры запроса в зависимости от их наличия
      const queryParams = new URLSearchParams();
      if (start_date?.value && end_date?.value) {
        queryParams.append('start_date', start_date.value);
        queryParams.append('end_date', end_date.value);
      }

      if (groups_ids?.value) queryParams.append('group_ids', groups_ids.value);

      // Выполняем запрос с параметрами
      return (
        (
          await axios.get(
            `/api/groups/schedules/hours?${queryParams.toString()}`
          )
        ).data || []
      );
    },
  });
}
