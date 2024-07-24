import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useGroupsQuery() {
  return useQuery({
    queryKey: ['groups'],
    queryFn: async () => (await axios.get('/api/groups')).data,
  });
}

export function useStoreGroup() {
  const queryClient = useQueryClient();
  let storeGroupMutation = useMutation({
    mutationFn: (body: object) =>
      axios.post('/api/groups', {
        ...body,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['groups'] });
    },
  });
  return storeGroupMutation;
}

export function useUpdateGroup() {
  const queryClient = useQueryClient();
  let updateGroupMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/groups/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['groups'] });
    },
  });
  return updateGroupMutation;
}

export function useDestroyGroup() {
  const queryClient = useQueryClient();
  let destroyGroupMutation = useMutation({
    mutationFn: id => axios.delete(`/api/groups/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['groups'], exact: true });
    },
  });
  return destroyGroupMutation;
}
