import Link from "next/link";
import LandingLayout from "@/layouts/landing";
import { followService } from "@/services/follow.service";
import { Follower } from "@/types/notification";
import { useEffect, useState } from "react";
import { useParams } from "next/navigation";

type FollowersListPageProps = {
  type: "followers" | "following";
};

export default function FollowersListPage({ type }: FollowersListPageProps) {
  const params = useParams();
  const username = params.username as string;
  const [followers, setFollowers] = useState<Follower[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);

  useEffect(() => {
    fetchFollowers();
  }, [page, type]);

  const fetchFollowers = async () => {
    try {
      setLoading(true);
      const response =
        type === "followers"
          ? await followService.getFollowers(username, { page, per_page: 20 })
          : await followService.getFollowing(username, { page, per_page: 20 });

      if (page === 1) {
        setFollowers(response.data);
      } else {
        setFollowers((prev) => [...prev, ...response.data]);
      }

      setHasMore(response.meta?.current_page < response.meta?.last_page);
    } catch (error) {
      console.error(`Failed to fetch ${type}:`, error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <LandingLayout>
      <div className="pt-24 pb-12 px-4 max-w-2xl mx-auto">
        <div className="flex items-center gap-4 mb-8">
          <Link href={`/users/${username}`} className="text-gray-400 hover:text-white">
            &larr; Back to {username}
          </Link>
        </div>

        <h1 className="text-3xl font-bold text-white mb-6">
          {type === "followers" ? "Followers" : "Following"}
        </h1>

        {loading && followers.length === 0 ? (
          <div className="text-center text-gray-400 py-12">Loading...</div>
        ) : followers.length === 0 ? (
          <div className="text-center text-gray-400 py-12">
            <p className="text-xl">No {type} yet</p>
          </div>
        ) : (
          <div className="space-y-3">
            {followers.map((user) => (
              <Link
                key={user.id}
                href={`/users/${user.username}`}
                className="flex items-center gap-4 p-4 bg-gray-900 rounded-lg hover:bg-gray-800 border border-gray-800"
              >
                <div className="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center overflow-hidden">
                  {user.avatar ? (
                    <img src={user.avatar} alt="" className="w-full h-full object-cover" />
                  ) : (
                    <span className="text-lg">{user.name[0]}</span>
                  )}
                </div>
                <div>
                  <p className="text-white font-medium">{user.name}</p>
                  <p className="text-gray-400 text-sm">@{user.username}</p>
                </div>
              </Link>
            ))}
          </div>
        )}

        {hasMore && (
          <div className="text-center mt-6">
            <button
              onClick={() => setPage((p) => p + 1)}
              disabled={loading}
              className="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50"
            >
              {loading ? "Loading..." : "Load More"}
            </button>
          </div>
        )}
      </div>
    </LandingLayout>
  );
}
