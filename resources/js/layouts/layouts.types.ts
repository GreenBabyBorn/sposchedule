export enum AppLayoutsEnum {
  default = 'default',
  public = 'public',
  admin = 'admin',
}

export const AppLayoutToFileMap: Record<AppLayoutsEnum, string> = {
  default: 'AppLayoutDefault.vue',
  public: 'AppLayoutPublic.vue',
  admin: 'AppLayoutAdmin.vue',
};
