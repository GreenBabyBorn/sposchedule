import type { Semester } from '@/components/schedule/types';
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { computed, type Ref } from 'vue';

export function useSemestersQuery() {
  return useQuery({
    queryKey: ['semesters'],
    queryFn: async () => (await axios.get('/api/semesters')).data as Semester[],
  });
}

export function useSemesterShowQuery(id: Ref) {
  const enabled = computed(() => Boolean(id?.value));
  return useQuery({
    enabled: enabled,
    queryKey: ['semesters', id],
    queryFn: async () => {
      return (await axios.get(`/api/semesters/${id?.value}`)).data as Semester;
    },
  });
}

export function useStoreSemester() {
  const queryClient = useQueryClient();
  const storeSemesterMutation = useMutation({
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
  const updateSemesterMutation = useMutation({
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
  const destroySemesterMutation = useMutation({
    mutationFn: (id: number) => axios.delete(`/api/semesters/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['semesters'] });
    },
  });
  return destroySemesterMutation;
}
