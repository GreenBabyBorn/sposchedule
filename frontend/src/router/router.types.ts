import type { VueElement } from 'vue';
import type { AppLayoutsEnum } from '@/layouts/layouts.types';

declare module 'vue-router' {
  interface RouteMeta {
    layout?: AppLayoutsEnum;
    layoutComponent?: VueElement;
  }
}

export enum RouteNamesEnum {
  home = 'home',
  groups = 'groups',
  subjects = 'subjects',
  teachers = 'teachers',
  schedules = 'schedules',
  schedulesChanges = 'schedulesChanges',
  semesters = 'semesters',
  user = 'user',
  auth = 'auth',
  bells = 'bells',
  buildings = 'buildings',
  history = 'history',
  analytics = 'analytics',
  load = 'load',
}
