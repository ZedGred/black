import { http } from "@/lib/http";
import {
  Notification,
  NotificationResponse,
  UnreadCountResponse,
} from "@/types/notification";

export const notificationService = {
  async getNotifications(params?: {
    page?: number;
    per_page?: number;
  }): Promise<{ data: any[]; meta: any; unread_count: number }> {
    const response = await http.get("/notifications", {
      params,
    });
    return {
      data: response.data.data.notifications.data,
      meta: response.data.data.notifications.meta,
      unread_count: response.data.data.unread_count
    };
  },

  async getUnreadCount(): Promise<number> {
    const response = await http.get("/notifications", {
      params: { per_page: 1 },
    });
    return response.data.data.unread_count;
  },

  async getNotification(id: string): Promise<{
    success: boolean;
    data: Notification;
  }> {
    const response = await http.get(`/notifications/${id}`);
    return response.data;
  },

  async markAsRead(id: string): Promise<{
    success: boolean;
    message: string;
  }> {
    const response = await http.post(`/notifications/${id}/read`);
    return response.data;
  },

  async markAllAsRead(): Promise<{
    success: boolean;
    message: string;
  }> {
    const response = await http.post("/notifications/mark-all-read");
    return response.data;
  },

  async deleteNotification(id: string): Promise<{
    success: boolean;
    message: string;
  }> {
    const response = await http.delete(`/notifications/${id}`);
    return response.data;
  },
};
