import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { computed } from 'vue';

export function useBellsQuery(type, building, weekDay?, date?) {
  const weekDayOrDate = computed(() =>
    type.value === 'Основное'
      ? `&week_day=${weekDay.value}`
      : `&date=${date.value}`
  );

  const typeValues = {
    Основное: 'main',
    Изменения: 'changes',
  };

  return useQuery({
    queryKey: ['bells', building, type, weekDayOrDate, typeValues[type.value]],
    queryFn: async () =>
      (
        await axios.get(
          `/api/bells?type=${typeValues[type.value]}&building=${building.value || ''}${weekDayOrDate.value}`
        )
      ).data,
  });
}

export function usePublicBellsQuery(building, date) {
  const enabled = computed(() => Boolean(date?.value || building?.value));

  return useQuery({
    queryKey: ['bells', date, building],
    enabled: enabled,
    retry: 0,
    queryFn: async () => {
      const queryParams = new URLSearchParams();
      if (building?.value) queryParams.append('building', building.value);
      if (date?.value) queryParams.append('date', date.value);
      return (await axios.get(`/api/bells/public?${queryParams.toString()}`))
        .data;
    },
  });
}
export function usePublicBellsPrintQuery(buildings, date) {
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
