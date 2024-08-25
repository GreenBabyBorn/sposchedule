<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import { useSubjectsQuery } from '@/queries/subjects';
import { useTeachersQuery } from '@/queries/teachers';
import { useStoreSchedule } from '@/queries/schedules';
import { useDestroyLesson, useStoreLesson, useUpdateLesson } from '@/queries/lessons';
import { useToast } from 'primevue/usetoast';
import { reactive, ref, toRef } from 'vue';
import { storeToRefs } from 'pinia';
import { useScheduleStore } from '@/stores/schedule';


const toast = useToast();
const props = defineProps({
    weekDay: { type: String, required: true },
    group: { required: true },
    semester: { required: true },
    item: { required: true },
})

const items: any = toRef<any>(() => props.item)

const { data: subjects } = useSubjectsQuery()
const { data: teachers } = useTeachersQuery()

const { mutateAsync: updateLesson, isPending: isUpdated } = useUpdateLesson()
async function editLesson(item) {
    if (!item.id) return

    try {
        await updateLesson({
            id: item.id,
            body: {
                ...item
            }
        })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
}

type LessonWithWeekTypes = {
    index: number,
    'ЧИСЛ': {
        subject: any | null,
        teachers: [] | null,
        building: string | null,
        cabinet: string | null,

    },
    'ЗНАМ'?: {
        subject: any | null,
        teachers: [] | null,
        building: string | null,
        cabinet: string | null,
    },
}

let newLesson = reactive<LessonWithWeekTypes>({
    index: null,
    'ЧИСЛ':
    {
        subject: null,
        teachers: [],
        building: null,
        cabinet: null,

    },
})

const { mutateAsync: storeSchedule, data: newSchedule } = useStoreSchedule()
const { mutateAsync: storeLesson } = useStoreLesson()

async function addNewLesson() {
    // @ts-ignore
    const loadedSchedule = props.item.find(item => item.schedule_id)

    if (!loadedSchedule?.schedule_id) {
        try {
            await storeSchedule({
                body: {
                    // @ts-ignore
                    group_id: props.group.id,
                    // @ts-ignore
                    semester_id: props.semester.id,
                    type: 'main',
                    week_day: props.weekDay,
                    view_mode: 'table'
                }
            })
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }
    // console.log(newLesson['ЧИСЛ']?.teachers, newLesson['ЗНАМ']?.teachers)
    try {
        await storeLesson({
            body: {
                ...newLesson['ЧИСЛ'],
                teachers: newLesson['ЧИСЛ'].teachers,
                week_type: addRowAddNewLessonState.value ? 'ЧИСЛ' : null,
                index: newLesson.index,
                subject_id: newLesson['ЧИСЛ'].subject?.id,
                schedule_id: loadedSchedule?.schedule_id ? loadedSchedule?.schedule_id : newSchedule.value.data.id
            }
        })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
    if (addRowAddNewLessonState.value && newLesson?.['ЗНАМ']?.subject && newLesson.index) {
        try {
            await storeLesson({
                body: {
                    ...newLesson['ЗНАМ'],
                    teachers: newLesson['ЗНАМ'].teachers,
                    week_type: 'ЗНАМ',
                    index: newLesson.index,
                    subject_id: newLesson['ЗНАМ'].subject?.id,
                    schedule_id: loadedSchedule?.schedule_id ? loadedSchedule?.schedule_id : newSchedule.value.data.id
                }
            })
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }
}

async function addLesson(weekType, index, schedule_id, item) {
    console.log(item)
    if (weekType == 'ЧИСЛ' && Object.keys(item).length !== 0) {
        try {
            // await storeSchedule({
            //     body: {
            //         // @ts-ignore
            //         group_id: props.group.id,
            //         // @ts-ignore
            //         semester_id: props.semester.id,
            //         type: 'main',
            //         // week_type: 'ЧИСЛ',
            //         week_day: props.weekDay,
            //         view_mode: 'table'
            //     }
            // })
            await storeLesson({
                body: {
                    ...item,
                    index: index,
                    week_type: 'ЧИСЛ',
                    subject_id: item.subject.id,
                    schedule_id: schedule_id
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
            // await storeSchedule({
            //     body: {
            //         // @ts-ignore
            //         group_id: props.group.id,
            //         // @ts-ignore
            //         semester_id: props.semester.id,
            //         type: 'main',
            //         // week_type: 'ЗНАМ',
            //         week_day: props.weekDay,
            //         view_mode: 'table'
            //     }
            // })
            await storeLesson({
                body: {
                    ...item,
                    index: index,
                    week_type: 'ЗНАМ',
                    subject_id: item.subject.id,
                    schedule_id: schedule_id
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


const addRowAddNewLessonState = ref(false)

function addRowAddNewLesson() {
    addRowAddNewLessonState.value = !addRowAddNewLessonState.value
    newLesson.ЗНАМ = addRowAddNewLessonState.value ? {
        subject: null,
        teachers: [],
        building: null,
        cabinet: null,
    } : null
}


</script>

<template>
    <div class="relative overflow-x-auto">
        <div class="">
            <table class="schedule-table dark:bg-surface-900">
                <caption class="text-2xl font-medium text-surface-200 mb-2">{{ props.weekDay }}</caption>
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Предмет</th>
                        <th>Преподаватели</th>
                        <th>Корпус</th>
                        <th>Кабинет</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="item in items">
                        <tr>
                            <td><span class="text-xl font-medium text-surface-200">
                                    {{ item.index }}
                                </span></td>
                            <td>
                                <div v-if="item['ЧИСЛ']" class="table-subrow"><Select @change="editLesson(item['ЧИСЛ'])"
                                        v-model="item['ЧИСЛ'].subject" class="w-full text-left" :options="subjects"
                                        optionLabel="name"></Select></div>

                                <div class="table-subrow" v-if="item.lesson"><Select @change="editLesson(item.lesson)"
                                        v-model="item.lesson.subject" class="w-full text-left" :options="subjects"
                                        optionLabel="name"></Select></div>

                                <div v-if="item['ЗНАМ']" class="table-subrow"><Select @change="editLesson(item['ЗНАМ'])"
                                        v-model="item['ЗНАМ'].subject" class="w-full text-left" :options="subjects"
                                        optionLabel="name"></Select></div>
                            </td>
                            <td>
                                <div class="table-subrow" v-if="item['ЧИСЛ']">
                                    <MultiSelect placeholder="Выберите преподавателя" @change="editLesson(item['ЧИСЛ'])"
                                        v-model="item['ЧИСЛ'].teachers" class="w-full" :options="teachers"
                                        optionLabel="name">
                                    </MultiSelect>
                                </div>
                                <div class="table-subrow" v-if="item.lesson">
                                    <MultiSelect placeholder="Выберите преподавателя" @change="editLesson(item.lesson)"
                                        v-model="item.lesson.teachers" class="w-full" :options="teachers"
                                        optionLabel="name">
                                    </MultiSelect>
                                </div>
                                <div class="table-subrow" v-if="item['ЗНАМ']">
                                    <MultiSelect placeholder="Выберите преподавателя" @change="editLesson(item['ЗНАМ'])"
                                        v-model="item['ЗНАМ'].teachers" class="w-full" :options="teachers"
                                        optionLabel="name">
                                    </MultiSelect>
                                </div>
                            </td>
                            <td>
                                <div class="table-subrow" v-if="item['ЧИСЛ']">
                                    <InputText class="w-full" @change="editLesson(item['ЧИСЛ'])"
                                        v-model="item['ЧИСЛ'].building" />
                                </div>
                                <div class="table-subrow" v-if="item.lesson">
                                    <InputText class="w-full" @change="editLesson(item.lesson)"
                                        v-model="item.lesson.building" />
                                </div>
                                <div class="table-subrow" v-if="item['ЗНАМ']">
                                    <InputText class="w-full" @change="editLesson(item['ЗНАМ'])"
                                        v-model="item['ЗНАМ'].building" />
                                </div>
                            </td>
                            <td>
                                <div class="table-subrow" v-if="item['ЧИСЛ']">
                                    <InputText class="w-full" @change="editLesson(item['ЧИСЛ'])"
                                        v-model="item['ЧИСЛ'].cabinet" />
                                </div>
                                <div class="table-subrow" v-if="item.lesson">
                                    <InputText class="w-full" @change="editLesson(item.lesson)"
                                        v-model="item.lesson.cabinet" />
                                </div>
                                <div class="table-subrow" v-if="item['ЗНАМ']">
                                    <InputText class="w-full" @change="editLesson(item['ЗНАМ'])"
                                        v-model="item['ЗНАМ'].cabinet" />
                                </div>
                            </td>
                            <td>
                                <div class="table-subrow" v-if="item.lesson">

                                    <!-- <Button v-if="!item['ЗНАМ']" @click="addRowLesson(item['ЧИСЛ'], 'ЗНАМ')" text
                                        :icon="`pi pi-percentage`"></Button> -->
                                    <Button text @click="removeLesson(item.lesson.id)" icon="pi pi-trash"
                                        severity="danger"></Button>
                                </div>
                                <div class="table-subrow" v-if="item['ЧИСЛ']">
                                    <Button text
                                        :disabled="!item['ЧИСЛ'].cabinet || !item['ЧИСЛ'].building || !item['ЧИСЛ'].subject"
                                        @click="addLesson('ЧИСЛ', item.index, item.schedule_id, item['ЧИСЛ'])"
                                        icon="pi pi-check" v-if="!item['ЧИСЛ'].id"></Button>

                                    <Button text @click="removeLesson(item['ЧИСЛ'].id)" icon="pi pi-trash"
                                        severity="danger" v-if="item['ЧИСЛ'].id"></Button>
                                </div>

                                <div class="table-subrow" v-if="item['ЗНАМ']">
                                    <Button text
                                        :disabled="!item['ЗНАМ'].cabinet || !item['ЗНАМ'].building || !item['ЗНАМ'].subject"
                                        @click="addLesson('ЗНАМ', item.index, item.schedule_id, item['ЗНАМ'])"
                                        icon="pi pi-check" v-if="!item['ЗНАМ'].id"></Button>

                                    <Button text @click="removeLesson(item['ЗНАМ'].id)" icon="pi pi-trash"
                                        severity="danger" v-if="item['ЗНАМ'].id"></Button>
                                </div>

                            </td>
                        </tr>
                    </template>
                    <tr>
                        <td>
                            <InputText class="min-w-10 w-full" v-model="newLesson.index" />
                        </td>
                        <td>
                            <div class="table-subrow"><Select editable v-model="newLesson['ЧИСЛ'].subject"
                                    class="w-full text-left" :options="subjects" optionLabel="name"></Select></div>
                            <div v-if="newLesson['ЗНАМ']" class="table-subrow"><Select editable
                                    v-model="newLesson['ЗНАМ'].subject" class="w-full text-left" :options="subjects"
                                    optionLabel="name"></Select></div>
                        </td>
                        <td>
                            <div class="table-subrow">
                                <MultiSelect placeholder="Выберите преподавателя" v-model="newLesson['ЧИСЛ'].teachers"
                                    class="w-full" :options="teachers" optionLabel="name">
                                </MultiSelect>
                            </div>
                            <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                                <MultiSelect placeholder="Выберите преподавателя" v-model="newLesson['ЗНАМ'].teachers"
                                    class="w-full" :options="teachers" optionLabel="name">
                                </MultiSelect>
                            </div>
                        </td>
                        <td>
                            <div class="table-subrow">
                                <InputText class="w-full" v-model="newLesson['ЧИСЛ'].building" />
                            </div>
                            <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                                <InputText class="w-full" v-model="newLesson['ЗНАМ'].building" />
                            </div>
                        </td>
                        <td>
                            <div class="table-subrow">
                                <InputText class="w-full" v-model="newLesson['ЧИСЛ'].cabinet" />
                            </div>
                            <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                                <InputText class="w-full" v-model="newLesson['ЗНАМ'].cabinet" />
                            </div>
                        </td>
                        <td>
                            <div class="table-subrow"><Button @click="addNewLesson()" text
                                    icon="pi pi-plus"></Button><Button @click="addRowAddNewLesson()" text
                                    :title="`${addRowAddNewLessonState ? 'Не дробное' : 'Дробное'} `"
                                    :icon="`pi ${addRowAddNewLessonState ? 'pi-arrows-h' : 'pi-percentage'} `"></Button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>

    </div>

</template>

<style scoped>
.schedule-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    /* Чтобы все столбцы имели фиксированную ширину */
}

.schedule-table th,
.schedule-table td {
    border: 1px solid var(--p-surface-600);
    padding: 10px;
    text-align: center;
}

.schedule-table th {

    font-weight: bold;
}

/* Подстроки в ячейках */
.table-subrow {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
}

.schedule-table th:first-child,
.schedule-table td:first-child {
    width: 5%;
}


.schedule-table th:nth-child(4),
.schedule-table td:nth-child(4) {
    width: 10%;
}

.schedule-table th:nth-child(5),
.schedule-table td:nth-child(5) {
    width: 10%;
}

.schedule-table th:nth-child(6),
.schedule-table td:nth-child(6) {
    width: 12%;
}

tbody tr {
    border-bottom: 2px var(--p-surface-600) solid;
    /* background: rgba(128, 128, 128, 0.243); */
}

tbody tr:last-child {
    border-bottom: none;

}

tbody>tr:last-child {

    /* border-top: 2px rgb(0, 153, 255) solid; */
    background: rgba(255, 255, 255, 0.062);
}
</style>