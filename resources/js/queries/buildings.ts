import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed } from 'vue';

export function useBuildingsQuery(name?) {
  return useQuery({
    queryKey: ['buildings'],
    queryFn: useDebounceFn(
      async () => (await axios.get(`/api/buildings`)).data,
      300
    ),
  });
}
