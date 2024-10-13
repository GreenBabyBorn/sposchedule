import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { computed } from 'vue';

export function useTeachersQuery() {
  return useQuery({
    queryKey: ['teachers'],
    queryFn: async () => (await axios.get(`/api/teachers`)).data,
  });
}

export function useStoreTeacher() {
  const queryClient = useQueryClient();
  let storeTeacherMutation = useMutation({
    mutationFn: (body: object) => axios.post('/api/teachers', body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['teachers'] });
    },
  });
  return storeTeacherMutation;
}

// export function useStoreSubjectForTeacher() {
//   const queryClient = useQueryClient();
//   let storeTeacherMutation = useMutation({
//     mutationFn: ({ id, subject_id }: any) =>
//       axios.post(`/api/teachers/${id}/subjects`, { subject_id }),
//     onSuccess: () => {
//       // queryClient.invalidateQueries({ queryKey: ['teachers'] });
//     },
//   });
//   return storeTeacherMutation;
// }

// export function useDestroySubjectForTeacher() {
//   const queryClient = useQueryClient();
//   let storeTeacherMutation = useMutation({
//     mutationFn: ({ id, subject_id }: any) =>
//       axios.delete(`/api/teachers/${id}/subjects`, {
//         data: { subject_id },
//       }),
//     onSuccess: () => {
//       // queryClient.invalidateQueries({ queryKey: ['teachers'] });
//     },
//   });
//   return storeTeacherMutation;
// }

export function useUpdateTeacher() {
  const queryClient = useQueryClient();
  let updateTeacherMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/teachers/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['teachers'] });
    },
  });
  return updateTeacherMutation;
}

export function useDestroyTeacher() {
  const queryClient = useQueryClient();
  let destroyTeacherMutation = useMutation({
    mutationFn: id => axios.delete(`/api/teachers/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['teachers'], exact: true });
    },
  });
  return destroyTeacherMutation;
}
