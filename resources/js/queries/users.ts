import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useUserUpdate() {
  const queryClient = useQueryClient();
  let storeTeacherMutation = useMutation({
    mutationFn: (body: object) => axios.patch('/api/user', body),
    onSuccess: () => {
      // queryClient.invalidateQueries({ queryKey: ['user'] });
    },
  });
  return storeTeacherMutation;
}
