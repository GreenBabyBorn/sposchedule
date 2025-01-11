<script setup lang="ts">
  import { toRef } from 'vue';
  import DatePicker from 'primevue/datepicker';
  import Checkbox from 'primevue/checkbox';
  import Button from 'primevue/button';
  import { useDestroyBellPeriod, useUpdateBellPeriod } from '@/queries/bells';
  import { useToast } from 'primevue/usetoast';
  import InputText from 'primevue/inputtext';
  import { isAxiosError } from 'axios';
  import type { BellsPeriod } from './types';

  const toast = useToast();

  const props = defineProps<{
    period: {
      id: number;
      index: string;
      has_break: boolean;
      period_from: Date;
      period_to: Date;
      period_from_after: Date;
      period_to_after: Date;
    };
  }>();

  const period = toRef(() => props.period);

  const { mutateAsync: updatePeriod } = useUpdateBellPeriod();
  const { mutateAsync: destroyPeriod } = useDestroyBellPeriod();

  function formatTime(time: Date | string | null) {
    if (Object.prototype.toString.call(time) === '[object Date]') {
      const hours = (time as Date).getHours().toString().padStart(2, '0');
      const minutes = (time as Date).getMinutes().toString().padStart(2, '0');
      return `${hours}:${minutes}`;
    }

    return time;
  }

  async function editPeriod(period: BellsPeriod) {
    let body = {
      ...period,
      period_from: formatTime(period.period_from),
      period_to: formatTime(period.period_to),
    };
    if (
      period.has_break &&
      (period.period_from_after || period.period_to_after)
    ) {
      body.period_from_after = formatTime(period.period_from_after) as Date;
      body.period_to_after = formatTime(period.period_to_after) as Date;
    } else if (!period.has_break) {
      body.period_from_after = null;
      body.period_to_after = null;
    }

    try {
      await updatePeriod({
        id: period.id,
        body,
      });
    } catch (e) {
      if (isAxiosError(e))
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail: e?.response?.data.message || 'Не удалось обновить период',
          life: 3000,
          closable: true,
        });
    }
  }

  async function deletePeriod(id: number) {
    try {
      await destroyPeriod(id);
    } catch (e) {
      if (isAxiosError(e))
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail: e?.response?.data.message,
          life: 3000,
          closable: true,
        });
    }
  }
</script>

<template>
  <tr class="bg-surface-100 dark:border-surface-700 dark:bg-surface-900">
    <td class="text-center text-lg">
      <InputText
        v-model="period.index"
        v-keyfilter="/^\d+$/"
        class="max-w-12 text-center"
        @blur="editPeriod(period)"
      />
      <!-- {{ period.index }} -->
    </td>

    <td class="">
      <div class="flex flex-col items-center justify-center gap-2 py-2">
        <div class="flex items-center gap-2">
          <DatePicker
            :id="`datepicker-timeonly${period.period_from}`"
            v-model="period.period_from"
            input-class="text-center"
            time-only
            fluid
            @blur="editPeriod(period)"
          />
          -
          <DatePicker
            :id="`datepicker-timeonly${period.period_to}`"
            v-model="period.period_to"
            input-class="text-center"
            time-only
            fluid
            @blur="editPeriod(period)"
          />
        </div>
        <div v-if="period.has_break" class="flex items-center gap-2">
          <DatePicker
            :id="`datepicker-timeonly${period.period_from_after}`"
            v-model="period.period_from_after"
            input-class="text-center"
            time-only
            fluid
            @blur="editPeriod(period)"
          />
          -
          <DatePicker
            :id="`datepicker-timeonly${period.period_to_after}`"
            v-model="period.period_to_after"
            input-class="text-center"
            time-only
            fluid
            @blur="editPeriod(period)"
          />
        </div>
      </div>
    </td>

    <td class="text-center">
      <Checkbox
        v-model="period.has_break"
        :binary="true"
        @change="editPeriod(period)"
      />
    </td>

    <td class="">
      <div class="flex justify-center">
        <Button
          text
          icon="pi pi-trash"
          severity="danger"
          @click="deletePeriod(period.id)"
        />
      </div>
    </td>
  </tr>
</template>
