import type { Teacher } from '@/components/schedule/types';
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { computed, type Ref } from 'vue';

export function useTeachersQuery(query?: object) {
  return useQuery({
    queryKey: ['teachers', query],
    queryFn: async () =>
      (
        await axios.get(`/api/teachers`, {
          params: query,
        })
      ).data as Teacher[],
  });
}

export function useStoreTeacher() {
  const queryClient = useQueryClient();
  const storeTeacherMutation = useMutation({
    mutationFn: (body: object) => axios.post('/api/teachers', body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['teachers'] });
    },
  });
  return storeTeacherMutation;
}

export function useUpdateTeacher() {
  const queryClient = useQueryClient();
  const updateTeacherMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/teachers/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['teachers'] });
    },
  });
  return updateTeacherMutation;
}

export function useDestroyTeacher() {
  const queryClient = useQueryClient();
  const destroyTeacherMutation = useMutation({
    mutationFn: (id: number) => axios.delete(`/api/teachers/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['teachers'], exact: true });
    },
  });
  return destroyTeacherMutation;
}

export function useMergeTeachers() {
  const queryClient = useQueryClient();
  const storeSubjectMutation = useMutation({
    mutationFn: ({ teacher_ids, target_name }: any) =>
      axios.post('/api/teachers/merge', {
        teacher_ids,
        target_name,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['teachers'] });
    },
  });
  return storeSubjectMutation;
}

export function useTeachersFromSubjectQuery(
  subject: Ref,
  group: Ref,
  date: Ref
) {
  const enabled = computed(() => {
    return Boolean(subject.value?.id && group.value?.id && date.value);
  });
  return useQuery({
    enabled: enabled,
    queryKey: ['teachersFromSubject', subject, group, date],
    queryFn: async () =>
      (
        await axios.get(`/api/teachers/schedules/main`, {
          params: {
            subject_id: subject.value?.id,
            group_id: group.value?.id,
            date: date.value,
          },
        })
      ).data,
  });
}
