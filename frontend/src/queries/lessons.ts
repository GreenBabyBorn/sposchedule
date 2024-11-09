import { useMutation, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { storeToRefs } from 'pinia';
import type {
  ChangesSchedules,
  Lesson,
  Schedule,
} from '@/components/schedule/types';
import { useScheduleStore } from '@/stores/schedule';
import type { Ref } from 'vue';

export function useUpdateLesson() {
  const queryClient = useQueryClient();
  // const scheduleStore = useScheduleStore();
  // const {
  //   formattedDate,
  //   selectedCourse,
  //   selectedGroup,
  //   building,
  //   selectedMainGroup,
  //   selectedMainSemester,
  // } = storeToRefs(scheduleStore);
  const updateLessonMutation = useMutation({
    mutationFn: ({ id, body }: any) => axios.patch(`/api/lessons/${id}`, body),
    // onMutate: async ({ id, body }) => {
    //   await queryClient.cancelQueries({
    //     queryKey: [
    //       'scheduleChanges',
    //       formattedDate,
    //       building,
    //       selectedCourse,
    //       selectedGroup,
    //     ],
    //   });

    //   const previousChanges: ChangesSchedules | undefined =
    //     queryClient.getQueryData([
    //       'scheduleChanges',
    //       formattedDate,
    //       building,
    //       selectedCourse,
    //       selectedGroup,
    //     ]);

    //   queryClient.setQueryData(
    //     [
    //       'scheduleChanges',
    //       formattedDate,
    //       building,
    //       selectedCourse,
    //       selectedGroup,
    //     ],
    //     (oldData: any) => {
    //       if (!oldData) return;
    //       const newData: ChangesSchedules = JSON.parse(JSON.stringify(oldData));
    //       newData.schedules = newData.schedules.map((schedule: Schedule) => {
    //         if (schedule.schedule_id === body.schedule_id) {
    //           schedule.lessons = schedule.lessons.map((lesson: Lesson) =>
    //             lesson.id === id ? { ...lesson, ...body } : lesson
    //           );
    //         }
    //         return schedule;
    //       });
    //       return newData;
    //     }
    //   );

    //   return { previousChanges };
    // },
    // onError: (err, item: Lesson, context) => {
    //   queryClient.setQueryData(
    //     [
    //       'scheduleChanges',
    //       formattedDate,
    //       building,
    //       selectedCourse,
    //       selectedGroup,
    //     ],
    //     context?.previousChanges
    //   );
    // },
    // onSettled: () => {
    //   queryClient.invalidateQueries({
    //     queryKey: ['scheduleMain', selectedMainGroup, selectedMainSemester],
    //   });
    // },
  });

  return updateLessonMutation;
}

export function useUpdateScheduleLesson() {
  // const queryClient = useQueryClient();
  // const scheduleStore = useScheduleStore();
  // const { formattedDate, selectedCourse, selectedGroup, building } =
  //   storeToRefs(scheduleStore);
  const updateLessonMutation = useMutation({
    mutationFn: ({ scheduleId, lessonId, body }: any) =>
      axios.patch(`/api/schedules/${scheduleId}/lessons/${lessonId}`, body),

    // onSettled: async (data, error, variables) => {
    // await queryClient.invalidateQueries({
    //   queryKey: ['schedule', variables.scheduleId],
    //   exact: true,
    // });
    // console.log(queryClient.getQueryData(['schedule', variables.scheduleId]));

    // queryClient.setQueryData(
    //   [
    //     'scheduleChanges',
    //     formattedDate,
    //     building,
    //     selectedCourse,
    //     selectedGroup,
    //   ],
    //   (oldData: any) => {
    //     if (!oldData) return;
    //     const newData: ChangesSchedules = JSON.parse(JSON.stringify(oldData));
    //     newData.schedules = newData.schedules.map((schedule: Schedule) => {
    //       if (schedule.id === variables.scheduleId) {
    //         schedule = data?.data;
    //       }
    //       return schedule;
    //     });
    //     return newData;
    //   }
    // );
    // },
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
    mutationFn: ({ body, scheduleId }: any) => axios.post(`/api/lessons`, body),
    onSettled: (data, error, variables) => {
      queryClient.invalidateQueries({
        queryKey: ['scheduleMain'],
      });
    },
  });
  return updateSemesterMutation;
}

export function useDestroyLesson() {
  const queryClient = useQueryClient();
  const destroySemesterMutation = useMutation({
    mutationFn: ({ id, scheduleId }: any) => axios.delete(`/api/lessons/${id}`),
    onSettled: (data, error, variables) => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
    },
  });
  return destroySemesterMutation;
}
