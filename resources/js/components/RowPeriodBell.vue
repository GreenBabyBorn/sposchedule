<script setup lang="ts">
import { computed, onMounted, ref, toRef, watch } from 'vue';
import DatePicker from 'primevue/datepicker';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import { useDestroyBellPeriod, useUpdateBellPeriod } from '@/queries/bells';
import { useToast } from 'primevue/usetoast';

// Toast для отображения сообщений об ошибках
const toast = useToast();

// Определяем свойства компонента
const props = defineProps({
    period: {
        type: Object,
    },
});

// Привязываем period к реактивному объекту
const period = toRef(props.period);

// Устанавливаем значение чекбокса при монтировании
let checkbox = ref();
onMounted(() => {
    checkbox.value = Boolean(period.value.period_to_after) || Boolean(period.value.period_from_after);
});

watch(checkbox, () => {
    period.value.period_from_after = checkbox.value ? period.value?.period_from_after : '';
    period.value.period_to_after = checkbox.value ? period.value?.period_to_after : '';
    editPeriod(period.value)
})

// Получаем функции для обновления и удаления периода
const { mutateAsync: updatePeriod } = useUpdateBellPeriod();
const { mutateAsync: destroyPeriod } = useDestroyBellPeriod();

// Функция для форматирования времени в строку 'H:i'
function formatTime(time) {
    // Проверяем, является ли время объектом Date, и форматируем только если это дата
    if (Object.prototype.toString.call(time) === '[object Date]') {
        const hours = time.getHours().toString().padStart(2, '0');
        const minutes = time.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    }
    // Возвращаем время без изменений, если оно уже в формате HH:mm
    return time;
}

// Функция для редактирования периода
async function editPeriod(period) {
    // Форматируем время для полей period_from и period_to
    let body = {
        ...period,
        period_from: formatTime(period.period_from), // Форматируем время начала
        period_to: formatTime(period.period_to), // Форматируем время окончания
        period_from_after: period.period_from_after ? formatTime(period.period_from_after) : null, // Форматируем, если поле заполнено
        period_to_after: period.period_to_after ? formatTime(period.period_to_after) : null // Форматируем, если поле заполнено
    };

    try {
        // Отправляем обновлённые данные на сервер
        await updatePeriod({
            id: period.id,
            body
        });
    } catch (e) {
        // Отображаем сообщение об ошибке в случае неудачи
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message || 'Не удалось обновить период', life: 3000, closable: true });
    }
}

// Функция для удаления периода
async function deletePeriod(id) {
    try {
        await destroyPeriod(id);
    } catch (e) {
        // Отображаем сообщение об ошибке
        toast.add({ severity: 'error', summary: 'Ошибка', detail: e?.response?.data.message, life: 3000, closable: true });
    }
}
</script>

<template>
    <tr class="group">
        <!-- Индекс периода -->
        <td
            class="border-surface-200 px-6 py-4 text-sm text-surface-700 group-last:border-none dark:border-surface-800 dark:text-surface-400">
            {{ period.index }}
        </td>

        <!-- Время начала и возможное время после -->
        <td class="border-surface-200 px-6 py-4 group-last:border-none dark:border-surface-800">
            <div class="mb-2">
                <DatePicker @blur="editPeriod(period)" v-model="period.period_from" id="datepicker-timeonly" timeOnly
                    fluid />
            </div>
            <div v-if="checkbox">
                <DatePicker @blur="editPeriod(period)" v-model="period.period_from_after" id="datepicker-timeonly"
                    timeOnly fluid />
            </div>
        </td>

        <!-- Время окончания и возможное время после -->
        <td class="border-surface-200 px-6 py-4 group-last:border-none dark:border-surface-800">
            <div class="mb-2">
                <DatePicker @blur="editPeriod(period)" v-model="period.period_to" id="datepicker-timeonly" timeOnly
                    fluid />
            </div>
            <div v-if="checkbox">
                <DatePicker @blur="editPeriod(period)" v-model="period.period_to_after" id="datepicker-timeonly"
                    timeOnly fluid />
            </div>
        </td>

        <!-- Чекбокс для включения дополнительного времени -->
        <td class="border-surface-200 px-6 py-4 group-last:border-none dark:border-surface-800">
            <Checkbox v-model="checkbox" :binary="true" />
        </td>

        <!-- Кнопка удаления периода -->
        <td class="border-surface-200 px-6 py-4 group-last:border-none dark:border-surface-800">
            <div class="flex justify-center">
                <Button @click="deletePeriod(period.id)" text icon="pi pi-trash" severity="danger" />
            </div>
        </td>
    </tr>
</template>
