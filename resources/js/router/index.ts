import { createWebHistory, createRouter } from 'vue-router';
import { loadLayoutMiddleware } from '@/router/middleware/loadLayout.middleware';
import { RouteNamesEnum } from '@/router/router.types';
import { AppLayoutsEnum } from '@/layouts/layouts.types';

import HomeView from '../pages/Home.vue';
import GroupsView from '../pages/Groups.vue';
import SubjectsView from '../pages/Subjects.vue';
import TeachersView from '../pages/Teachers.vue';
import SchedulesView from '../pages/MainSchedules.vue';
import SchedulesChanges from '../pages/ChangesSchedules.vue';
import SemestersView from '../pages/Semesters.vue';
import UserView from '../pages/User.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: HomeView },
    {
      path: '/admin/user',
      name: RouteNamesEnum.user,
      component: UserView,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Пользователь',
      },
    },
    {
      path: '/admin/groups',
      name: RouteNamesEnum.groups,
      component: GroupsView,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Группы',
      },
    },
    {
      path: '/admin/schedules/main',
      name: RouteNamesEnum.schedules,
      component: SchedulesView,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Основное расписание',
      },
    },
    {
      path: '/admin/schedules/changes',
      name: RouteNamesEnum.schedulesChanges,
      component: SchedulesChanges,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Расписание',
      },
    },
    {
      path: '/admin/subjects',
      name: RouteNamesEnum.subjects,
      component: SubjectsView,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Предметы',
      },
    },
    {
      path: '/admin/teachers',
      name: RouteNamesEnum.teachers,
      component: TeachersView,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Преподаватели',
      },
    },
    {
      path: '/admin/semesters',
      name: RouteNamesEnum.semesters,
      component: SemestersView,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Семестры',
      },
    },
  ],
});

router.beforeEach(loadLayoutMiddleware);
router.beforeEach(to => {
  const { title, description }: any = to.meta;
  const defaultTitle = 'СУР';
  const defaultDescription = 'Система управления расписанием';

  document.title = title || defaultTitle;

  const descriptionElement = document.querySelector(
    'head meta[name="description"]'
  );

  descriptionElement.setAttribute('content', description || defaultDescription);
});

export default router;
