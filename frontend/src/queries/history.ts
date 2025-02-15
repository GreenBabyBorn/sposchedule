import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import type { Ref } from 'vue';
// import { computed } from 'vue';

export function useHistoryQuery(page: Ref, rows: Ref, searchTerm: Ref) {
  // const enabled = computed(() => {
  //   return Boolean((page.value && rows.value) || searchTerm.value);
  // });
  return useQuery({
    // enabled: enabled,
    queryKey: ['history', page, rows, searchTerm],
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
