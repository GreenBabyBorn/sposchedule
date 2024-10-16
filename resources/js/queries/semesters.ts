import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { computed } from 'vue';

export function useSemestersQuery() {
  return useQuery({
    queryKey: ['semesters'],
    queryFn: async () => (await axios.get('/api/semesters')).data,
  });
}

export function useSemesterShowQuery(id) {
  const enabled = computed(() => Boolean(id?.value));
  return useQuery({
    enabled: enabled,
    queryKey: ['semesters', id],
    queryFn: async () => {
      const url = `/api/semesters/${id?.value}`;
      const response = await axios.get(url);
      return response.data;
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
    mutationFn: id => axios.delete(`/api/semesters/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['semesters'], exact: true });
    },
  });
  return destroySemesterMutation;
}
