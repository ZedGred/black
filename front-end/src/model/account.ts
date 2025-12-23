// types/account.ts
export interface AccountData {
  username: string;
  email: string;
  password: string;
}

export interface AccountResponse {
  id: string;
  username: string;
  email: string;
  createdAt: string;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}
