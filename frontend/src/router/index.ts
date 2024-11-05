import { createWebHistory, createRouter } from 'vue-router';
import { loadLayoutMiddleware } from '@/router/middleware/loadLayout.middleware';
import { RouteNamesEnum } from '@/router/router.types';
import { AppLayoutsEnum } from '@/layouts/layouts.types';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/404',
      name: 'NotFound',
      component: () => import('@/pages/NotFoundView.vue'),
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
      component: () => import('@/pages/HomeView.vue'),
      meta: {
        layout: AppLayoutsEnum.public,
      },
    },
    {
      path: '/print/changes',
      component: () => import('@/pages/PrintChangesView.vue'),
      meta: {
        layout: AppLayoutsEnum.empty,
        title: 'Изменения',
      },
    },
    {
      path: '/print/main',
      component: () => import('@/pages/PrintMainView.vue'),
      meta: {
        layout: AppLayoutsEnum.empty,
        title: 'Основное',
      },
    },
    {
      path: '/print/bells',
      component: () => import('@/pages/PrintBellsView.vue'),
      meta: {
        layout: AppLayoutsEnum.empty,
        title: 'Звонки',
      },
    },
    {
      path: '/admin/login',
      name: RouteNamesEnum.auth,
      component: () => import('@/pages/LoginView.vue'),
      meta: {
        layout: AppLayoutsEnum.default,
        title: 'Авторизация',
      },
    },
    {
      path: '/analytics',
      name: RouteNamesEnum.analytics,
      component: () => import('@/pages/AnalyticsView.vue'),
      meta: {
        layout: AppLayoutsEnum.public,
        title: 'Аналитика',
      },
    },
    {
      path: '/admin/history',
      name: RouteNamesEnum.history,
      component: () => import('@/pages/admin/HistoryView.vue'),
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'История',
      },
    },
    {
      path: '/admin/user',
      name: RouteNamesEnum.user,
      component: () => import('@/pages/admin/UserView.vue'),
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Пользователь',
      },
    },
    {
      path: '/admin/bells',
      name: RouteNamesEnum.bells,
      component: () => import('@/pages/admin/BellsView.vue'),
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Звонки',
      },
    },
    {
      path: '/admin/groups',
      name: RouteNamesEnum.groups,
      component: () => import('@/pages/admin/GroupsView.vue'),
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Группы',
      },
    },
    {
      path: '/admin/schedules/main',
      name: RouteNamesEnum.schedules,
      component: () => import('@/pages/admin/MainSchedulesView.vue'),
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Основное расписание',
      },
    },
    {
      path: '/admin/schedules/changes',
      name: RouteNamesEnum.schedulesChanges,
      component: () => import('@/pages/admin/ChangesSchedulesView.vue'),
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Расписание',
      },
    },
    {
      path: '/admin/subjects',
      name: RouteNamesEnum.subjects,
      component: () => import('@/pages/admin/SubjectsView.vue'),
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Предметы',
      },
    },
    {
      path: '/admin/teachers',
      name: RouteNamesEnum.teachers,
      component: () => import('@/pages/admin/TeachersView.vue'),
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Преподаватели',
      },
    },
    {
      path: '/admin/semesters',
      name: RouteNamesEnum.semesters,
      component: () => import('@/pages/admin/SemestersView.vue'),
      meta: {
        layout: AppLayoutsEnum.admin,
        title: 'Семестры',
      },
    },
    {
      path: '/admin/buildings',
      name: RouteNamesEnum.buildings,
      component: () => import('@/pages/admin/BuildingsView.vue'),
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
  try {
    const response = await axios.get('/api/user');
    authStore.user = response.data;
    if (to.path === '/admin/login') return next(from.path);
  } catch (e: unknown) {
    if (axios.isAxiosError(e)) {
      if (
        e.response?.status === 401 &&
        to.path !== '/admin/login' &&
        to.meta.layout === 'admin'
      ) {
        next('/admin/login');
        return;
      }
    } else {
      console.error('Ошибка beforeEach:', e);
    }
  }

  next();
});

interface RouteMeta {
  title?: string;
  description?: string;
}

router.beforeEach(to => {
  const { title, description }: RouteMeta = to.meta as RouteMeta;
  const defaultTitle = 'Пары РКЭ';
  const defaultDescription =
    'Расписание учебного процесса Рязанского Колледжа Электроники';

  document.title = title ? `${title} | ${defaultTitle}` : defaultTitle;

  const descriptionElement = document.querySelector(
    'head meta[name="description"]'
  );

  descriptionElement!.setAttribute(
    'content',
    description || defaultDescription
  );
});

export default router;
