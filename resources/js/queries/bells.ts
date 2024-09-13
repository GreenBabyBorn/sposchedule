import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed } from 'vue';

export function useBellsQuery(type, building, weekDay?, date?) {
  const enabled = computed(() => {
    if (weekDay.value) {
      return (
        Boolean(type.value) ||
        // Boolean(variant.value) ||
        Boolean(weekDay.value) ||
        Boolean(building.value)
      );
    }
    if (date.value) {
      return (
        Boolean(type.value) ||
        // Boolean(variant.value) ||
        Boolean(date.value) ||
        Boolean(building.value)
      );
    }
    // return Boolean(type.value) || Boolean(variant.value) || Boolean(date.value);
  });
  const weekDayOrDate = computed(() =>
    type.value === 'Основное'
      ? `&week_day=${weekDay.value}`
      : `&date=${date.value}`
  );

  const typeValues = {
    Основное: 'main',
    Изменения: 'changes',
  };
  // const variantValues = {
  //   Обычный: 'normal',
  //   Сокращенные: 'reduced',
  // };

  return useQuery({
    queryKey: ['bells', type, weekDay, date, building],
    enabled: enabled,
    queryFn: useDebounceFn(
      async () =>
        (
          await axios.get(
            `/api/bells?type=${typeValues[type.value]}&building=${building.value}${weekDayOrDate.value}`
          )
        ).data,
      300
    ),
  });
}

export function usePublicBellsQuery(building, date) {
  const enabled = computed(() => {
    if (date.value) {
      return Boolean(date.value) || Boolean(building.value);
    }
  });

  return useQuery({
    queryKey: ['bells', date, building],
    enabled: enabled,
    queryFn: async () =>
      (
        await axios.get(
          `/api/bells/public?building=${building.value}&date=${date.value}`
        )
      ).data,
  });
}

export function useStorePeriod() {
  const queryClient = useQueryClient();
  let storePeriodMutation = useMutation({
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
export function useStoreBell() {
  const queryClient = useQueryClient();
  let storePeriodMutation = useMutation({
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
export function useUpdateBellPeriod() {
  const queryClient = useQueryClient();
  let storePeriodMutation = useMutation({
    mutationFn: ({ id, body }: any) =>
      axios.patch(`/api/bells-periods/${id}`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
    },
  });
  return storePeriodMutation;
}

export function useDestroyBellPeriod() {
  const queryClient = useQueryClient();
  let destroyPeriodMutation = useMutation({
    mutationFn: (id: number) => axios.delete(`/api/bells-periods/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bells'] });
    },
  });
  return destroyPeriodMutation;
}
