<script setup lang="ts">
import { usePublicBellsQuery } from '@/queries/bells';
import { useDateFormat } from '@vueuse/core';
import Select from 'primevue/select';
import { computed, ref, watch } from 'vue';
import PublicRowPeriodBell from '@/components/PublicRowPeriodBell.vue';

const building = ref(1)
const buildings = ref([
    {
        value: 1,
    },
    {
        value: 2,
    },
    {
        value: 3,
    },
    {
        value: 4,
    },
    {
        value: 5,
    },
    {
        value: 6,
    },
])
const date = ref(new Date())

const formattedDate = computed(() => {
    return date.value ? useDateFormat(date.value, 'DD.MM.YYYY').value : null;
});

const { data: publicBells } = usePublicBellsQuery(building, formattedDate)
</script>

<template>
    <div class="max-w-screen-xl mx-auto px-4 py-4">
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
                <div class="flex flex-wrap gap-2 items-start w-full">
                    <Select title="Корпус" optionValue="value" v-model="building" :options="buildings"
                        option-label="value" placeholder="Корпус"></Select>

                </div>

            </div>
            <div class="">
                <div class="rounded-md border border-surface-200 dark:border-surface-800 dark:bg-surface-950">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr>
                                    <th
                                        class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                        №</th>
                                    <th
                                        class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                        Начало</th>
                                    <th
                                        class="border-b border-surface-200 px-6 py-4 text-left text-sm text-surface-700 dark:border-surface-800 dark:text-surface-300">
                                        Конец</th>
                                </tr>
                            </thead>
                            <tbody>
                                <PublicRowPeriodBell :key="period.id" v-for="period in publicBells?.periods"
                                    :period="period">
                                </PublicRowPeriodBell>



                            </tbody>
                        </table>


                    </div>

                </div>
            </div>
        </div>
    </div>

</template>