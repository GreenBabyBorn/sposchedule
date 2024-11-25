export type Teacher = { id: number; name: string; subjects?: Subject[] };

export type Subject = { id: number; name: string; teachers?: Teacher[] };

export type SubjectWithTeachers = Subject & {
  teachers: Teacher[];
};

export type ScheduleType = 'changes' | 'main';

export type Lesson = {
  id?: number;
  index: number;
  schedule_id: number;
  subject: Subject;
  week_type: string | null;
  cabinet: string;
  building: string;
  message: string | null;
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
  id: number;
  week_day: WeekDays | '';
  published: boolean;
  type: ScheduleType;
  group: Group;
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
  id: number;
  published: boolean;
  lessons: LessonMainSchedule[];
};

export type LessonMainSchedule = {
  index: number;
  types: WeekTypeLesson[];
};

export type WeekTypeLesson = {
  week_type: weekType;
  id: number;
  schedule_id: number;
  cabinet: string;
  building: string;
  subject: Subject;
  subject_id?: number;
  teachers: Teacher[];
  message: string | null;
};

export type Group = {
  id: number;
  name: string;
  specilization: string;
  course: number;
  index: string;
  semesters: Semester[];
  buidings: any[];
};

export type Semester = {
  id: number;
  index: string;
  years: string;
  start: string;
  end: string;
  groups?: Group[];
};
