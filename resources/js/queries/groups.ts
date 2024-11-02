import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useGroupsQuery(name?, building?, course?) {
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
      ).data;
    },
  });
}
export function useGroupsPublicQuery(name?, building?, course?) {
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
    mutationFn: id => axios.delete(`/api/groups/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['groups'] });
    },
  });
  return destroyGroupMutation;
}

// export function useStoreSemesterForGroup() {
//   const queryClient = useQueryClient();
//   let storeGroupMutation = useMutation({
//     mutationFn: ({ id, semester_id }: any) =>
//       axios.post(`/api/groups/${id}/semesters`, { semester_id }),
//     onSuccess: () => {
//       // queryClient.invalidateQueries({ queryKey: ['groups'] });
//     },
//   });
//   return storeGroupMutation;
// }

// export function useDestroySemesterForGroup() {
//   const queryClient = useQueryClient();
//   let storeGroupMutation = useMutation({
//     mutationFn: ({ id, semester_id }: any) =>
//       axios.delete(`/api/groups/${id}/semesters`, {
//         data: { semester_id },
//       }),
//     onSuccess: () => {
//       // queryClient.invalidateQueries({ queryKey: ['groups'] });
//     },
//   });
//   return storeGroupMutation;
// }
