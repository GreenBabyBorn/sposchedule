import { useScheduleStore } from '@/stores/schedule';
import { useMutation, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { storeToRefs } from 'pinia';

const scheduleStore = useScheduleStore();
const { formattedDate, selectedCourse, selectedGroup, building } =
  storeToRefs(scheduleStore);

export function useUpdateLesson() {
  const queryClient = useQueryClient();

  const updateLessonMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/lessons/${id}`, body),
    onMutate: async ({ id, body }) => {
      // Отмена любого исходящего рефетча для предотвращения перезаписи оптимистичных обновлений
      await queryClient.cancelQueries({ queryKey: ['scheduleMain'] });
      await queryClient.cancelQueries({
        queryKey: [
          'scheduleChanges',
          formattedDate,
          building,
          selectedCourse,
          selectedGroup,
        ],
      });

      // Сохранение предыдущих данных для отката в случае ошибки
      const previousMain = queryClient.getQueryData(['scheduleMain']);
      const previousChanges = queryClient.getQueryData([
        'scheduleChanges',
        formattedDate,
        building,
        selectedCourse,
        selectedGroup,
      ]);

      // Оптимистичное обновление данных
      queryClient.setQueryData(['scheduleMain'], (oldData: any) => {
        if (!oldData) return;
        const newData = { ...oldData };
        newData.schedules = newData.schedules.map((schedule: any) => {
          if (schedule.id === body.schedule_id) {
            schedule.lessons = schedule.lessons.map((lesson: any) =>
              lesson.id === id ? { ...lesson, ...body } : lesson
            );
          }
          return schedule;
        });
        return newData;
      });

      queryClient.setQueryData(
        [
          'scheduleChanges',
          formattedDate,
          building,
          selectedCourse,
          selectedGroup,
        ],
        (oldData: any) => {
          if (!oldData) return;
          const newData = { ...oldData };
          newData.schedules = newData.schedules.map((schedule: any) => {
            if (schedule.id === body.schedule_id) {
              schedule.lessons = schedule.lessons.map((lesson: any) =>
                lesson.id === id ? { ...lesson, ...body } : lesson
              );
            }
            return schedule;
          });
          return newData;
        }
      );

      // Возврат контекста для использования в случае ошибки
      return { previousMain, previousChanges };
    },
    onError: (err, data, context) => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
      // Откат данных в случае ошибки
      console.log(context.previousChanges);
      if (context?.previousMain) {
        queryClient.setQueryData(['scheduleMain'], context.previousMain);
      }
      if (context?.previousChanges) {
        queryClient.setQueryData(
          [
            'scheduleChanges',
            formattedDate,
            building,
            selectedCourse,
            selectedGroup,
          ],
          context.previousChanges
        );
      }
    },
    onSettled: () => {
      // При успешном завершении можно дополнительно инвалировать запросы для актуализации данных
      // queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
      // queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });

  return updateLessonMutation;
}

// export function useUpdateLesson() {
//   const queryClient = useQueryClient();
//   const updateSemesterMutation = useMutation({
//     mutationFn: ({ id, body }: any) => axios.patch(`/api/lessons/${id}`, body),
//     onSuccess: () => {
//       queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
//       queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
//     },
//     onError: () => {
//       queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
//       queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
//     },
//   });
//   return updateSemesterMutation;
// }

export function useStoreLesson() {
  const queryClient = useQueryClient();
  const updateSemesterMutation = useMutation({
    mutationFn: ({ body }: any) => axios.post(`/api/lessons`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ['scheduleMain'],
      });
      queryClient.invalidateQueries({
        queryKey: ['scheduleChanges'],
      });
    },
  });
  return updateSemesterMutation;
}

export function useDestroyLesson() {
  const queryClient = useQueryClient();
  const destroySemesterMutation = useMutation({
    mutationFn: ({ id }: any) => axios.delete(`/api/lessons/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
    onError: () => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
  });
  return destroySemesterMutation;
}
