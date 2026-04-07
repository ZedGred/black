"use client";

import { useEffect, useState } from "react";
import { useRouter, useParams } from "next/navigation";
import Link from "next/link";
import LandingLayout from "@/layouts/landing";
import { articleService } from "@/services/article.service";
import { followService } from "@/services/follow.service";
import { AuthUtils } from "@/lib/auth";
import { Article } from "@/types/article";
import { Follower } from "@/types/notification";
import { format } from "date-fns";
import { UserPlus, UserMinus, Calendar, FileText } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function UserProfilePage() {
  const params = useParams();
  const router = useRouter();
  const [user, setUser] = useState<Follower | null>(null);
  const [articles, setArticles] = useState<Article[]>([]);
  const [loading, setLoading] = useState(true);
  const [isFollowing, setIsFollowing] = useState(false);
  const [followersCount, setFollowersCount] = useState(0);
  const [followingCount, setFollowingCount] = useState(0);
  const [activeTab, setActiveTab] = useState<"articles" | "followers" | "following">("articles");

  const username = params.username as string;
  const currentUser = AuthUtils.getUser();
  const isOwnProfile = currentUser?.username === username;

  useEffect(() => {
    if (username) {
      fetchData();
    }
  }, [username]);

  const fetchData = async () => {
    try {
      setLoading(true);
      
      const articlesResponse = await articleService.getUserArticles(username, { per_page: 10 });
      setArticles(articlesResponse.data);
      
      if (!isOwnProfile) {
        const followCheck = await followService.checkFollow(username);
        setIsFollowing(followCheck.data.is_following);
        setFollowersCount(followCheck.data.followers_count);
        setFollowingCount(followCheck.data.following_count);
      }
    } catch (error) {
      console.error("Failed to fetch user data:", error);
      router.push("/articles");
    } finally {
      setLoading(false);
    }
  };

  const handleFollow = async () => {
    try {
      if (isFollowing) {
        const response = await followService.unfollow(username);
        setIsFollowing(false);
        setFollowersCount(response.data.followers_count);
      } else {
        const response = await followService.follow(username);
        setIsFollowing(true);
        setFollowersCount(response.data.followers_count);
      }
    } catch (error) {
      console.error("Failed to follow/unfollow:", error);
    }
  };

  if (loading) {
    return (
      <LandingLayout>
        <div className="pt-24 flex justify-center items-center min-h-screen">
          <div className="text-white">Loading...</div>
        </div>
      </LandingLayout>
    );
  }

  return (
    <LandingLayout>
      <div className="pt-24 pb-12 px-4 max-w-4xl mx-auto">
        <div className="bg-gray-900 rounded-lg p-8 mb-8 border border-gray-800">
          <div className="flex items-start justify-between">
            <div className="flex items-center gap-6">
              <div className="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center overflow-hidden">
                {user?.avatar ? (
                  <img src={user.avatar} alt="" className="w-full h-full object-cover" />
                ) : (
                  <span className="text-4xl">{username[0]?.toUpperCase()}</span>
                )}
              </div>
              <div>
                <h1 className="text-3xl font-bold text-white">{user?.name || username}</h1>
                <p className="text-gray-400">@{username}</p>
                {user?.bio && <p className="text-gray-300 mt-2">{user.bio}</p>}
              </div>
            </div>
            
            {!isOwnProfile && (
              <Button
                onClick={handleFollow}
                variant={isFollowing ? "outline" : "default"}
              >
                {isFollowing ? (
                  <>
                    <UserMinus size={18} className="mr-2" />
                    Following
                  </>
                ) : (
                  <>
                    <UserPlus size={18} className="mr-2" />
                    Follow
                  </>
                )}
              </Button>
            )}
          </div>
          
          <div className="flex items-center gap-6 mt-6 pt-6 border-t border-gray-800">
            <div className="flex items-center gap-2">
              <FileText size={18} className="text-gray-400" />
              <span className="text-white font-medium">{articles.length}</span>
              <span className="text-gray-400">Articles</span>
            </div>
            <Link href={`/users/${username}/followers`} className="flex items-center gap-2 hover:text-white text-gray-400">
              <span className="text-white font-medium">{followersCount}</span>
              <span>Followers</span>
            </Link>
            <Link href={`/users/${username}/following`} className="flex items-center gap-2 hover:text-white text-gray-400">
              <span className="text-white font-medium">{followingCount}</span>
              <span>Following</span>
            </Link>
          </div>
        </div>

        <div className="flex gap-4 mb-6 border-b border-gray-800">
          <button
            onClick={() => setActiveTab("articles")}
            className={`pb-3 px-2 ${
              activeTab === "articles"
                ? "text-white border-b-2 border-white"
                : "text-gray-400"
            }`}
          >
            Articles
          </button>
          <button
            onClick={() => setActiveTab("followers")}
            className={`pb-3 px-2 ${
              activeTab === "followers"
                ? "text-white border-b-2 border-white"
                : "text-gray-400"
            }`}
          >
            Followers
          </button>
          <button
            onClick={() => setActiveTab("following")}
            className={`pb-3 px-2 ${
              activeTab === "following"
                ? "text-white border-b-2 border-white"
                : "text-gray-400"
            }`}
          >
            Following
          </button>
        </div>

        {activeTab === "articles" && (
          <div className="space-y-4">
            {articles.length === 0 ? (
              <div className="text-center text-gray-400 py-8">
                <p>No articles yet</p>
              </div>
            ) : (
              articles.map((article) => (
                <Link
                  key={article.id}
                  href={`/articles/${article.slug || article.id}`}
                  className="block bg-gray-900 rounded-lg p-4 hover:bg-gray-800 border border-gray-800"
                >
                  <h3 className="text-xl font-semibold text-white">{article.title}</h3>
                  <div className="flex items-center gap-4 mt-2 text-sm text-gray-400">
                    <span>{article.likes_count || 0} likes</span>
                    <span>{article.comments_count || 0} comments</span>
                    <span>
                      {article.published_at
                        ? format(new Date(article.published_at), "MMM d, yyyy")
                        : ""}
                    </span>
                  </div>
                </Link>
              ))
            )}
          </div>
        )}

        {activeTab === "followers" && (
          <div className="text-center text-gray-400 py-8">
            <Link href={`/users/${username}/followers`} className="text-blue-400 hover:underline">
              View all followers
            </Link>
          </div>
        )}

        {activeTab === "following" && (
          <div className="text-center text-gray-400 py-8">
            <Link href={`/users/${username}/following`} className="text-blue-400 hover:underline">
              View all following
            </Link>
          </div>
        )}
      </div>
    </LandingLayout>
  );
}
