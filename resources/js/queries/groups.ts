import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed } from 'vue';

export function useGroupsQuery(name?) {
  const enabled = computed(() => {
    return Boolean(name?.value);
  });
  return useQuery({
    queryKey: ['groups'],
    // enabled: true || enabled,
    queryFn: useDebounceFn(
      async () => (await axios.get(`/api/groups`)).data,
      300
    ),
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

export function useStoreSemesterForGroup() {
  const queryClient = useQueryClient();
  let storeGroupMutation = useMutation({
    mutationFn: ({ id, semester_id }: any) =>
      axios.post(`/api/groups/${id}/semesters`, { semester_id }),
    onSuccess: () => {
      // queryClient.invalidateQueries({ queryKey: ['groups'] });
    },
  });
  return storeGroupMutation;
}

export function useDestroySemesterForGroup() {
  const queryClient = useQueryClient();
  let storeGroupMutation = useMutation({
    mutationFn: ({ id, semester_id }: any) =>
      axios.delete(`/api/groups/${id}/semesters`, {
        data: { semester_id },
      }),
    onSuccess: () => {
      // queryClient.invalidateQueries({ queryKey: ['groups'] });
    },
  });
  return storeGroupMutation;
}
