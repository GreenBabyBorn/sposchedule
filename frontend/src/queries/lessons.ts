import { useMutation, useQueryClient } from '@tanstack/vue-query';
import axios from 'axios';
import { storeToRefs } from 'pinia';
import type {
  ChangesSchedules,
  Lesson,
  Schedule,
} from '@/components/schedule/types';
import { useScheduleStore } from '@/stores/schedule';

export function useStoreLesson() {
  const queryClient = useQueryClient();

  const updateSemesterMutation = useMutation({
    mutationFn: async ({ lesson }: { lesson: any }) =>
      (await axios.post(`/api/lessons`, lesson)).data as Lesson,
    onSettled: (data, error, variables) => {
      queryClient.invalidateQueries({
        queryKey: ['scheduleChanges'],
      });
      queryClient.invalidateQueries({
        queryKey: ['scheduleMain'],
      });
    },
  });
  return updateSemesterMutation;
}

export function useUpdateLesson() {
  const queryClient = useQueryClient();
  const scheduleStore = useScheduleStore();
  const {
    formattedDate,
    selectedCourse,
    selectedGroup,
    building,
    selectedMainGroup,
    selectedMainSemester,
  } = storeToRefs(scheduleStore);
  const updateLessonMutation = useMutation({
    mutationFn: async ({ lesson }: { lesson: Lesson }) =>
      (await axios.patch(`/api/lessons/${lesson.id}`, lesson)).data,
    onMutate: async ({ lesson }) => {
      await queryClient.cancelQueries({
        queryKey: [
          'scheduleChanges',
          formattedDate,
          building,
          selectedCourse,
          selectedGroup,
        ],
      });

      const previousChanges = queryClient.getQueryData([
        'scheduleChanges',
        formattedDate,
        building,
        selectedCourse,
        selectedGroup,
      ]);

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
          const newData: ChangesSchedules = JSON.parse(JSON.stringify(oldData));
          newData.schedules = newData.schedules.map((s: Schedule) => {
            if (s.id === lesson.schedule_id) {
              s.lessons = s.lessons
                .map((l: Lesson) =>
                  l.id === lesson.id ? { ...l, ...lesson } : l
                )
                .sort((a, b) => a.index - b.index);
            }
            return s;
          });

          return newData;
        }
      );

      return { previousChanges };
    },
    onError: (err, { lesson }, context) => {
      queryClient.setQueryData(
        [
          'scheduleChanges',
          formattedDate,
          building,
          selectedCourse,
          selectedGroup,
        ],
        context?.previousChanges
      );
    },
    onSettled: () => {
      queryClient.invalidateQueries({
        queryKey: ['scheduleMain', selectedMainGroup, selectedMainSemester],
      });
    },
  });

  return updateLessonMutation;
}

export function useDestroyLesson() {
  const queryClient = useQueryClient();
  const scheduleStore = useScheduleStore();
  const { formattedDate, selectedCourse, selectedGroup, building } =
    storeToRefs(scheduleStore);
  const destroySemesterMutation = useMutation({
    mutationFn: ({ lesson }: { lesson: Lesson }) =>
      axios.delete(`/api/lessons/${lesson.id}`),
    onMutate: async ({ lesson }) => {
      await queryClient.cancelQueries({
        queryKey: [
          'scheduleChanges',
          formattedDate,
          building,
          selectedCourse,
          selectedGroup,
        ],
      });

      const previousChanges = queryClient.getQueryData([
        'scheduleChanges',
        formattedDate,
        building,
        selectedCourse,
        selectedGroup,
      ]);

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
          const newData: ChangesSchedules = JSON.parse(JSON.stringify(oldData));
          newData.schedules = newData.schedules.map((s: Schedule) => {
            if (s.id === lesson.schedule_id) {
              s.lessons = s.lessons.filter(l => l.id !== lesson.id);
            }
            return s;
          });

          return newData;
        }
      );
      return { previousChanges };
    },
    onError: (err, { lesson }, context) => {
      queryClient.setQueryData(
        [
          'scheduleChanges',
          formattedDate,
          building,
          selectedCourse,
          selectedGroup,
        ],
        context?.previousChanges
      );
      queryClient.invalidateQueries({ queryKey: ['scheduleChanges'] });
    },
    onSettled: (data, error, variables) => {
      queryClient.invalidateQueries({ queryKey: ['scheduleMain'] });
    },
  });
  return destroySemesterMutation;
}
