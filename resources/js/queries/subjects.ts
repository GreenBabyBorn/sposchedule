import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useSubjectsQuery() {
  return useQuery({
    queryKey: ['subjects'],
    queryFn: async () => (await axios.get('/api/subjects')).data,
  });
}

export function useStoreSubject() {
  const queryClient = useQueryClient();
  let storeSubjectMutation = useMutation({
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
  let updateSubjectMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/subjects/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['subjects'] });
    },
  });
  return updateSubjectMutation;
}

export function useDestroySubject() {
  const queryClient = useQueryClient();
  let destroySubjectMutation = useMutation({
    mutationFn: id => axios.delete(`/api/subjects/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['subjects'], exact: true });
    },
  });
  return destroySubjectMutation;
}
