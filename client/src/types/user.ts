export interface User {
  id: string;
  name: string;
  username: string;
  email: string;
  avatar?: string;
  role: string;
  bio?: string;
  followers_count?: number;
  following_count?: number;
  created_at?: string;
}

export interface AuthUser extends User {
  permissions: string[];
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  username: string;
  email: string;
  password: string;
  confirm_password: string;
}

export interface AuthResponse {
  success: boolean;
  message: string;
  data: {
    user: AuthUser;
    token: {
      access_token: string;
      refresh_token: string;
      expires_in: number;
    };
  };
}
