export interface Article {
  id: string;
  title: string;
  slug: string;
  content: string;
  excerpt?: string;
  thumbnail?: string;
  status: "draft" | "published";
  published_at?: string;
  user_id: string;
  user?: {
    id: string;
    name: string;
    username: string;
    avatar?: string;
  };
  categories?: Category[];
  likes_count?: number;
  comments_count?: number;
  is_liked?: boolean;
  created_at: string;
  updated_at: string;
}

export interface Category {
  id: string;
  name: string;
  slug: string;
  user_id?: string;
  articles_count?: number;
  created_at: string;
}

export interface Comment {
  id: string;
  content: string;
  user_id: string;
  article_id: string;
  parent_id?: string;
  user?: {
    id: string;
    name: string;
    username: string;
    avatar?: string;
  };
  replies?: Comment[];
  likes_count?: number;
  is_liked?: boolean;
  created_at: string;
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
  errors?: Record<string, string[]>;
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}
