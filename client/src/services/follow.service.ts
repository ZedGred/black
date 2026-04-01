import { http } from "@/lib/http";
import { FollowResponse, FollowersResponse } from "@/types/notification";

export const followService = {
  async follow(username: string): Promise<FollowResponse> {
    const response = await http.post<FollowResponse>("/follow", { username });
    return response.data;
  },

  async unfollow(username: string): Promise<FollowResponse> {
    const response = await http.post<FollowResponse>("/unfollow", { username });
    return response.data;
  },

  async checkFollow(username: string): Promise<{
    success: boolean;
    data: { is_following: boolean; followers_count: number; following_count: number };
  }> {
    const response = await http.get(`/users/${username}/follow/check`);
    return response.data;
  },

  async getFollowers(
    username: string,
    params?: { page?: number; per_page?: number }
  ): Promise<FollowersResponse> {
    const response = await http.get<FollowersResponse>(
      `/users/${username}/followers`,
      { params }
    );
    return response.data;
  },

  async getFollowing(
    username: string,
    params?: { page?: number; per_page?: number }
  ): Promise<FollowersResponse> {
    const response = await http.get<FollowersResponse>(
      `/users/${username}/following`,
      { params }
    );
    return response.data;
  },
};
