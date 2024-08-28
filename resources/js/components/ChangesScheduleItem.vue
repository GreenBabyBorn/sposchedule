<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import { useSubjectsQuery } from '@/queries/subjects';
import { useTeachersQuery } from '@/queries/teachers';
import { useFromMainToChangesSchedule, useStoreSchedule, useStoreScheduleChange } from '@/queries/schedules';
import { useDestroyLesson, useStoreLesson, useUpdateLesson } from '@/queries/lessons';
import { useToast } from 'primevue/usetoast';
import { reactive, ref, toRef } from 'vue';
import { storeToRefs } from 'pinia';
import { useScheduleStore } from '@/stores/schedule';

const toast = useToast();
const props = defineProps({
    group: { required: true, type: Object },
    date: { required: false },
    type: { required: true },
    semester: { required: false, type: Object },
    lessons: { required: true },
    schedule: { required: true, type: Object },
})
const lessons: any = toRef<any>(() => props.lessons)

const { data: subjects } = useSubjectsQuery()
const { data: teachers } = useTeachersQuery()

const { mutateAsync: updateLesson, isPending: isUpdated } = useUpdateLesson()


const { mutateAsync: fromMainToChangesSchedule, data: newChannges } = useFromMainToChangesSchedule()
async function editLesson(item) {
    if (!item.id) return

    if (props.type === 'main') {
        try {
            await fromMainToChangesSchedule({
                id: props.schedule.id, body: {
                    date: props.date,
                }
            })
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
        console.log(item)
        console.log(newChannges.value.data.lessons.find(x => x.index === item.index))
        // item = newChannges.value.data.lessons.find(x => x.index === item.index)
    }
    try {
        await updateLesson({
            id: props.type === 'main' ? newChannges.value.data.lessons.find(x => x.index === item.index).id : item.id,
            body: {
                ...item,
                id: props.type === 'main' ? newChannges.value.data.lessons.find(x => x.index === item.index).id : item.id,
                schedule_id: props.type === 'main' ? newChannges.value.data.lessons.find(x => x.index === item.index).schedule_id : item.schedule_id,
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
    subject: any | null,
    teachers: [] | null,
    building: string | null,
    cabinet: string | null,
}
let newLesson = reactive<LessonWithWeekTypes>({
    index: null,
    subject: null,
    teachers: [],
    building: null,
    cabinet: null,
})

const { mutateAsync: storeSchedule, data: newSchedule } = useStoreScheduleChange()
const { mutateAsync: storeLesson } = useStoreLesson()

async function addNewLesson() {

    const loadedSchedule = props.schedule?.id
    if (!loadedSchedule || props.schedule.type === 'main') {

        try {
            await storeSchedule({
                body: {
                    group_id: props.group.id,
                    semester_id: props.semester.id,
                    type: 'changes',
                    view_mode: 'table',
                    date: props.date,
                }
            })
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message, life: 3000, closable: true });
            return
        }
    }
    try {
        await storeLesson({
            body: {
                ...newLesson,
                teachers: newLesson.teachers,
                index: newLesson.index,
                subject_id: newLesson.subject?.id,
                schedule_id: loadedSchedule && props.schedule.type !== 'main' ? loadedSchedule : newSchedule.value.data.id
            }
        })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
}

const { mutateAsync: destroyLesson, } = useDestroyLesson()
async function removeLesson(id) {
    if (props.type === 'main') {
        try {
            await fromMainToChangesSchedule({
                id: props.schedule.id, body: {
                    date: props.date,
                }
            })
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }

    }
    else {
        try {
            await destroyLesson({ id: id })
        }
        catch (e) {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
            return
        }
    }

}
</script>

<template>
    <div class="schedule-item">
        <div class="mb-2 flex justify-between items-center"> <span
                class="text-2xl text-left font-medium text-surface-800 dark:text-white/80">{{
                    props.group.name }}</span>
            <span :class="{
                'border border-green-400 ': props.type
                    !== 'main'
            }" class="text-sm text-right bg-surface-600 py-1 px-2 rounded-lg text-white/80">{{
                props.type
                    === 'main' ? 'Основное' : 'Изменения' }}</span>
        </div>
        <table class="schedule-table dark:bg-surface-900">

            <thead>
                <tr>
                    <th>
                        <div class="">№</div>
                    </th>
                    <th>
                        <div class="">Предмет / Преподаватели</div>
                    </th>

                    <th>
                        <div class="">Корпус</div>
                    </th>
                    <th>
                        <div class="">Кабинет</div>
                    </th>
                    <th>
                        <div class="">Действия</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template v-for="item in lessons">
                    <tr>
                        <td><span class="text-xl font-medium text-surface-800 dark:text-white/80">
                                {{ item.index }}
                            </span></td>
                        <td>


                            <div v-if="item.subject" class="table-subrow"><Select @change="editLesson(item)"
                                    v-model="item.subject" class="w-full text-left" :options="subjects"
                                    optionLabel="name"></Select>

                            </div>
                            <div class="table-subrow" v-if="item.teachers">
                                <MultiSelect placeholder="Выберите преподавателя" @change="editLesson(item)"
                                    v-model="item.teachers" class="w-full" :options="teachers" optionLabel="name">
                                </MultiSelect>

                            </div>
                        </td>

                        <td>

                            <div class="table-subrow" v-if="item.building">
                                <InputText class="w-full" @change="editLesson(item)" v-model="item.building" />
                            </div>
                        </td>
                        <td>

                            <div class="table-subrow" v-if="item.cabinet">
                                <InputText class="w-full" @change="editLesson(item)" v-model="item.cabinet" />
                            </div>
                        </td>
                        <td>


                            <div class="table-subrow" v-if="item.index">
                                <Button text :disabled="!item.cabinet || !item.building || !item.subject"
                                    icon="pi pi-check" v-if="!item.id"></Button>

                                <Button text @click="removeLesson(item.id)" icon="pi pi-trash" severity="danger"
                                    v-if="item.id"></Button>
                            </div>

                        </td>
                    </tr>
                </template>
                <tr>
                    <td>
                        <InputText size="small" class="w-full text-center" v-model="newLesson.index" />
                    </td>
                    <td>
                        <div class="table-subrow"><Select placeholder="Предмет" editable v-model="newLesson.subject"
                                class="w-full text-left" :options="subjects" optionLabel="name"></Select></div>
                        <div class="table-subrow">
                            <MultiSelect placeholder="Преподаватели" v-model="newLesson.teachers" class="w-full"
                                :options="teachers" optionLabel="name">
                            </MultiSelect>
                        </div>
                    </td>

                    <td>

                        <div class="table-subrow">
                            <InputText size="small" class="w-full text-center" v-model="newLesson.building" />
                        </div>
                    </td>
                    <td>

                        <div class="table-subrow">
                            <InputText size="small" class="w-full text-center" v-model="newLesson.cabinet" />
                        </div>
                    </td>
                    <td>
                        <div class="table-subrow"><Button @click="addNewLesson()" text icon="pi pi-plus"></Button>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>



    </div>

</template>

<style scoped>
.schedule-item {
    width: 450px;
}

.schedule-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 0.8rem;
    /* Чтобы все столбцы имели фиксированную ширину */
}

.schedule-table th,
.schedule-table td {
    border: 1px solid var(--p-surface-600);
    /* padding: 5px; */
    text-align: center;
}

.schedule-table th {}

.schedule-table th>div {}

/* Подстроки в ячейках */
.table-subrow {
    display: flex;
    align-items: center;
    justify-content: center;
    /* padding: 5px; */
}

.schedule-table th:first-child,
.schedule-table td:first-child {
    width: 10%;
}

.schedule-table th:nth-child(2),
.schedule-table td:nth-child(2) {
    width: 40%;
}

.schedule-table th:nth-child(3),
.schedule-table td:nth-child(3) {
    width: 15%;
}

.schedule-table th:nth-child(4),
.schedule-table td:nth-child(4) {
    width: 15%;
}

.schedule-table th:nth-child(5),
.schedule-table td:nth-child(5) {
    width: 20%;
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