export type BellType = 'changes' | 'main';

export type Bell = {
  id: number;
  building: string;
  type: BellType;
  periods: BellsPeriod[] | [];
};

export type BellsPeriod = {
  id: number;
  index: string;
  has_break: boolean;
  period_from: Date;
  period_to: Date;
  period_from_after: Date | null;
  period_to_after: Date | null;
};
