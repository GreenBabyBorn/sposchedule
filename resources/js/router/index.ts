import { createWebHistory, createRouter } from 'vue-router';
import { loadLayoutMiddleware } from '@/router/middleware/loadLayout.middleware';
import { RouteNamesEnum } from '@/router/router.types';
import { AppLayoutsEnum } from '@/layouts/layouts.types';

import HomeView from '../pages/Home.vue';
import BellsView from '../pages/Bells.vue';
import GroupsView from '../pages/admin/Groups.vue';
import SubjectsView from '../pages/admin/Subjects.vue';
import TeachersView from '../pages/admin/Teachers.vue';
import SchedulesMainView from '../pages/admin/MainSchedules.vue';
import SchedulesChangesView from '../pages/admin/ChangesSchedules.vue';
import SemestersView from '../pages/admin/Semesters.vue';
import BuildingsView from '../pages/admin/Buildings.vue';
import UserView from '../pages/admin/User.vue';
import AuthView from '../pages/Login.vue';
import Bells from '../pages/admin/Bells.vue';
import NotFound from '../pages/NotFound.vue';
import PrintView from '../pages/PrintChanges.vue';
import PrintMainView from '../pages/PrintMain.vue';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/404',
      name: 'NotFound',
      component: NotFound,
      meta: {
        layout: AppLayoutsEnum.public,
        title: '404',
      },
    },
    {
      path: '/:catchAll(.*)',
      redirect: '404',
    },
    {
      path: '/',
      component: HomeView,
      meta: {
        layout: AppLayoutsEnum.public,
      },
    },
    {
      path: '/print/changes',
      component: PrintView,
      meta: {
        layout: AppLayoutsEnum.empty,
        title: 'Изменения',
      },
    },
    {
      path: '/print/main',
      component: PrintMainView,
      meta: {
        layout: AppLayoutsEnum.empty,
        title: 'Основное',
      },
    },
    // {
    //   path: '/bells',
    //   component: BellsView,
    //   meta: { title: 'Звонки', layout: AppLayoutsEnum.public },
    // },
    {
      path: '/admin/login',
      name: RouteNamesEnum.auth,
      component: AuthView,
      meta: {
        layout: AppLayoutsEnum.default,
        title: 'Авторизация',
      },
    },
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
      path: '/admin/bells',
      name: RouteNamesEnum.bells,
      component: Bells,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Звонки',
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
      component: SchedulesMainView,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Основное расписание',
      },
    },
    {
      path: '/admin/schedules/changes',
      name: RouteNamesEnum.schedulesChanges,
      component: SchedulesChangesView,
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
    {
      path: '/admin/buildings',
      name: RouteNamesEnum.buildings,
      component: BuildingsView,
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Корпуса',
      },
    },
  ],
});

router.beforeEach(loadLayoutMiddleware);

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // Если пользователь уже авторизован и пытается попасть на страницу входа, перенаправляем его на админскую страницу
  if (to.path === '/admin/login' && authStore.token) {
    return next('/admin/schedules/changes');
  }

  // Если пользователь не авторизован и пытается получить доступ к админским страницам, перенаправляем на страницу входа
  if (to.meta.layout === 'admin' && !authStore.token) {
    return next('/admin/login');
  }

  // Если у пользователя есть токен, но данные пользователя еще не загружены, загружаем их
  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchUser();
    } catch (error) {
      // Если произошла ошибка при загрузке данных пользователя, перенаправляем на страницу входа
      // authStore.logout();
      return next('/admin/login');
    }
  }

  // Разрешаем переход на целевой маршрут
  next();
});

router.beforeEach(to => {
  const { title, description }: any = to.meta;
  const defaultTitle = 'Пары РКЭ';
  const defaultDescription = 'Расписание РКЭ';

  document.title = title ? `${title} | ${defaultTitle}` : defaultTitle;

  const descriptionElement = document.querySelector(
    'head meta[name="description"]'
  );

  descriptionElement.setAttribute('content', description || defaultDescription);
});

export default router;
