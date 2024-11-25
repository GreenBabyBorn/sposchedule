import type { Group } from '@/components/schedule/types';
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import type { Ref } from 'vue';

export function useGroupsQuery(name?: Ref, building?: Ref, course?: Ref) {
  return useQuery({
    queryKey: ['groups', name, building, course],
    queryFn: async () => {
      return (
        await axios.get(`/api/groups`, {
          params: {
            name: name?.value,
            building: building?.value,
            course: course?.value,
          },
        })
      ).data as Group[];
    },
  });
}
export function useGroupsPublicQuery(name?: Ref, building?: Ref, course?: Ref) {
  return useQuery({
    queryKey: ['groups', name, building, course],
    queryFn: async () => {
      return (
        await axios.get(`/api/groups/public`, {
          params: {
            name: name?.value,
            building: building?.value,
            course: course?.value,
          },
        })
      ).data;
    },
  });
}

export function useStoreGroup() {
  const queryClient = useQueryClient();
  const storeGroupMutation = useMutation({
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
  const updateGroupMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/groups/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['groups'] });
    },
  });
  return updateGroupMutation;
}

export function useDestroyGroup() {
  const queryClient = useQueryClient();
  const destroyGroupMutation = useMutation({
    mutationFn: (id: number) => axios.delete(`/api/groups/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['groups'] });
    },
  });
  return destroyGroupMutation;
}
