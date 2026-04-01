// lib/http.ts
import axios from "axios";
import { AuthUtils } from "@/lib/auth";

export const http = axios.create({
  baseURL: (process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000") + "/api/v1",
  headers: {
    "Content-Type": "application/json",
  },
  timeout: 10000,
});

http.interceptors.request.use(
  (config) => {
    const token = AuthUtils.getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

http.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      AuthUtils.removeToken();
      if (typeof window !== "undefined") {
        window.location.href = "/login";
      }
    }
    return Promise.reject(error);
  }
);
