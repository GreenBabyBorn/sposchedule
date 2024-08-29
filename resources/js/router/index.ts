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
import AuthView from '../pages/Login.vue';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: HomeView },
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
      authStore.logout();
      return next('/admin/login');
    }
  }

  // Разрешаем переход на целевой маршрут
  next();
});

router.beforeEach(to => {
  const { title, description }: any = to.meta;
  const defaultTitle = 'Пары РКЭ';
  const defaultDescription = 'Система управления расписанием';

  document.title = `${defaultTitle} | ${title}` || defaultTitle;

  const descriptionElement = document.querySelector(
    'head meta[name="description"]'
  );

  descriptionElement.setAttribute('content', description || defaultDescription);
});

export default router;
