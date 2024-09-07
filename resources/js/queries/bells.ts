import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed } from 'vue';

export function useBellsQuery(type, variant, building, weekDay?, date?) {
  const enabled = computed(() => {
    if (weekDay.value) {
      return (
        Boolean(type.value) ||
        Boolean(variant.value) ||
        Boolean(weekDay.value) ||
        Boolean(building.value)
      );
    }
    if (date.value) {
      return (
        Boolean(type.value) ||
        Boolean(variant.value) ||
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
  const variantValues = {
    Обычный: 'normal',
    Сокращенные: 'reduced',
  };

  return useQuery({
    queryKey: ['bells', type, variant, weekDay, date, building],
    enabled: enabled,
    queryFn: useDebounceFn(
      async () =>
        (
          await axios.get(
            `/api/bells?type=${typeValues[type.value]}&building=${building.value}&variant=${variantValues[variant.value]}${weekDayOrDate.value}`
          )
        ).data,
      300
    ),
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
