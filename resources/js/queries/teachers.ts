import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useTeachersQuery(query?) {
  return useQuery({
    queryKey: ['teachers'],
    queryFn: async () =>
      (
        await axios.get(`/api/teachers`, {
          params: query,
        })
      ).data,
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
    mutationFn: id => axios.delete(`/api/teachers/${id}`),
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
