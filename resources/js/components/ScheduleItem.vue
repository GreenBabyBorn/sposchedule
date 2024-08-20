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


const newLesson = reactive({
    'ЧИСЛ':
    {
        subject: null,
        teachers: [],
        building: null,
        cabinet: null,
        index: null,
    },
    'ЗНАМ': {
        subject: null,
        teachers: [],
        building: null,
        cabinet: null,
        index: null,
    }
})

const { mutateAsync: storeSchedule, data: newSchedule } = useStoreSchedule()
const { mutateAsync: storeLesson } = useStoreLesson()

async function addLesson() {
    console.log(props.item)
    // if (!newLesson.ЧИСЛ.subject) {
    //     toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Выберите предмет', life: 3000, closable: true });
    //     return
    // }
    // if (!newLesson.ЧИСЛ.teachers.length) {
    //     toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Выберите преподавателя', life: 3000, closable: true });
    //     return
    // }
    try {
        if (newLesson.ЧИСЛ.subject) {
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
                    ...newLesson.ЧИСЛ,
                    subject_id: newLesson.ЧИСЛ.subject.id,
                    schedule_id: newSchedule.value.data.id
                }
            })
        }

    }
    catch (e) {

    }
}

const { mutateAsync: destroyLesson } = useDestroyLesson()

async function removeLesson(id) {
    try {
        await destroyLesson({ id: id })
    }
    catch (e) {

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
                    <th class="border border-surface-800 p-2 font-medium">Предметы ЧИСЛ|ЗНАМ</th>
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
                                v-model="item['ЧИСЛ'].subject" class="" :options="subjects" optionLabel="name"></Select>
                        </td>
                        <td class=" border-surface-800 p-2">
                            <MultiSelect placeholder="Выберите преподавателя" @change="editLesson(item['ЧИСЛ'])"
                                v-model="item['ЧИСЛ'].teachers" class="" :options="teachers" optionLabel="name">
                            </MultiSelect>
                        </td>
                        <td class=" border-surface-800 p-2">
                            <InputText @change="editLesson(item['ЧИСЛ'])" v-model="item['ЧИСЛ'].building" />

                        </td>
                        <td class=" border-surface-800 p-2">
                            <InputText @change="editLesson(item['ЧИСЛ'])" v-model="item['ЧИСЛ'].cabinet" />
                        </td>

                        <td class="border-surface-800 p-2">
                            <Button icon="pi pi-plus" v-if="!item['ЧИСЛ'].id"></Button>
                            <Button @click="removeLesson(item['ЧИСЛ'].id)" icon="pi pi-trash" severity="danger"
                                v-if="item['ЧИСЛ'].id"></Button>
                        </td>
                    </tr>
                    <tr class="border-t border-surface-800">
                        <td class=" border-surface-800 p-2"><Select @change="editLesson(item['ЗНАМ'])"
                                v-model="item['ЗНАМ'].subject" class="" :options="subjects" optionLabel="name"></Select>
                        </td>
                        <td class=" border-surface-800 p-2">
                            <MultiSelect placeholder="Выберите преподавателя" @change="editLesson(item['ЗНАМ'])"
                                v-model="item['ЗНАМ'].teachers" :options="teachers" optionLabel="name">
                            </MultiSelect>
                        </td>
                        <td class=" border-surface-800 p-2">
                            <InputText @change="editLesson(item['ЗНАМ'])" v-model="item['ЗНАМ'].building" />
                        </td>

                        <td class="border-surface-800 p-2">
                            <InputText @change="editLesson(item['ЗНАМ'])" v-model="item['ЗНАМ'].cabinet" />
                        </td>

                        <td class="border-surface-800 p-2">
                            <Button icon="pi pi-plus" v-if="!item['ЗНАМ'].id"></Button>
                            <Button @click="removeLesson(item['ЗНАМ'].id)" icon="pi pi-trash" severity="danger"
                                v-if="item['ЗНАМ'].id"></Button>
                        </td>
                    </tr>

                </template>





                <tr class="border-t border-surface-800">
                    <td class="border border-surface-800 p-2 text-center " rowspan="2">
                        <InputText class="max-w-10" v-model="newLesson['ЧИСЛ'].index" />
                    </td>
                    <td class="border-surface-800 p-2"><Select v-model="newLesson['ЧИСЛ'].subject" class=""
                            :options="subjects" optionLabel="name"></Select>
                    </td>
                    <td class=" border-surface-800 p-2">
                        <MultiSelect placeholder="Выберите преподавателя" v-model="newLesson['ЧИСЛ'].teachers" class=""
                            :options="teachers" optionLabel="name">
                        </MultiSelect>
                    </td>
                    <td class="text-center border-surface-800 p-2">
                        <InputText class="max-w-10" v-model="newLesson['ЧИСЛ'].building" />

                    </td>
                    <td class="text-center border-surface-800 p-2">
                        <InputText class="max-w-10" v-model="newLesson['ЧИСЛ'].cabinet" />
                    </td>

                    <td class="text-center border-surface-800 p-2" rowspan="2">
                        <Button @click="addLesson()" icon="pi pi-plus"></Button>

                    </td>
                </tr>
                <tr class="border-t border-surface-800">
                    <td class=" border-surface-800 p-2"><Select v-model="newLesson['ЗНАМ'].subject" class=""
                            :options="subjects" optionLabel="name"></Select>
                    </td>
                    <td class=" border-surface-800 p-2">
                        <MultiSelect placeholder="Выберите преподавателя" v-model="newLesson['ЗНАМ'].teachers"
                            :options="teachers" optionLabel="name">
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

    <!-- <div class="">
        <div class="table-container">
            <div class="table-header">
                №
            </div>
            <div class="table-header">
                Предмет
            </div>
            <div class="table-header">
                Преподаватели
            </div>
            <div class="table-header">
                Корпус
            </div>
            <div class="table-header">
                Кабинет
            </div>
            <div class="table-header">
                Действия
            </div>
            <div class="table-row num">
                <div class="table-cell">1</div>
            </div>
            <div class="table-row">
                <div class="table-cell">123</div>
            </div>
            <div class="table-row">
                <div class="table-cell">123</div>
            </div>
            <div class="table-row">
                <div class="table-cell">123</div>
            </div>
            <div class="table-row">
                <div class="table-cell">123</div>
            </div>
            <div class="table-row">
                <div class="table-cell">123</div>
            </div>
        </div>
    </div> -->
</template>

<style>
.num {
    grid-row: 1/3;
}

.table-container {
    display: grid;
    grid-template-columns: 2rem 2fr 2fr max-content max-content 1fr;
    /* 4 колонки одинаковой ширины */
    gap: 10px;
    width: 100%;
    /* max-width: 800px; */
    margin: 0 auto;
}

.table-header {
    text-align: center;
    padding: 10px;
    font-weight: bold;
}

.table-cell {
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd;
}
</style>