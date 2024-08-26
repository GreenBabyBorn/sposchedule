<script setup lang="ts">
import Select from 'primevue/select';
import { useGroupsQuery } from '@/queries/groups';
import DatePicker from 'primevue/datepicker';
import { ref } from "vue";

const date = ref();

const { data: groups, isFetched, } = useGroupsQuery()
const selectedChangesGroup = ref()
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Расписание (изменения)</h1>
        </div>
        <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center">


                <DatePicker v-model="date" />
            </div>

        </div>
        <div class="flex flex-col gap-6">

        </div>
    </div>
</template>