import { useMutation, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useUpdateLesson() {
  const queryClient = useQueryClient();
  const updateSemesterMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/lessons/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
    onError: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });
  return updateSemesterMutation;
}

export function useStoreLesson() {
  const queryClient = useQueryClient();
  const updateSemesterMutation = useMutation({
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
  const destroySemesterMutation = useMutation({
    mutationFn: ({ id }: any) => axios.delete(`/api/lessons/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });
  return destroySemesterMutation;
}
