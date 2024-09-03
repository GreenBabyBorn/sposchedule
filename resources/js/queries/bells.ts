import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed } from 'vue';

export function useBellsQuery(type, variant, weekDay?, date?) {
  const enabled = computed(() => {
    if (weekDay.value) {
      return (
        Boolean(type.value) || Boolean(variant.value) || Boolean(weekDay.value)
      );
    }
    if (date.value) {
      return (
        Boolean(type.value) || Boolean(variant.value) || Boolean(date.value)
      );
    }
    // return Boolean(type.value) || Boolean(variant.value) || Boolean(date.value);
  });
  const weekDayOrDate = computed(() =>
    !date.value ? `&week_day=${weekDay.value}` : `&date=${date.value}`
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
    queryKey: ['bells', type, variant, weekDay, date],
    enabled: enabled,
    queryFn: useDebounceFn(
      async () =>
        (
          await axios.get(
            `/api/bells?type=${typeValues[type.value]}&variant=${variantValues[variant.value]}${weekDayOrDate.value}`
          )
        ).data,
      300
    ),
  });
}
