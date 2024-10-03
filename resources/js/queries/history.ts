import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useHistoryQuery() {
  return useQuery({
    queryKey: ['history'],
    queryFn: async () => (await axios.get('/api/history')).data,
  });
}

export function useDestroyHistory() {
  const queryClient = useQueryClient();
  let destroySemesterMutation = useMutation({
    mutationFn: (id: any) => axios.delete(`/api/history/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['history'] });
    },
  });
  return destroySemesterMutation;
}
