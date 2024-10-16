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
  <tr class="bg-surface-50 dark:bg-surface-900 py-4">
    <td
      class="px-4 text-center py-4 font-bold text-surface-700 group-last:border-none dark:text-surface-400"
    >
      {{ period.index }} пара
    </td>

    <td
      class="px-4 text-center py-4 group-last:border-none flex flex-col gap-1 justify-center"
    >
      <div class="">{{ period.period_from }} - {{ period.period_to }}</div>
      <div v-if="period?.period_from_after && period?.period_from_after">
        {{ period.period_from_after }} - {{ period.period_to_after }}
      </div>
    </td>
  </tr>
</template>
