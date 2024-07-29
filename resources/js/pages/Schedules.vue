<script setup lang="ts">
import ScheduleItem from '../components/ScheduleItem.vue'
import SelectButton from 'primevue/selectbutton';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { reactive, ref } from 'vue'
import { useGroupsQuery } from '@/queries/groups';
import { useSemestersQuery, useStoreSemester } from '@/queries/semesters';

const value = ref('Основное');
const options = ref(['Основное', 'Изменения']);



const selectedMainGroup = ref();
const { data: groups } = useGroupsQuery()
const { data: semesters } = useSemestersQuery()
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-baseline">
            <h1 class="text-2xl">Расписание</h1>
        </div>
        <div class="flex items-center justify-between gap-4 p-4 rounded-lg dark:bg-surface-800">
            <div class="flex gap-2 items-center">
                <SelectButton v-model="value" :options="options" aria-labelledby="basic" />
                <Select v-model="selectedMainGroup" :options="groups" optionLabel="name" placeholder="Группа"
                    class="w-full md:w-[10rem]" />
                <Select v-model="selectedMainGroup" :options="semesters" optionLabel="name" placeholder="Семестр"
                    class="w-full md:w-[10rem]" />

            </div>

            <Button>Сохранить</Button>
        </div>
        <div class="">



            <!-- <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                <ScheduleItem></ScheduleItem>

            </div> -->
            <!-- <table class="table2 mqtable3">
                <tbody>
                    <tr>
                        <th></th>
                        <th>ИС-301</th>

                    </tr>
                    <tr>
                        <th style="writing-mode: sideways-lr;">Понедельник</th>
                        <td>
                            <ScheduleItem></ScheduleItem>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <th>Понедельник</th>
                    </tr>
                    <tr>
                        <th>Вторник</th>
                        <td>qwe</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <th>Вторник</th>
                    </tr>
                    <tr>
                        <th>Среда</th>
                        <td>qwe</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <th>Среда</th>
                    </tr>
                    <tr>
                        <th>Четверг</th>
                        <td>qwe</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <th>Четверг</th>
                    </tr>
                    <tr>
                        <th>Пятница</th>
                        <td>qwe</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <th>Пятница</th>
                    </tr>
                </tbody>
            </table> -->
        </div>
    </div>




</template>