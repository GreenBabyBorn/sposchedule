<script setup lang="ts">
  import { toRef } from 'vue';
  import DatePicker from 'primevue/datepicker';
  import Checkbox from 'primevue/checkbox';
  import Button from 'primevue/button';
  import { useDestroyBellPeriod, useUpdateBellPeriod } from '@/queries/bells';
  import { useToast } from 'primevue/usetoast';

  const toast = useToast();

  const props = defineProps({
    period: {
      type: Object,
    },
  });

  const period = toRef(props.period);

  const { mutateAsync: updatePeriod } = useUpdateBellPeriod();
  const { mutateAsync: destroyPeriod } = useDestroyBellPeriod();

  function formatTime(time) {
    if (Object.prototype.toString.call(time) === '[object Date]') {
      const hours = time.getHours().toString().padStart(2, '0');
      const minutes = time.getMinutes().toString().padStart(2, '0');
      return `${hours}:${minutes}`;
    }

    return time;
  }

  async function editPeriod(period) {
    let body = {
      ...period,
      period_from: formatTime(period.period_from),
      period_to: formatTime(period.period_to),
    };
    console.log(period.has_break);
    if (
      period.has_break &&
      period.period_from_after &&
      period.period_to_after
    ) {
      body.period_from_after = formatTime(period.period_from_after);
      body.period_to_after = formatTime(period.period_to_after);
    } else if (
      period.has_break &&
      !period?.period_from_after &&
      !period?.period_to_after
    ) {
      body.period_from_after = period.period_from;
      body.period_to_after = period.period_to;
    } else {
      body.period_from_after = null;
      body.period_to_after = null;
    }

    try {
      await updatePeriod({
        id: period.id,
        body,
      });
    } catch (e) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: e?.response?.data.message || 'Не удалось обновить период',
        life: 3000,
        closable: true,
      });
    }
  }

  async function deletePeriod(id) {
    try {
      await destroyPeriod(id);
    } catch (e) {
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
  <tr
    class="border dark:border-surface-700 border-surface-400 bg-surface-100 dark:bg-surface-800"
  >
    <td class="text-center text-lg">
      {{ period.index }}
    </td>

    <td class="">
      <div class="flex justify-center items-center flex-col gap-2 py-2">
        <div class="flex gap-2 items-center">
          <DatePicker
            id="datepicker-timeonly"
            v-model="period.period_from"
            time-only
            fluid
            @blur="editPeriod(period)"
          />
          -
          <DatePicker
            id="datepicker-timeonly"
            v-model="period.period_to"
            time-only
            fluid
            @blur="editPeriod(period)"
          />
        </div>
        <div v-if="period.has_break" class="flex gap-2 items-center">
          <DatePicker
            id="datepicker-timeonly"
            v-model="period.period_from_after"
            time-only
            fluid
            @blur="editPeriod(period)"
          />
          -
          <DatePicker
            id="datepicker-timeonly"
            v-model="period.period_to_after"
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
