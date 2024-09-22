import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed } from 'vue';

export function useGroupsQuery(name?, building?, course?) {
  const enabled = computed(() => {
    return Boolean(name?.value || building?.value || course?.value || true);
  });

  return useQuery({
    enabled: enabled,
    queryKey: ['groups', name, building, course],
    staleTime: 300000,
    queryFn: async () => {
      const queryParams = new URLSearchParams();
      if (name?.value) queryParams.append('name', name.value);
      if (building?.value) queryParams.append('building', building.value);
      if (course?.value) queryParams.append('course', course.value);

      return (await axios.get(`/api/groups?${queryParams.toString()}`)).data;
    },
  });
}
export function useGroupsPublicQuery(name?, building?, course?) {
  const enabled = computed(() => {
    return Boolean(name?.value || building?.value || course?.value || true);
  });

  return useQuery({
    enabled: enabled,
    queryKey: ['groups', name, building, course],
    queryFn: async () => {
      const queryParams = new URLSearchParams();
      if (name?.value) queryParams.append('name', name.value);
      if (building?.value) queryParams.append('building', building.value);
      if (course?.value) queryParams.append('course', course.value);

      return (await axios.get(`/api/groups/public?${queryParams.toString()}`))
        .data;
    },
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
      queryClient.invalidateQueries({ queryKey: ['groups'] });
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
