import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useSemestersQuery() {
  return useQuery({
    queryKey: ['semesters'],
    queryFn: async () => (await axios.get('/api/semesters')).data,
  });
}

export function useStoreSemester() {
  const queryClient = useQueryClient();
  let storeSemesterMutation = useMutation({
    mutationFn: (body: object) =>
      axios.post('/api/semesters', {
        ...body,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['semesters'] });
    },
  });
  return storeSemesterMutation;
}

export function useUpdateSemester() {
  const queryClient = useQueryClient();
  let updateSemesterMutation = useMutation({
    mutationFn: ({ id, body }: any) =>
      axios.patch(`/api/semesters/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['semesters'] });
    },
  });
  return updateSemesterMutation;
}

export function useDestroySemester() {
  const queryClient = useQueryClient();
  let destroySemesterMutation = useMutation({
    mutationFn: id => axios.delete(`/api/semesters/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['semesters'], exact: true });
    },
  });
  return destroySemesterMutation;
}