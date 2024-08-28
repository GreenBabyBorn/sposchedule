import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { computed } from 'vue';

export function useUpdateLesson() {
  const queryClient = useQueryClient();
  let updateSemesterMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/lessons/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
    },
  });
  return updateSemesterMutation;
}

export function useStoreLesson() {
  const queryClient = useQueryClient();
  let updateSemesterMutation = useMutation({
    mutationFn: ({ body }: any) => axios.post(`/api/lessons`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ['scheduleMain'],
      });
      queryClient.invalidateQueries({
        queryKey: ['scheduleChanges'],
      });
    },
  });
  return updateSemesterMutation;
}

export function useDestroyLesson() {
  const queryClient = useQueryClient();
  let destroySemesterMutation = useMutation({
    mutationFn: ({ id }: any) => axios.delete(`/api/lessons/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });
  return destroySemesterMutation;
}
