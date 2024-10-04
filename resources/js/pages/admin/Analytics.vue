<script setup lang="ts">

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';   // optional
import Row from 'primevue/row';                   // optional

import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';
import { useGroupsQuery } from '@/queries/groups';
import Chip from 'primevue/chip';
import MultiSelect from 'primevue/multiselect';
import { computed, ref } from 'vue';
import { useAnalyticsSchedulesQuery } from '@/queries/schedules';
import { useDateFormat } from '@vueuse/core';

const { data: groups, isFetched } = useGroupsQuery();

const rangeDates = ref(null)
const selectedGroups = ref(null)

const start_date = computed(() => rangeDates.value?.[0] ? useDateFormat(rangeDates?.value?.[0], 'DD.MM.YYYY').value : null)
const end_date = computed(() => rangeDates.value?.[1] ? useDateFormat(rangeDates?.value?.[1], 'DD.MM.YYYY').value : null)
const groups_ids = computed(() => selectedGroups.value?.map(group => group.id))

const { data } = useAnalyticsSchedulesQuery(start_date, end_date, groups_ids)
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Аналитика</h1>
        </div>

        <div class="">
            <form class="flex flex-wrap items-center gap-2 p-4 rounded-lg dark:bg-surface-800">
                <div class="flex flex-wrap items-center gap-2">
                    <MultiSelect v-model="selectedGroups" display="chip" :options="groups" optionLabel="name" filter
                        placeholder="Выбрать группы" :maxSelectedLabels="3" class="" />
                    <DatePicker v-model="rangeDates" append-to="self" placeholder="Период" date-format="dd.mm.yy"
                        selectionMode="range" :manualInput="false" />

                </div>
            </form>
        </div>
        <div class="">
            <DataTable :value="data" rowGroupMode="rowspan" tableStyle="min-width: 50rem">
                <Column field="group_name" header="Группа" style="min-width: 200px">
                    <template #body="slotProps">
                        {{ slotProps.data.group_name }}
                    </template>
                </Column>
                <Column field="subjects.[]" header="Предметы" style="min-width: 200px">
                    <template #body="slotProps">
                        <div v-for="(value, key, index) in slotProps.data.subjects" class="">
                            <p class="leading-normal">
                                {{ key }} -
                                <span class="text-lg ">{{ value }} ак. ч.</span>
                            </p>

                        </div>
                    </template>
                </Column>

            </DataTable>
        </div>
    </div>
</template>