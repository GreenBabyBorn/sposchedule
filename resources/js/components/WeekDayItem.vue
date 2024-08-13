<script setup lang="ts">
import InputText from 'primevue/inputtext';

import InputNumber from 'primevue/inputnumber';

import Select from 'primevue/select';
import { useSubjectsQuery } from '@/queries/subjects';
import { useTeachersQuery } from '@/queries/teachers';

const props = defineProps({

    weekDay: { type: String, required: true },
    lessons: { type: Array, required: true },
})

const { data: subjects } = useSubjectsQuery()
const { data: teachers } = useTeachersQuery()
</script>

<template>
    <table class="min-w-full border-collapse border border-gray-400">
        <caption>{{ props.weekDay }}</caption>
        <thead>
            <tr>
                <th class="border border-gray-300 p-2">№</th>
                <th class="border border-gray-300 p-2">Предметы</th>
                <th class="border border-gray-300 p-2">Препод</th>
                <th class="border border-gray-300 p-2">Корпус</th>
                <th class="border border-gray-300 p-2">Кабинет</th>
            </tr>
        </thead>

        <tbody>

            <tr class="border-t">
                <td class="w-2 border border-gray-300 p-2 text-center" rowspan="2">0</td>
                <td class=" border-gray-300 p-2"><Select class="w-full" :options="subjects" optionLabel="name"></Select>
                </td>
                <td class=" border-gray-300 p-2"><Select class="w-full" :options="teachers" optionLabel="name"></Select>
                </td>
                <td class="w-4 border-gray-300 p-2">
                    <InputNumber inputId="minmax" :min="0" :max="100" fluid />
                </td>
                <td class="w-4 border-gray-300 p-2">
                    <InputNumber inputId="minmax" :min="0" :max="100" fluid />
                </td>
            </tr>
            <tr>
                <td class=" border-gray-300 p-2"><Select class="w-full" :options="subjects" optionLabel="name"></Select>
                </td>
                <td class=" border-gray-300 p-2"><Select class="w-full" :options="teachers" optionLabel="name"></Select>
                </td>
                <td class=" border-gray-300 p-2">
                    <InputNumber inputId="minmax" :min="0" :max="100" fluid />
                </td>
                <td class=" border-gray-300 p-2">
                    <InputNumber inputId="minmax" :min="0" :max="100" fluid />
                </td>
            </tr>

        </tbody>

    </table>
</template>