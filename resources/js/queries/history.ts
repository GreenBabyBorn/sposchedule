import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useHistoryQuery(page: any, rows: any) {
  return useQuery({
    queryKey: ['history', page, rows],
    queryFn: async () => {
      const response = await axios.get('/api/history', {
        params: { page: page.value, rows: rows.value },
      });
      return response.data;
    },
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
