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

export type weekType = 'ЧИСЛ' | 'ЗНАМ' | '';

export type Schedule = {
  schedule_id: number;
  week_day: 'ПН' | 'ВТ' | 'СР' | 'ЧТ' | 'ПТ' | 'СБ' | 'ВС' | '';
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
