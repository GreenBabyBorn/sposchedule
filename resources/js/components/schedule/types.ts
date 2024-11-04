export type Teacher = { id: number; name: string; subjects?: Subject[] };

export type Subject = { id: number; name: string; teachers?: Teacher[] };

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

export type weekType = 'ЧИСЛ' | 'ЗНАМ' | null;

export type Schedule = {
  schedule_id: number;
  week_day: WeekDays | '';
  published: boolean;
  type: ScheduleType;
  group: object;
  lessons: Lesson[];
};

export type ChangesSchedules = {
  week_type: weekType;
  last_updated: string;
  schedules: Schedule[];
  semester: any;
};

export type WeekDays = 'ПН' | 'ВТ' | 'СР' | 'ЧТ' | 'ПТ' | 'СБ' | 'ВС';

export type MainSchedule = {
  week_day: WeekDays;
  type: ScheduleType;
  schedule_id: number;
  published: boolean;
  lessons: LessonMainSchedule[];
};

export type LessonMainSchedule = {
  index: number;
  types: WeekTypeLesson[];
};

export type WeekTypeLesson = {
  week_type: weekType;
  lesson_id: number;
  schedule_id: number;
  cabinet: string;
  building: string;
  subject: Subject;
  teachers: Teacher[];
};

export type Group = {
  id: number;
  name: string;
  specilization: string;
  course: number;
  index: string;
  semesters: any[];
  buidings: any[];
};
