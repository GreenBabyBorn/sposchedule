import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';

export type Building = {
  name: string;
  location: string;
  created_at?: Date;
  updated_at?: Date;
};

export function useBuildingsQuery() {
  return useQuery({
    queryKey: ['buildings'],
    queryFn: async () => (await axios.get(`/api/buildings`)).data as Building[],
  });
}

export function useStoreBuilding() {
  const queryClient = useQueryClient();
  const storeBuildingMutation = useMutation({
    mutationFn: (body: object) => axios.post('/api/buildings', body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['buildings'] });
    },
  });
  return storeBuildingMutation;
}

export function useUpdateBuilding() {
  const queryClient = useQueryClient();
  const updateuildingMutation = useMutation({
    mutationFn: ({ name, body }: any) =>
      axios.patch(`/api/buildings/${name}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['buildings'] });
    },
  });
  return updateuildingMutation;
}

export function useDestroyBuilding() {
  const queryClient = useQueryClient();
  const destroyuildingMutation = useMutation({
    mutationFn: name => axios.delete(`/api/buildings/${name}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['buildings'] });
    },
  });
  return destroyuildingMutation;
}
