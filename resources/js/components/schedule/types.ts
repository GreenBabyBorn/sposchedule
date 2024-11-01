export type Teacher = { id: number; name: string };

export type Subject = { id: number; name: string };
export type SubjectWithTeachers = Subject & {
  teachers: Teacher[];
};

export type ScheduleType = 'changes' | 'main';

export type Lesson = {
  id: number;
  index: number;
  schedule_id: number;
  subject: Subject;
  week_type: string;
  cabinet: string;
  building: string;
  message: string;
  teachers?: Teacher[];
};

export type PublicLesson = {
  id: number;
  index: number;
  subject_name: string;
  week_type: string;
  cabinet: string;
  building: string;
  message: string;
  teachers?: Teacher[];
};
