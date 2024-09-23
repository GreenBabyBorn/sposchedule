<script setup lang="ts">
import { toRef } from 'vue';
import MultiSelect from 'primevue/multiselect';

import Select from 'primevue/select';
// import Select from '../ui/Select.vue';

import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';

const props = defineProps<{
    lesson: any,
    teachers: any,
    subjects: any,
    isEdit: boolean,
}>()

const teachers: any = toRef<any>(() => props.teachers)
const subjects: any = toRef<any>(() => props.subjects)
const isEdit: any = toRef<any>(() => props.isEdit)

const lesson = toRef(() => props.lesson)

const emit = defineEmits<{
    (e: 'removeLesson', id: number): void
    (e: 'editLesson', lesson: any): void
}>()

const removeLesson = (id: number) => {
    emit('removeLesson', id);
}

const editLesson = (lesson: any) => {
    emit('editLesson', lesson);
}

</script>
<template>
    <tr>
        <td><span class="text-xl font-medium text-surface-800 dark:text-white/80">
                {{ lesson?.index }}
            </span></td>
        <td v-show="lesson?.message" colspan="3/1">
            <div class="table-subrow">

                <Textarea v-if="isEdit" @change="editLesson(lesson)" v-model="lesson.message"
                    placeholder="Введите сообщение для группы" class="w-full" />
            </div>
        </td>
        <td v-if="!lesson?.message">
            <div v-if="lesson.subject" class="table-subrow">
                <span v-if="!isEdit">{{ lesson.subject.name }}</span>
                <Select v-else filter @change="editLesson(lesson)" v-model="lesson.subject" class="w-full text-left"
                    :options="subjects" option-label="name" />
            </div>
            <div v-if="lesson.teachers" class="table-subrow">
                <div class="" v-if="!isEdit">
                    <span v-for="teacher in lesson.teachers">{{ teacher.name }}</span>
                </div>
                <MultiSelect v-else placeholder="Выберите преподавателя" @change="editLesson(lesson)"
                    v-model="lesson.teachers" :options="teachers" class="w-full" option-label="name" />
            </div>
        </td>
        <td v-show="!lesson.message">
            <div v-if="lesson.id" class="table-subrow">
                <span v-if="!isEdit">{{ lesson.building }}</span>
                <InputText v-else class="w-full" @change="editLesson(lesson)" v-model="lesson.building" />
            </div>
        </td>
        <td v-show="!lesson.message">
            <div v-if="lesson.id" class="table-subrow">
                <span v-if="!isEdit">{{ lesson.cabinet }}</span>
                <InputText v-else class="w-full" @change="editLesson(lesson)" v-model="lesson.cabinet" />
            </div>
        </td>
        <td v-if="isEdit">
            <div class="table-subrow">

                <Button text :disabled="!lesson?.cabinet || !lesson?.building || !lesson?.subject" icon="pi pi-check"
                    v-if="!lesson?.id && isEdit" />

                <Button text @click="removeLesson(lesson?.id)" icon="pi pi-trash" severity="danger"
                    v-if="lesson?.id && isEdit" />
            </div>
        </td>
    </tr>
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