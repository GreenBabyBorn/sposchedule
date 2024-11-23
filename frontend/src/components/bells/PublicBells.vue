<script setup lang="ts">
  import { usePublicBellsQuery } from '@/queries/bells';
  import { useBellsStore } from '@/stores/bells';
  import { storeToRefs } from 'pinia';
  import { computed, toRef, watch } from 'vue';

  const props = defineProps<{
    building: string | null;
    formattedDate: string | null;
  }>();

  const building = toRef(() => props.building);
  const formattedDate = toRef(() => props.formattedDate);

  const { data: fetchedPublicBells, isFetched: isFetchedBells } =
    usePublicBellsQuery(building, formattedDate);

  const bellsStore = useBellsStore();
  const { publicBells, isFetchedPublicBells } = storeToRefs(bellsStore);
  const { setPublicBells } = bellsStore;

  watch(fetchedPublicBells, setPublicBells);
  watch(isFetchedBells, () => {
    isFetchedPublicBells.value = isFetchedBells.value;
  });

  const mergedBells = computed(() => {
    const periodsEqual = (periods1, periods2) => {
      if (periods1.length !== periods2.length) return false;
      return periods1.every((p1, index) => {
        const p2 = periods2[index];
        return (
          p1.index === p2.index &&
          p1.has_break === p2.has_break &&
          p1.period_from === p2.period_from &&
          p1.period_to === p2.period_to &&
          p1.period_from_after === p2.period_from_after &&
          p1.period_to_after === p2.period_to_after
        );
      });
    };
    const grouped = [];

    publicBells.value?.forEach(bell => {
      let group = grouped.find(g =>
        periodsEqual(g.bells.periods, bell.periods)
      );

      if (group) {
        group.building += `, ${bell.building}`;
      } else {
        grouped.push({
          building: String(bell.building),
          bells: bell,
        });
      }
    });

    return grouped;
  });

  const getIndexesFromBells = computed(() => {
    const indexes = new Set<number>();
    mergedBells.value?.forEach(bell => {
      bell.bells.periods.forEach(period => {
        indexes.add(period.index);
      });
    });
    return Array.from(indexes).sort((a, b) => a - b);
  });
</script>
<template>
  <div class="">
    <h2
      v-if="!fetchedPublicBells && isFetchedBells"
      class="text-center text-2xl"
    >
      Звонки не найдены 🙁
    </h2>
    <div v-if="fetchedPublicBells" class="">
      <table class="bells-table rounded bg-surface-50 dark:bg-surface-900">
        <thead>
          <tr>
            <th>
              <div class="flex flex-col gap-2 p-2 text-xs">
                <span class="self-end">Корпус</span>
                <span class="rotate-12 border" />
                <span class="self-start">№ пары</span>
              </div>
            </th>
            <th v-for="bell in mergedBells" :key="bell?.building">
              <div class="flex flex-col items-center gap-1">
                <span>
                  {{ bell?.building }}
                </span>
                <span
                  :class="{
                    'text-green-400': bell.bells?.type !== 'main',
                    'text-surface-400': bell.bells?.type === 'main',
                  }"
                  class="rounded-lg text-right text-sm"
                  >{{
                    bell.bells?.type === 'main' ? 'Основное' : 'Изменения'
                  }}</span
                >
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="index in getIndexesFromBells" :key="index" class="">
            <td class="py-4 text-center font-bold">{{ index }} пара</td>
            <template v-for="bell in mergedBells" :key="bell?.building">
              <template
                v-for="period in bell.bells.periods"
                :key="period.index"
              >
                <td v-if="period?.index === index">
                  <div class="text-nowrap">
                    {{ period.period_from }} - {{ period.period_to }}
                  </div>
                  <div v-if="period?.period_from_after" class="text-nowrap">
                    {{ period.period_from_after }} -
                    {{ period.period_to_after }}
                  </div>
                </td>
              </template>
              <td
                v-if="
                  !bell.bells.periods.find(period => period.index === index)
                "
              />
            </template>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
  .bells-table {
    border-collapse: collapse;
  }

  .bells-table td {
    padding: 0.75rem 1rem;
  }
</style>
