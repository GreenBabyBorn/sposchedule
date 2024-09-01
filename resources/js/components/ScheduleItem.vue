<script setup lang="ts">
import { useToast } from 'primevue/usetoast';
import { toRef } from 'vue';

const toast = useToast();
const props = defineProps({
    group: { required: true, type: Object },
    date: { required: false },
    week_type: { required: false },
    type: { required: true },
    semester: { required: false, type: Object },
    lessons: { required: true },
    schedule: { required: true, type: Object },
    published: { required: false, type: Boolean },
})
const lessons: any = toRef<any>(() => props.lessons)
</script>

<template>
    <div class="schedule-item">
        <div class="mb-4 flex flex-wrap  justify-between items-center"> <span
                class="text-xl text-left font-medium text-surface-800 dark:text-white/80">{{
                    props.group.name }}</span>
            <span>{{ props.week_type }}</span>
            <span :class="{
                'border border-green-400 ': props.type
                    !== 'main',
                'border border-surface-400 ': props.type
                    === 'main'
            }" class="text-sm text-right  py-1 px-2 rounded-lg dark:text-white/80">{{
                props.type
                    === 'main' ? 'Основное' : 'Изменения' }}</span>
        </div>
        <table v-if="lessons" class="schedule-table dark:bg-surface-900">
            <thead>
                <tr class="text-sm dark:bg-surface-950">
                    <th>
                        <div class="">№</div>
                    </th>
                    <th>
                        <div class="border-b dark:border-surface-700 flex justify-start p-1">
                            <span>
                                Предмет
                            </span>
                        </div>
                        <div class="flex justify-end p-1">
                            Преподаватели
                        </div>
                    </th>

                    <th>
                        <div class="">Корпус</div>
                    </th>
                    <th>
                        <div class="">Кабинет</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template v-for="item in lessons">
                    <tr class="">
                        <td><span class="text-xl font-medium text-surface-800 dark:text-white/80">
                                {{ item.index }}
                            </span></td>
                        <td>
                            <div v-if="item.id" :class="{ 'border-b border-surface-700': item.teachers?.length }"
                                class=" flex p-2"><span class="text-lg">{{
                                    item.subject.name }}</span>
                            </div>
                            <div class="flex flex-wrap px-2 justify-end" v-if="item.id">
                                <span class="dark:text-surface-500 text-sm" v-for="teacher in item.teachers">{{
                                    teacher.name
                                }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="table-subrow" v-if="item.id">
                                <span>{{ item.building }}</span>
                            </div>
                        </td>
                        <td>

                            <div class="table-subrow" v-if="item.id">
                                <span>{{ item.cabinet }}</span>
                            </div>
                        </td>
                    </tr>
                </template>

            </tbody>
        </table>
        <div v-else class="flex justify-center items-center">
            <span class="text-2xl">


                Расписание не найдено
            </span>
        </div>


    </div>
</template>

<style scoped>
.schedule-item {
    width: 100%;
}

.schedule-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    /* font-size: 0.8rem; */
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
</style>