<script setup lang="ts">
  import { toRef } from 'vue';
  const props = defineProps({
    period: {
      type: Object,
    },
  });

  const period = toRef(() => props.period);
</script>
<template>
  <tr class="bg-surface-50 py-4 dark:bg-surface-900">
    <td
      class="px-4 py-4 text-center font-bold text-surface-700 group-last:border-none dark:text-surface-400"
    >
      {{ period.index }} пара
    </td>

    <td
      class="flex flex-col justify-center gap-1 px-4 py-4 text-center group-last:border-none"
    >
      <div class="">{{ period.period_from }} - {{ period.period_to }}</div>
      <div v-if="period?.period_from_after && period?.period_from_after">
        {{ period.period_from_after }} - {{ period.period_to_after }}
      </div>
    </td>
  </tr>
</template>
