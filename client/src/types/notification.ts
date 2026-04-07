export interface Notification {
  id: string;
  user_id: string;
  type: string;
  title: string;
  message: string;
  is_read: boolean;
  data?: {
    type: string;
    id: string;
    title?: string;
  };
  created_at: string;
}

export interface NotificationResponse {
  success: boolean;
  message: string;
  data: Notification[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface UnreadCountResponse {
  success: boolean;
  message: string;
  data: number;
}

export interface Follower {
  id: string;
  name: string;
  username: string;
  avatar?: string;
  bio?: string;
}

export interface FollowResponse {
  success: boolean;
  message: string;
  data: {
    is_following: boolean;
    followers_count: number;
    following_count: number;
  };
}

export interface FollowersResponse {
  success: boolean;
  message: string;
  data: Follower[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}
