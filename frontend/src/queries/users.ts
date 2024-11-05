import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export function useUserQuery() {
  return useQuery({
    queryKey: ['user'],
    queryFn: async () => (await axios.get(`/api/user`)).data,
  });
}
export function useUserUpdate() {
  const queryClient = useQueryClient();
  const updateUserMutation = useMutation({
    mutationFn: (body: object) => axios.patch('/api/user', body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['user'] });
    },
  });
  return updateUserMutation;
}
