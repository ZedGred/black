import { http } from "@/lib/http";
import { Article, Category, Comment, ApiResponse, PaginatedResponse } from "@/types/article";

export const articleService = {
  async getArticles(params?: {
    page?: number;
    per_page?: number;
    category?: string;
    status?: string;
  }): Promise<{ data: any[]; meta: any }> {
    const response = await http.get("/articles", { params });
    return {
      data: response.data.data.data,
      meta: response.data.data.meta
    };
  },

  async getArticle(idOrSlug: string): Promise<ApiResponse<Article>> {
    const response = await http.get(`/articles/${idOrSlug}`);
    return response.data;
  },

  async getUserArticles(
    username: string,
    params?: { page?: number; per_page?: number }
  ): Promise<ApiResponse<Article[]>> {
    const response = await http.get(`/users/${username}/articles`, { params });
    return response.data;
  },

  async createArticle(data: {
    title: string;
    content: string;
    excerpt?: string;
    thumbnail?: string;
    category_ids?: string[];
    status?: "draft" | "published";
  }): Promise<ApiResponse<Article>> {
    const response = await http.post("/articles", data);
    return response.data;
  },

  async updateArticle(
    id: string,
    data: Partial<{
      title: string;
      content: string;
      excerpt: string;
      thumbnail: string;
      category_ids: string[];
      status: string;
    }>
  ): Promise<ApiResponse<Article>> {
    const response = await http.put(`/articles/${id}`, data);
    return response.data;
  },

  async deleteArticle(id: string): Promise<ApiResponse<null>> {
    const response = await http.delete(`/articles/${id}`);
    return response.data;
  },

  async likeArticle(id: string): Promise<ApiResponse<null>> {
    const response = await http.post(`/articles/${id}/like`);
    return response.data;
  },

  async unlikeArticle(id: string): Promise<ApiResponse<null>> {
    const response = await http.delete(`/articles/${id}/like`);
    return response.data;
  },

  async getMyDrafts(params?: {
    page?: number;
    per_page?: number;
  }): Promise<ApiResponse<Article[]>> {
    const response = await http.get("/articles/my/drafts", { params });
    return response.data;
  },

  async getMyArticles(params?: {
    page?: number;
    per_page?: number;
  }): Promise<ApiResponse<Article[]>> {
    const response = await http.get("/articles/my/articles", { params });
    return response.data;
  },

  async publishDraft(id: string): Promise<ApiResponse<Article>> {
    const response = await http.post(`/articles/${id}/publish`);
    return response.data;
  },

  async getArticleComments(
    articleId: string,
    params?: { page?: number; per_page?: number }
  ): Promise<ApiResponse<Comment[]>> {
    const response = await http.get(`/articles/${articleId}/comments`, {
      params,
    });
    return response.data;
  },

  async createComment(
    articleId: string,
    data: { content: string; parent_id?: string }
  ): Promise<ApiResponse<Comment>> {
    const response = await http.post(`/comments/articles/${articleId}`, data);
    return response.data;
  },
};

export const categoryService = {
  async getCategories(): Promise<ApiResponse<Category[]>> {
    const response = await http.get("/categories");
    return response.data;
  },

  async getCategory(slug: string): Promise<ApiResponse<Category>> {
    const response = await http.get(`/categories/${slug}`);
    return response.data;
  },

  async createCategory(data: {
    name: string;
    slug?: string;
  }): Promise<ApiResponse<Category>> {
    const response = await http.post("/categories", data);
    return response.data;
  },

  async updateCategory(
    id: string,
    data: { name: string; slug?: string }
  ): Promise<ApiResponse<Category>> {
    const response = await http.put(`/categories/${id}`, data);
    return response.data;
  },

  async deleteCategory(id: string): Promise<ApiResponse<null>> {
    const response = await http.delete(`/categories/${id}`);
    return response.data;
  },
};
