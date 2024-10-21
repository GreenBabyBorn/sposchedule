import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
// import { computed } from 'vue';

export function useHistoryQuery(page, rows, searchTerm) {
  // const enabled = computed(() => {
  //   return Boolean((page.value && rows.value) || searchTerm.value);
  // });
  return useQuery({
    // enabled: enabled,
    queryKey: ['history', page, rows],
    queryFn: async () => {
      const response = await axios.get('/api/history', {
        params: {
          page: page.value,
          rows: rows.value,
          search: searchTerm.value || null,
        },
      });
      return response.data;
    },
  });
}

export function useDestroyHistory() {
  const queryClient = useQueryClient();
  const destroySemesterMutation = useMutation({
    mutationFn: (id: any) => axios.delete(`/api/history/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['history'] });
    },
  });
  return destroySemesterMutation;
}
