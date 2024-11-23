import { watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

/**
 * Универсальная функция для debounce обновления реактивных значений
 * @param source - реактивное значение, которое нужно отслеживать
 * @param target - реактивное значение, которое будет обновлено
 * @param delay - задержка debounce (в миллисекундах)
 * @param options - дополнительные опции для useDebounceFn (например, maxWait)
 */
export function useDebouncedSync<T>(
  source: { value: T },
  target: { value: T },
  delay: number = 500,
  options?: { maxWait?: number }
) {
  const updateTarget = useDebounceFn(
    () => {
      target.value = source.value;
    },
    delay,
    options
  );

  watch(source, updateTarget);
}
