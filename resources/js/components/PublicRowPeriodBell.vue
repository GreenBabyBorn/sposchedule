<script setup lang="ts">
import { computed, onMounted, ref, toRef, watch } from 'vue';
const props = defineProps({
    period: {
        type: Object,
    },
});

const period = toRef(props.period);

</script>
<template>

    <tr class="group">
        <!-- Индекс периода -->
        <td
            class="border-surface-200 px-6 py-4 text-sm text-surface-700 group-last:border-none dark:border-surface-800 dark:text-surface-400">
            {{ period.index }}
        </td>

        <!-- Время начала и возможное время после -->
        <td class="border-surface-200 px-6 py-4 group-last:border-none dark:border-surface-800">
            <div class="mb-2">
                {{ period.period_from }}
            </div>
            <div v-if="period?.period_from_after">
                {{ period.period_from_after }}
            </div>
        </td>

        <!-- Время окончания и возможное время после -->
        <td class="border-surface-200 px-6 py-4 group-last:border-none dark:border-surface-800">
            <div class="mb-2">
                {{ period.period_to }}
            </div>
            <div v-if="period?.period_to_after">
                {{ period.period_to_after }} </div>
        </td>

    </tr>
</template>