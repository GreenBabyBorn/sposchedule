import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import type { Ref } from 'vue';

export function useLoadsQuery(query?: Ref) {
  console.log(query?.value);
  return useQuery({
    queryKey: ['loads', query],
    queryFn: async () =>
      (
        await axios.get(`/api/loads`, {
          params: { ...query?.value },
        })
      ).data,
  });
}

export function useStoreLoadQuery() {
  const queryClient = useQueryClient();
  const updateTeacherMutation = useMutation({
    mutationFn: ({ body }: any) => axios.post(`/api/loads`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['loads'] });
    },
  });
  return updateTeacherMutation;
}

export function useDestroyLoadQuery() {
  const queryClient = useQueryClient();
  const destroy = useMutation({
    mutationFn: id => axios.delete(`/api/loads/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['loads'] });
    },
  });
  return destroy;
}
