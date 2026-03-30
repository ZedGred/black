import { User } from '@/types/user';

export const AuthUtils = {
  setToken: (token: string) => {
    localStorage.setItem('token', token);
  },
  getToken: (): string | null => {
    return localStorage.getItem('token');
  },
  removeToken: () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  },
  isAuthenticated: (): boolean => {
    return !!localStorage.getItem('token');
  },
  setUser: (user: User) => {
    localStorage.setItem('user', JSON.stringify(user));
  },
  getUser: () => {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  },
  getAuthHeader: () => {
    const token = localStorage.getItem('token');
    return token ? { Authorization: `Bearer ${token}` } : {};
  },
};

export async function fetchWithAuth(url: string, options: RequestInit = {}) {
  const token = AuthUtils.getToken();
  
  const headers = {
    'Content-Type': 'application/json',
    ...(token && { Authorization: `Bearer ${token}` }),
    ...options.headers,
  };

  const response = await fetch(url, {
    ...options,
    headers,
  });

  if (response.status === 401) {
    AuthUtils.removeToken();
    window.location.href = '/login';
  }

  return response;
}