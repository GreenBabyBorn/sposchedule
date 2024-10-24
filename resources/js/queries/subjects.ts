import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useSubjectsQuery(query?: object) {
  return useQuery({
    queryKey: ['subjects', query],

    queryFn: async () =>
      (
        await axios.get('/api/subjects', {
          params: query,
        })
      ).data,
  });
}

export function useStoreSubject() {
  const queryClient = useQueryClient();
  const storeSubjectMutation = useMutation({
    mutationFn: (name: string) =>
      axios.post('/api/subjects', {
        name,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['subjects'] });
    },
  });
  return storeSubjectMutation;
}

export function useUpdateSubject() {
  const queryClient = useQueryClient();
  const updateSubjectMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/subjects/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['subjects'] });
    },
  });
  return updateSubjectMutation;
}

export function useDestroySubject() {
  const queryClient = useQueryClient();
  const destroySubjectMutation = useMutation({
    mutationFn: id => axios.delete(`/api/subjects/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['subjects'], exact: true });
    },
  });
  return destroySubjectMutation;
}
