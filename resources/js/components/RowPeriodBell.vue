<script setup lang="ts">
import { computed, onMounted, ref, toRef } from 'vue';
import DatePicker from 'primevue/datepicker';
import Checkbox from 'primevue/checkbox';



const props = defineProps({
    data: {
        type: Object,
    },
})

const period = toRef(props.data)


let checkbox = ref();
onMounted(() => {
    checkbox.value = Boolean(period.value.period_to_after) || Boolean(period.value.period_from_after)
})
// const has_break = computed(() => period.value.period_to_after || period.value.period_from_after || checkbox.value);
</script>

<template>
    <tr class="group">
        <td
            class=" border-surface-200 px-6 py-4 align- text-sm text-surface-700 group-last:border-none dark:border-surface-800 dark:text-surface-400">
            {{ period.index }}</td>
        <td class=" border-surface-200 px-6 py-4 align-top group-last:border-none dark:border-surface-800">
            <div class="mb-2">
                <DatePicker v-model="period.period_from" id="datepicker-timeonly" timeOnly fluid />
            </div>
            <div v-if="checkbox">
                <DatePicker v-model="period.period_from_after" id="datepicker-timeonly" timeOnly fluid />
            </div>
        </td>
        <td class="border-surface-200 px-6 py-4 align-top group-last:border-none dark:border-surface-800">
            <div class="mb-2">
                <DatePicker v-model="period.period_to" id="datepicker-timeonly" timeOnly fluid />
            </div>
            <div v-if="checkbox">
                <DatePicker v-model="period.period_to_after" id="datepicker-timeonly" timeOnly fluid />
            </div>
        </td>
        <td class=" border-surface-200 px-6 py-4 align-top group-last:border-none dark:border-surface-800">
            <Checkbox v-model="checkbox" :binary="true" />
        </td>
    </tr>
</template>