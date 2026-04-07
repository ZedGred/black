import { http } from "@/lib/http";
import {
  LoginCredentials,
  RegisterData,
  AuthResponse,
  AuthUser,
} from "@/types/user";

export const authService = {
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response = await http.post<AuthResponse>("/login", credentials);
    return response.data;
  },

  async register(data: RegisterData): Promise<AuthResponse> {
    const response = await http.post<AuthResponse>("/register/users", data);
    return response.data;
  },

  async me(): Promise<{ success: boolean; data: AuthUser }> {
    const response = await http.get("/me");
    return response.data;
  },

  async logout(): Promise<void> {
    await http.post("/logout");
  },

  async refreshToken(
    refreshToken: string
  ): Promise<{ data: { token: { access_token: string } } }> {
    const response = await http.post("/refresh/token", {
      refresh_token: refreshToken,
    });
    return response.data;
  },

  getGoogleAuthUrl: (): string => {
    const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";
    return `${baseUrl}/api/auth/google`;
  },
};
