import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { computed, type Ref } from 'vue';

export function useBellsQuery(
  type: Ref,
  building: Ref,
  weekDay?: Ref,
  date?: Ref
) {
  const typeValues = {
    Основное: 'main',
    Изменения: 'changes',
  };

  return useQuery({
    queryKey: ['bells', building, type, weekDay, typeValues[type.value], date],
    queryFn: async () =>
      (
        await axios.get(`/api/bells`, {
          params: {
            building: building.value,
            type: typeValues[type.value],
            week_day: weekDay?.value,
            date: date?.value,
          },
        })
      ).data,
  });
}

export function usePublicBellsQuery(building: Ref, date: Ref) {
  const enabled = computed(() => Boolean(date?.value));

  return useQuery({
    queryKey: ['bells', date, building],
    enabled: enabled,
    retry: 0,
    queryFn: async () => {
      return (
        await axios.get(`/api/bells/public`, {
          params: {
            building: building.value,
            date: date.value,
          },
        })
      ).data;
    },
  });
}
export function usePublicBellsPrintQuery(buildings: Ref, date: Ref) {
  const enabled = computed(() => Boolean(date?.value));

  return useQuery({
    queryKey: ['bells', date, buildings],
    enabled: enabled,
    retry: 0,
    queryFn: async () => {
      const queryParams = new URLSearchParams();
      if (date?.value) queryParams.append('date', date.value);
      if (buildings?.value) queryParams.append('buildings', buildings.value);
      return (
        await axios.get(`/api/bells/public/print?${queryParams.toString()}`)
      ).data;
    },
  });
}

export function useStorePeriod() {
  const queryClient = useQueryClient();
  const storePeriodMutation = useMutation({
    mutationFn: (body: object) =>
      axios.post('/api/bells-periods', {
        ...body,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
    },
  });
  return storePeriodMutation;
}

export function useStorePresetBell() {
  const queryClient = useQueryClient();
  const storePeriodMutation = useMutation({
    mutationFn: (body: object) =>
      axios.post('/api/bells/presets', {
        ...body,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells-presets'] });
    },
  });
  return storePeriodMutation;
}
export function useApplyPreset() {
  const queryClient = useQueryClient();
  const storePeriodMutation = useMutation({
    mutationFn: (body: object) =>
      axios.post('/api/bells/presets/apply', {
        ...body,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
      queryClient.invalidateQueries({ queryKey: ['bells-presets'] });
    },
  });
  return storePeriodMutation;
}
export function usePresetsBells() {
  return useQuery({
    queryKey: ['bells-presets'],

    queryFn: async () => (await axios.get(`/api/bells/presets`)).data,
  });
}
export function useStoreBell() {
  const queryClient = useQueryClient();
  const storePeriodMutation = useMutation({
    mutationFn: (body: object) =>
      axios.post('/api/bells', {
        ...body,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
    },
  });
  return storePeriodMutation;
}
export function useUpdateBell() {
  const queryClient = useQueryClient();
  const storePeriodMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/bells/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
      // queryClient.invalidateQueries({ queryKey: ['bells-presets'] });
    },
    onError: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
      // queryClient.invalidateQueries({ queryKey: ['bells-presets'] });
    },
  });
  return storePeriodMutation;
}

export function useUpdateBellPeriod() {
  const queryClient = useQueryClient();
  const storePeriodMutation = useMutation({
    mutationFn: ({ id, body }: any) =>
      axios.patch(`/api/bells-periods/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
      // queryClient.invalidateQueries({ queryKey: ['bells-presets'] });
    },
    onError: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
      // queryClient.invalidateQueries({ queryKey: ['bells-presets'] });
    },
  });
  return storePeriodMutation;
}

export function useDestroyBellPeriod() {
  const queryClient = useQueryClient();
  const destroyPeriodMutation = useMutation({
    mutationFn: (id: number) => axios.delete(`/api/bells-periods/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
    },
  });
  return destroyPeriodMutation;
}
export function useDestroyBells() {
  const queryClient = useQueryClient();
  const destroyPeriodMutation = useMutation({
    mutationFn: (id: number) => axios.delete(`/api/bells/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells-presets'] });
    },
  });
  return destroyPeriodMutation;
}
