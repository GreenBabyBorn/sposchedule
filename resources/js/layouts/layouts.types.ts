export enum AppLayoutsEnum {
  default = 'default',
  login = 'login',
  admin = 'admin',
}

export const AppLayoutToFileMap: Record<AppLayoutsEnum, string> = {
  default: 'AppLayoutDefault.vue',
  login: 'AppLayoutLogin.vue',
  admin: 'AppLayoutAdmin.vue',
};
