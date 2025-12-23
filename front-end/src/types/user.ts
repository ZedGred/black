// src/types/user.ts
export interface User {
  id: string;
  email: string;
  name: string;
  role: 'admin' | 'user';
}


export type RegisterForm = {
  username: string;
  email: string;
  password: string;
  confirm_password: string;
};
