import { useMutation } from '@tanstack/vue-query';
import axios from 'axios';

export function useUserUpdate() {
  const storeTeacherMutation = useMutation({
    mutationFn: (body: object) => axios.patch('/api/user', body),
    onSuccess: () => {
      // queryClient.invalidateQueries({ queryKey: ['user'] });
    },
  });
  return storeTeacherMutation;
}
