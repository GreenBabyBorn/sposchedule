<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import { useSubjectsQuery } from '@/queries/subjects';
import { useTeachersQuery } from '@/queries/teachers';
import { useStoreSchedule } from '@/queries/schedules';
import { useDestroyLesson, useStoreLesson } from '@/queries/lessons';
import { useToast } from 'primevue/usetoast';
import { reactive, toRef } from 'vue';



const toast = useToast();
const props = defineProps({
    weekDay: { type: String, required: true },
    group: { required: true },
    semester: { required: true },
    item: { required: true },
})

const items = toRef(() => props.item)

const { data: subjects } = useSubjectsQuery()
const { data: teachers } = useTeachersQuery()

const { mutateAsync: updateLesson, isPending: isUpdated } = useStoreLesson()
async function editLesson(item) {
    if (!item.id) return
    console.log(item.teachers)
    try {
        console.log(item.building)
        await updateLesson({
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

let newLesson = reactive({
    index: null,
    'ЧИСЛ':
    {
        subject: null,
        teachers: [],
        building: null,
        cabinet: null,

    },
    'ЗНАМ': {
        subject: null,
        teachers: [],
        building: null,
        cabinet: null,

    }
})

const { mutateAsync: storeSchedule, data: newSchedule } = useStoreSchedule()
const { mutateAsync: storeLesson } = useStoreLesson()

async function addNewLesson() {
    if (newLesson.ЧИСЛ.subject && newLesson.index) {
        try {
            await storeSchedule({
                body: {
                    // @ts-ignore
                    group_id: props.group.id,
                    // @ts-ignore
                    semester_id: props.semester.id,
                    type: 'main',
                    week_type: 'ЧИСЛ',
                    week_day: props.weekDay,
                    view_mode: 'table'
                }
            })
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }

        try {
            await storeLesson({
                body: {
                    ...newLesson.ЧИСЛ,
                    index: newLesson.index,
                    subject_id: newLesson.ЧИСЛ.subject.id,
                    schedule_id: newSchedule.value.data.id
                }
            })
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }

    }

    if (newLesson.ЗНАМ.subject && newLesson.index) {
        try {
            await storeSchedule({
                body: {
                    // @ts-ignore
                    group_id: props.group.id,
                    // @ts-ignore
                    semester_id: props.semester.id,
                    type: 'main',
                    week_type: 'ЗНАМ',
                    week_day: props.weekDay,
                    view_mode: 'table'
                }
            })
            await storeLesson({
                body: {
                    ...newLesson.ЗНАМ,
                    index: newLesson.index,
                    subject_id: newLesson.ЗНАМ.subject.id,
                    schedule_id: newSchedule.value.data.id
                }
            })
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }

}

async function addLesson(weekType, index, item) {
    console.log(item)
    if (weekType == 'ЧИСЛ' && Object.keys(item).length !== 0) {
        try {
            await storeSchedule({
                body: {
                    // @ts-ignore
                    group_id: props.group.id,
                    // @ts-ignore
                    semester_id: props.semester.id,
                    type: 'main',
                    week_type: 'ЧИСЛ',
                    week_day: props.weekDay,
                    view_mode: 'table'
                }
            })
            await storeLesson({
                body: {
                    ...item,
                    index: index,
                    subject_id: item.subject.id,
                    schedule_id: newSchedule.value.data.id
                }
            })
        }
        catch (e) {
            console.log(e)
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }

    else if (weekType == 'ЗНАМ' && Object.keys(item).length !== 0) {
        console.log(item)
        try {
            await storeSchedule({
                body: {
                    // @ts-ignore
                    group_id: props.group.id,
                    // @ts-ignore
                    semester_id: props.semester.id,
                    type: 'main',
                    week_type: 'ЗНАМ',
                    week_day: props.weekDay,
                    view_mode: 'table'
                }
            })
            await storeLesson({
                body: {
                    ...item,
                    index: index,
                    subject_id: item.subject.id,
                    schedule_id: newSchedule.value.data.id
                }
            })
        }
        catch (e) {
            console.log(e)
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }
    else {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Недозаполненно', life: 3000, closable: true });
    }
}

const { mutateAsync: destroyLesson } = useDestroyLesson()
async function removeLesson(id) {
    try {
        await destroyLesson({ id: id })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
}

</script>

<template>
    <div class="relative overflow-x-auto">
        <table class="w-full table-auto border-collapse border border-surface-800">
            <caption class="text-2xl font-medium text-left mb-2">{{ props.weekDay }}</caption>
            <thead>
                <tr>
                    <th class=" border border-surface-800 p-2 font-medium">№</th>
                    <th class="border border-surface-800 p-2 font-medium">Предметы ЧИСЛ | ЗНАМ</th>
                    <th class="border border-surface-800 p-2 font-medium">Преподаватель</th>
                    <th class=" border border-surface-800 p-2 font-medium">Корпус</th>
                    <th class="border border-surface-800 p-2 font-medium">Кабинет</th>
                    <th class="border border-surface-800 p-2 font-medium">Действия</th>
                </tr>
            </thead>

            <tbody>
                <template v-for="item in items as any">
                    <tr class="border-t border-surface-800">
                        <td class="border border-surface-800 p-2 text-center" rowspan="2">{{ item.index }}</td>
                        <td class="border-surface-800 p-2"><Select @change="editLesson(item['ЧИСЛ'])"
                                v-model="item['ЧИСЛ'].subject" class="w-full" :options="subjects"
                                optionLabel="name"></Select>
                        </td>
                        <td class=" border-surface-800 p-2">
                            <MultiSelect placeholder="Выберите преподавателя" @change="editLesson(item['ЧИСЛ'])"
                                v-model="item['ЧИСЛ'].teachers" class="w-full" :options="teachers" optionLabel="name">
                            </MultiSelect>
                        </td>
                        <td class="text-center border-surface-800 p-2">
                            <InputText class="max-w-10" @change="editLesson(item['ЧИСЛ'])"
                                v-model="item['ЧИСЛ'].building" />

                        </td>
                        <td class="text-center border-surface-800 p-2">
                            <InputText class="max-w-10" @change="editLesson(item['ЧИСЛ'])"
                                v-model="item['ЧИСЛ'].cabinet" />
                        </td>

                        <td class="border-surface-800 p-2">
                            <Button @click="addLesson('ЧИСЛ', item.index, item['ЧИСЛ'])" icon="pi pi-plus"
                                v-if="!item['ЧИСЛ'].id"></Button>
                            <Button @click="removeLesson(item['ЧИСЛ'].id)" icon="pi pi-trash" severity="danger"
                                v-if="item['ЧИСЛ'].id"></Button>
                        </td>
                    </tr>
                    <tr class="border-t border-surface-800">
                        <td class=" border-surface-800 p-2"><Select @change="editLesson(item['ЗНАМ'])"
                                v-model="item['ЗНАМ'].subject" class="w-full" :options="subjects"
                                optionLabel="name"></Select>
                        </td>
                        <td class=" border-surface-800 p-2">
                            <MultiSelect placeholder="Выберите преподавателя" @change="editLesson(item['ЗНАМ'])"
                                v-model="item['ЗНАМ'].teachers" class="w-full" :options="teachers" optionLabel="name">
                            </MultiSelect>
                        </td>
                        <td class="text-center border-surface-800 p-2">
                            <InputText class="max-w-10" @change="editLesson(item['ЗНАМ'])"
                                v-model="item['ЗНАМ'].building" />
                        </td>

                        <td class="text-center border-surface-800 p-2">
                            <InputText class="max-w-10" @change="editLesson(item['ЗНАМ'])"
                                v-model="item['ЗНАМ'].cabinet" />
                        </td>

                        <td class="border-surface-800 p-2">

                            <Button @click="addLesson('ЗНАМ', item.index, item['ЗНАМ'])" icon="pi pi-plus"
                                v-if="!item['ЗНАМ'].id"></Button>
                            <Button @click="removeLesson(item['ЗНАМ'].id)" icon="pi pi-trash" severity="danger"
                                v-if="item['ЗНАМ'].id"></Button>
                        </td>
                    </tr>

                </template>

                <tr class="border-t border-surface-800">
                    <td class="border border-surface-800 p-2 text-center " rowspan="2">
                        <InputText class="max-w-10" v-model="newLesson.index" />
                    </td>
                    <td class="border-surface-800 p-2"><Select v-model="newLesson['ЧИСЛ'].subject" class="w-full"
                            :options="subjects" optionLabel="name"></Select>
                    </td>
                    <td class=" border-surface-800 p-2">
                        <MultiSelect placeholder="Выберите преподавателя" v-model="newLesson['ЧИСЛ'].teachers"
                            class="w-full" :options="teachers" optionLabel="name">
                        </MultiSelect>
                    </td>
                    <td class="text-center border-surface-800 p-2">
                        <InputText class="max-w-10" v-model="newLesson['ЧИСЛ'].building" />

                    </td>
                    <td class="text-center border-surface-800 p-2">
                        <InputText class="max-w-10" v-model="newLesson['ЧИСЛ'].cabinet" />
                    </td>

                    <td class="border-l-2 text-center border-surface-800 p-2" rowspan="2">
                        <Button @click="addNewLesson()" icon="pi pi-plus"></Button>

                    </td>
                </tr>
                <tr class="border-t border-surface-800">
                    <td class=" border-surface-800 p-2"><Select v-model="newLesson['ЗНАМ'].subject" class="w-full"
                            :options="subjects" optionLabel="name"></Select>
                    </td>
                    <td class=" border-surface-800 p-2">
                        <MultiSelect placeholder="Выберите преподавателя" v-model="newLesson['ЗНАМ'].teachers"
                            :options="teachers" class="w-full" optionLabel="name">
                        </MultiSelect>
                    </td>
                    <td class="text-center border-surface-800 p-2">
                        <InputText class="max-w-10" v-model="newLesson['ЗНАМ'].building" />
                    </td>

                    <td class="text-center border-surface-800 p-2">
                        <InputText class="max-w-10" v-model="newLesson['ЗНАМ'].cabinet" />
                    </td>


                </tr>

            </tbody>

        </table>

    </div>

</template>