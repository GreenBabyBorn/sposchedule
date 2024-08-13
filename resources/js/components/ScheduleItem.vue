<script setup lang="ts">
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import { useSubjectsQuery } from '@/queries/subjects';
import { useTeachersQuery } from '@/queries/teachers';
import { useUpdateSchedule } from '@/queries/schedules';
import { useToast } from 'primevue/usetoast';
import { toRef } from 'vue';



const toast = useToast();
const props = defineProps({

    weekDay: { type: String, required: true },
    // lessons: { type: Array, required: true },
    item: { required: true },
})

const items = toRef(() => props.item)

const { data: subjects } = useSubjectsQuery()
const { data: teachers } = useTeachersQuery()

const { mutateAsync: updateSchedule, isPending: isUpdated } = useUpdateSchedule()
async function updateCabinet(item) {
    try {
        console.log(item.building)
        await updateSchedule({
            id: item.id,
            body: {
                building: item.building,
                cabinet: item.cabinet,
                subject_id: item.subject.id,
                schedule_id: item.schedule_id,
                index: item.index,
            }
        })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
}

</script>

<template>
    <div class="">
        <table class="min-w-full border-collapse border border-surface-800">
            <caption class="text-2xl font-medium text-left mb-2">{{ props.weekDay }}</caption>
            <thead>
                <tr>
                    <th class="border border-surface-800 p-2 font-medium">№</th>
                    <th class="max-w-min border border-surface-800 p-2 font-medium">Предметы ЧИСЛ|ЗНАМ</th>
                    <th class="max-w-min border border-surface-800 p-2 font-medium">Преподаватель</th>
                    <th class="border border-surface-800 p-2 font-medium">Корпус</th>
                    <th class="border border-surface-800 p-2 font-medium">Кабинет</th>
                </tr>
            </thead>

            <tbody>
                <template v-for="item in items as any">
                    <tr class="border-t border-surface-800">
                        <td class="w-2 border border-surface-800 p-2 text-center" rowspan="2">{{ item.index }}</td>
                        <td class="border-surface-800 p-2"><Select @change="updateCabinet(item['ЧИСЛ'])"
                                v-model="item['ЧИСЛ'].subject" class="w-full" :options="subjects"
                                optionLabel="name"></Select></td>
                        <td class=" border-surface-800 p-2">
                            <MultiSelect @change="updateCabinet(item['ЧИСЛ'])" v-model="item['ЧИСЛ'].teachers"
                                class="w-full" :options="teachers" optionLabel="name"></MultiSelect>
                        </td>
                        <td class="w-4 border-surface-800 p-2">
                            <InputText @change="updateCabinet(item['ЧИСЛ'])" v-model="item['ЧИСЛ'].building" />
                            <!-- <InputNumber v-model="item['ЧИСЛ'].building" @blur="updateCabinet(item['ЧИСЛ'])" /> -->
                        </td>
                        <td class="w-4 border-surface-800 p-2">
                            <InputText @change="updateCabinet(item['ЧИСЛ'])" v-model="item['ЧИСЛ'].cabinet" />
                        </td>
                    </tr>
                    <tr class="">
                        <td class=" border-surface-800 p-2"><Select @change="updateCabinet(item['ЗНАМ'])"
                                v-model="item['ЗНАМ'].subject" class="w-full" :options="subjects"
                                optionLabel="name"></Select></td>
                        <td class=" border-surface-800 p-2">
                            <MultiSelect @change="updateCabinet(item['ЗНАМ'])" v-model="item['ЗНАМ'].teachers"
                                class="w-full" :options="teachers" optionLabel="name"></MultiSelect>
                        </td>
                        <td class=" border-surface-800 p-2">
                            <InputText @change="updateCabinet(item['ЗНАМ'])" v-model="item['ЗНАМ'].building" />
                            <!-- <InputNumber @update:modelValue="updateCabinet(item['ЗНАМ'])"
                                v-model="item['ЗНАМ'].building" inputId="minmax" :min="0" fluid /> -->
                        </td>
                        <td class=" border-surface-800 p-2">
                            <InputText @change="updateCabinet(item['ЗНАМ'])" v-model="item['ЗНАМ'].cabinet" />
                        </td>
                    </tr>
                </template>






            </tbody>

        </table>

    </div>
</template>