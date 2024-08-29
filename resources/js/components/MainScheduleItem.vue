<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import MultiSelect from 'primevue/multiselect';
import Select from 'primevue/select';
import { useSubjectsQuery } from '@/queries/subjects';
import { useTeachersQuery } from '@/queries/teachers';
import { useStoreSchedule } from '@/queries/schedules';
import { useDestroyLesson, useStoreLesson, useUpdateLesson } from '@/queries/lessons';
import { useToast } from 'primevue/usetoast';
import { reactive, ref, toRef } from 'vue';

const toast = useToast();

const props = defineProps({
    weekDay: { type: String, required: true },
    group: { required: true, type: Object },
    semester: { required: true, type: Object },
    item: { required: true, type: [Object] },
});

// Применение toRef для индивидуальных props
const group = toRef(props, 'group');
const semester = toRef(props, 'semester');
const weekDay = toRef(props, 'weekDay');
const items = toRef(props, 'item');

const { data: subjects } = useSubjectsQuery();
const { data: teachers } = useTeachersQuery();

const { mutateAsync: updateLesson, isPending: isUpdated } = useUpdateLesson();
async function editLesson(item) {
    if (!item.id) return;

    try {
        await updateLesson({
            id: item.id,
            body: {
                ...item,
            },
        });
    } catch (e) {
        showError(e);
    }
}

const addRowAddNewLessonState = ref(false);

function addRowAddNewLesson() {
    addRowAddNewLessonState.value = !addRowAddNewLessonState.value;
    newLesson.ЗНАМ = addRowAddNewLessonState.value
        ? {
            subject: null,
            teachers: [],
            building: null,
            cabinet: null,
        }
        : null;
}

type LessonWithWeekTypes = {
    index: number;
    ЧИСЛ: {
        subject: any | null;
        teachers: [] | null;
        building: string | null;
        cabinet: string | null;
    };
    ЗНАМ?: {
        subject: any | null;
        teachers: [] | null;
        building: string | null;
        cabinet: string | null;
    };
};

let newLesson = reactive<LessonWithWeekTypes>({
    index: null,
    ЧИСЛ: {
        subject: null,
        teachers: [],
        building: null,
        cabinet: null,
    },
});

const { mutateAsync: storeSchedule, data: newSchedule } = useStoreSchedule();
const { mutateAsync: storeLesson } = useStoreLesson();
const { mutateAsync: destroyLesson } = useDestroyLesson();

async function addOrUpdateSchedule() {
    const loadedSchedule = items.value.find((item) => item.schedule_id);

    if (!loadedSchedule?.schedule_id) {
        try {
            await storeSchedule({
                body: {
                    group_id: group.value.id,
                    semester_id: semester.value.id,
                    type: 'main',
                    week_day: weekDay.value,
                    view_mode: 'table',
                },
            });
        } catch (e) {
            showError(e);
            return;
        }
    }

    return loadedSchedule?.schedule_id || newSchedule.value.data.id;
}

async function addNewLesson() {
    const schedule_id = await addOrUpdateSchedule();
    if (!schedule_id) return;

    // Создаем первый урок 'ЧИСЛ'
    if (addRowAddNewLessonState.value) {

        await createLesson('ЧИСЛ', schedule_id);
    }
    else {
        await createLesson('', schedule_id);
    }

    // Если 'ЗНАМ' также добавляется
    if (addRowAddNewLessonState.value && newLesson.ЗНАМ?.subject && newLesson.index) {
        await createLesson('ЗНАМ', schedule_id);
    }
}

async function createLesson(weekType, schedule_id) {
    const lessonData = weekType === 'ЧИСЛ' || weekType === '' ? newLesson['ЧИСЛ'] : newLesson['ЗНАМ'];
    if (!lessonData || Object.keys(lessonData).length === 0) {
        showToast('Ошибка', 'Недозаполненно');
        return;
    }

    try {
        await storeLesson({
            body: {
                ...lessonData,
                teachers: lessonData.teachers,
                week_type: weekType,
                index: newLesson.index,
                subject_id: lessonData.subject?.id,
                schedule_id: schedule_id,
            },
        });
    } catch (e) {
        showError(e);
    }
}

async function removeLesson(id) {
    try {
        await destroyLesson({ id });
    } catch (e) {
        showError(e);
    }
}

function showError(e) {
    toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response?.data.message || 'Произошла ошибка',
        life: 3000,
        closable: true,
    });
}

function showToast(summary, detail) {
    toast.add({
        severity: 'error',
        summary: summary,
        detail: detail,
        life: 3000,
        closable: true,
    });
}

</script>

<template>
    <div class="relative overflow-x-auto">
        <div class="">
            <table class="schedule-table dark:bg-surface-900">
                <caption class="text-2xl font-medium  mb-2">{{ props.weekDay }}</caption>
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
                            <td><span class="text-xl font-medium ">
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
                                    <InputText class="w-full text-center" @change="editLesson(item['ЧИСЛ'])"
                                        v-model="item['ЧИСЛ'].building" />
                                </div>
                                <div class="table-subrow" v-if="item.lesson">
                                    <InputText class="w-full text-center" @change="editLesson(item.lesson)"
                                        v-model="item.lesson.building" />
                                </div>
                                <div class="table-subrow" v-if="item['ЗНАМ']">
                                    <InputText class="w-full text-center" @change="editLesson(item['ЗНАМ'])"
                                        v-model="item['ЗНАМ'].building" />
                                </div>
                            </td>
                            <td>
                                <div class="table-subrow" v-if="item['ЧИСЛ']">
                                    <InputText class="w-full text-center" @change="editLesson(item['ЧИСЛ'])"
                                        v-model="item['ЧИСЛ'].cabinet" />
                                </div>
                                <div class="table-subrow" v-if="item.lesson">
                                    <InputText class="w-full text-center" @change="editLesson(item.lesson)"
                                        v-model="item.lesson.cabinet" />
                                </div>
                                <div class="table-subrow" v-if="item['ЗНАМ']">
                                    <InputText class="w-full text-center" @change="editLesson(item['ЗНАМ'])"
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
                                        @click="createLesson('ЧИСЛ', item.schedule_id)" icon="pi pi-check"
                                        v-if="!item['ЧИСЛ'].id"></Button>

                                    <Button text @click="removeLesson(item['ЧИСЛ'].id)" icon="pi pi-trash"
                                        severity="danger" v-if="item['ЧИСЛ'].id"></Button>
                                </div>

                                <div class="table-subrow" v-if="item['ЗНАМ']">
                                    <Button text
                                        :disabled="!item['ЗНАМ'].cabinet || !item['ЗНАМ'].building || !item['ЗНАМ'].subject"
                                        @click="createLesson('ЗНАМ', item.schedule_id)" icon="pi pi-check"
                                        v-if="!item['ЗНАМ'].id"></Button>

                                    <Button text @click="removeLesson(item['ЗНАМ'].id)" icon="pi pi-trash"
                                        severity="danger" v-if="item['ЗНАМ'].id"></Button>
                                </div>

                            </td>
                        </tr>
                    </template>
                    <tr>
                        <td>
                            <InputText size="small" class="min-w-10 w-full text-center" v-model="newLesson.index" />
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
                                <InputText size="small" class="w-full text-center"
                                    v-model="newLesson['ЧИСЛ'].building" />
                            </div>
                            <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                                <InputText size="small" class="w-full text-center"
                                    v-model="newLesson['ЗНАМ'].building" />
                            </div>
                        </td>
                        <td>
                            <div class="table-subrow">
                                <InputText size="small" class="w-full text-center"
                                    v-model="newLesson['ЧИСЛ'].cabinet" />
                            </div>
                            <div v-if="newLesson['ЗНАМ']" class="table-subrow">
                                <InputText size="small" class="w-full text-center"
                                    v-model="newLesson['ЗНАМ'].cabinet" />
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
    font-size: 0.8rem;
    /* Чтобы все столбцы имели фиксированную ширину */
}

.schedule-table th,
.schedule-table td {
    border: 1px solid var(--p-surface-600);
    /* padding: 10px; */
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