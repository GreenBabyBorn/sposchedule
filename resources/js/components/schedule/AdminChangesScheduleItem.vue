<script setup lang="ts">
import MultiSelect from 'primevue/multiselect';

import Select from 'primevue/select';

import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';


// import { useSubjectsQuery } from '@/queries/subjects';
// import { useTeachersQuery } from '@/queries/teachers';
import { useFromMainToChangesSchedule, useStoreSchedule, useStoreScheduleChange, useUpdateSchedule } from '@/queries/schedules';
import { useDestroyLesson, useStoreLesson, useUpdateLesson } from '@/queries/lessons';
import { useToast } from 'primevue/usetoast';
import { onUpdated, reactive, ref, toRef, watch } from 'vue';
import ToggleButton from 'primevue/togglebutton';


import AdminChangesScheduleItemRow from './AdminChangesScheduleItemRow.vue';

const toast = useToast();
const props = defineProps({
    group: { required: false, type: [Object, null], default: null },
    date: { required: false },
    week_type: { required: false },
    type: { required: true },
    semester: { required: false, type: Object },
    lessons: { required: true },
    schedule: { required: true, type: Object },
    published: { required: false, type: Boolean },
    teachers: { required: true },
    subjects: { required: true }
})
const lessons: any = toRef<any>(() => props.lessons)
const teachers: any = toRef<any>(() => props.teachers)
const subjects: any = toRef<any>(() => props.subjects)

// const {  teachers, subjects } = toRef<any>(props).value
// const lessons: any = ref(props.lessons)
// const teachers: any = ref(props.teachers)
// const subjects: any = ref(props.subjects)

const published = ref(props.published)

watch(() => props.published, (newValue) => {
    published.value = newValue
})

const { mutateAsync: updateLesson, isPending: isUpdated } = useUpdateLesson()

const { mutateAsync: fromMainToChangesSchedule, data: newChanges } = useFromMainToChangesSchedule()
async function editLesson(item) {
    if (!item.id) return
    if (!item.message == (!item.subject)) return
    // console.log(item)
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
    try {
        await updateLesson({
            id: props.type === 'main' ? newChanges.value.data.lessons.find(x => x.index === item.index).id : item.id,
            body: {
                ...item,
                subject_id: item.subject?.id,
                id: props.type === 'main' ? newChanges.value.data.lessons.find(x => x.index === item.index).id : item.id,
                schedule_id: props.type === 'main' ? newChanges.value.data.lessons.find(x => x.index === item.index).schedule_id : item.schedule_id,
            }
        })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message, life: 3000, closable: true });
        return
    }
}

type LessonWithWeekTypes = {
    index: number,
    subject: any | null,
    teachers: [] | null,
    building: string | null,
    cabinet: string | null,
    message?: string | null,
}
let newLesson = reactive<LessonWithWeekTypes>({
    index: null,
    subject: null,
    teachers: [],
    building: null,
    cabinet: null,
    message: null
})

const { mutateAsync: storeSchedule, data: newSchedule } = useStoreScheduleChange()
const { mutateAsync: storeLesson } = useStoreLesson()

async function addNewLesson() {
    const loadedSchedule = toRef(() => props.schedule).value.id;

    // Если тип расписания 'main', конвертируем его в изменения
    if (props.schedule.type === 'main') {
        try {
            await fromMainToChangesSchedule({
                id: props.schedule.id,
                body: {
                    date: props.date,
                },
            });
        } catch (e) {
            toast.add({
                severity: 'error',
                summary: 'Ошибка',
                detail: e?.response?.data?.message || 'Не удалось конвертировать основное расписание в изменения.',
                life: 3000,
                closable: true,
            });
            return;
        }
    }

    // Если расписание не загружено и тип 'changes', создаем новое расписание
    if (!loadedSchedule) {
        try {
            await storeSchedule({
                body: {
                    group_id: props.group.id,
                    semester_id: props.semester.id,
                    type: 'changes',
                    // view_mode: 'table',
                    date: props.date,
                },
            });
        } catch (e) {
            toast.add({
                severity: 'error',
                summary: 'Ошибка',
                detail: e?.response?.data?.message || 'Не удалось сохранить расписание.',
                life: 3000,
                closable: true,
            });
            return;
        }
    }

    // Определяем правильный ID для использования в storeLesson
    let scheduleId;
    if (loadedSchedule && props.schedule.type !== 'main') {
        scheduleId = loadedSchedule;
    } else if (props.schedule.type === 'main') {
        scheduleId = newChanges.value?.data?.id; // Убедитесь, что newChanges имеет значение перед доступом к его свойствам
    } else {
        scheduleId = newSchedule.value?.data?.id; // Тоже необходимо проверить наличие значения
    }
    // console.log(scheduleId, newSchedule.value)
    try {
        await storeLesson({
            body: {
                ...newLesson,
                teachers: newLesson.teachers,
                index: newLesson.index,
                subject_id: newLesson.subject?.id,
                schedule_id: scheduleId,
            },
        });
        newLesson = reactive<LessonWithWeekTypes>({
            index: (Number(newLesson.index) + 1),
            subject: null,
            teachers: [],
            building: newLesson.building,
            cabinet: null,
            message: null
        })
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Ошибка',
            detail: e?.response?.data?.message || 'Не удалось сохранить пару.',
            life: 3000,
            closable: true,
        });
        return;
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

const { mutateAsync: updateChangesSchedule } = useUpdateSchedule()
async function handlePublished() {
    try {
        await updateChangesSchedule({
            id: props.schedule.id,

            body: {
                published: published.value,
            }
        })
    }
    catch (e) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response.data.message, life: 3000, closable: true });
        return
    }
}

const hideAddNewLesson = ref(false)

const newLessonMessageState = ref(false)
function handlenewLessonMessage() {
    newLessonMessageState.value = !newLessonMessageState.value
    if (newLessonMessageState.value) {
        newLesson = reactive<LessonWithWeekTypes>({
            index: newLesson.index,
            subject: null,
            teachers: [],
            building: null,
            cabinet: null,
        })
    }
}
const isEdit = ref(false)
</script>

<template>
    <div class="schedule-item">
        <div class="p-2 dark:bg-surface-800  flex flex-wrap  justify-between items-center">

            <!-- <button class="pi pi-pen-to-square"></button> -->
            <div class="flex items-center gap-2">
                <span class="text-xl text-left font-medium text-surface-800 dark:text-white/80">
                    {{ props?.group?.name }}
                </span>

                <Button title="Редактировать" severity="secondary" @click="isEdit = !isEdit" text
                    icon="pi pi-pen-to-square"></Button>
            </div>


            <span>{{ props?.week_type }}</span>
            <div v-if="props.type !== 'main'" class="">
                <ToggleButton @change="handlePublished" :disabled="!lessons" v-model="published" class="text-sm" fluid
                    onLabel="Снять с публикации" offLabel="Опубликовать" />
            </div>
            <span :class="{
                'text-green-400 ': props?.type
                    !== 'main',
                'text-surface-400 ': props?.type
                    === 'main'
            }" class="text-sm text-right  py-1 px-2 rounded-lg ">{{
                props?.type
                    === 'main' ? 'Основное' : 'Изменения' }}</span>
        </div>
        <table class="schedule-table dark:bg-surface-900">
            <thead v-show="lessons?.length > 0 || hideAddNewLesson">
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
                    <th v-if="isEdit">
                        <div class="">Действия</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template :key="lesson.id" v-for="lesson in lessons">
                    <AdminChangesScheduleItemRow :isEdit="isEdit" :subjects="subjects" :teachers="teachers"
                        @removeLesson="removeLesson" @editLesson="editLesson" :lesson="lesson" />
                </template>



                <tr v-if="hideAddNewLesson && isEdit">
                    <td>
                        <InputText size="small" class="w-full text-center" v-model="newLesson.index" />
                    </td>

                    <td v-show="newLessonMessageState" colspan="3/1">
                        <div class="table-subrow">
                            <Textarea v-model="newLesson.message" placeholder="Введите сообщение для группы"
                                class="w-full" />
                        </div>
                    </td>
                    <td v-show="!newLessonMessageState">
                        <div class="table-subrow"><Select :autoFilterFocus="true" filter placeholder="Предмет"
                                v-model="newLesson.subject" class="w-full text-left" :options="subjects"
                                optionLabel="name" />
                        </div>
                        <div class="table-subrow">
                            <MultiSelect :autoFilterFocus="true" filter placeholder="Преподаватели"
                                v-model="newLesson.teachers" class="w-full" :options="teachers" optionLabel="name" />

                        </div>
                    </td>

                    <td v-show="!newLessonMessageState">

                        <div class="table-subrow">
                            <InputText size="small" class="w-full text-center" v-model="newLesson.building" />
                        </div>
                    </td>
                    <td v-show="!newLessonMessageState">
                        <div class="table-subrow">
                            <InputText size="small" class="w-full text-center" v-model="newLesson.cabinet" />
                        </div>
                    </td>
                    <td>
                        <div class="table-subrow">
                            <Button
                                :disabled="!newLessonMessageState && (!newLesson.index || !newLesson.subject) || newLessonMessageState && !newLesson.message"
                                @click="addNewLesson()" text icon="pi pi-save" />
                            <Button @click="handlenewLessonMessage"
                                :title="`${newLessonMessageState ? 'Переключиться на обычную пару' : 'Переключиться на комментарий'}`"
                                text :icon="`pi ${newLessonMessageState ? 'pi-table' : 'pi-comment'}`" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-if="isEdit" class="mt-2 flex items-center justify-center">
            <Button label="Новая пара" title="Открыть форму для добавления пары" size="small" outlined
                severity="secondary" class="w-full text-surface-800 dark:text-white/80"
                @click="hideAddNewLesson = !hideAddNewLesson"
                :icon="!hideAddNewLesson ? 'pi pi-angle-down' : 'pi pi-angle-up'"></Button>
        </div>
    </div>
</template>

<style scoped>
.schedule-item {
    /* width: 450px; */
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
    /* background: rgba(255, 255, 255, 0.062); */
}
</style>