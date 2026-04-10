"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import LandingLayout from "@/layouts/landing";
import { articleService } from "@/services/article.service";
import { AuthUtils } from "@/lib/auth";
import { format } from "date-fns";
import { FileText, Edit, Trash2, BookOpen, Clock, Eye, Plus } from "lucide-react";
import { Button } from "@/components/ui/button";
import toast from "react-hot-toast";

export default function StoriesPage() {
  const router = useRouter();
  const [published, setPublished] = useState<any[]>([]);
  const [drafts, setDrafts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<"published" | "drafts">("published");
  const [publishing, setPublishing] = useState<string | null>(null);
  const [deleting, setDeleting] = useState<string | null>(null);

  useEffect(() => {
    if (!AuthUtils.isAuthenticated()) {
      router.push("/login");
      return;
    }
    fetchStories();
  }, []);

  const fetchStories = async () => {
    try {
      setLoading(true);
      const [pubRes, draftRes] = await Promise.all([
        articleService.getMyArticles(),
        articleService.getMyDrafts(),
      ]);
      setPublished(pubRes.data);
      setDrafts(draftRes.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handlePublish = async (id: string) => {
    try {
      setPublishing(id);
      await articleService.publishDraft(id);
      toast.success("Story published!");
      fetchStories();
    } catch (err: any) {
      toast.error(err?.response?.data?.message || "Failed to publish");
    } finally {
      setPublishing(null);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this story?")) return;
    try {
      setDeleting(id);
      await articleService.deleteArticle(id);
      toast.success("Story deleted");
      fetchStories();
    } catch (err) {
      toast.error("Failed to delete");
    } finally {
      setDeleting(null);
    }
  };

  const ArticleCard = ({ article, isDraft = false }: { article: any; isDraft?: boolean }) => (
    <div className="group flex gap-4 py-6 border-b border-gray-800/50 last:border-0">
      {article.thumbnail && (
        <div className="flex-shrink-0 w-28 h-20 rounded-lg overflow-hidden bg-gray-800">
          <img src={article.thumbnail} alt={article.title} className="w-full h-full object-cover" />
        </div>
      )}
      <div className="flex-1 min-w-0">
        <div className="flex items-start justify-between gap-3">
          <div className="flex-1 min-w-0">
            <h3 className="font-semibold text-white text-lg leading-tight line-clamp-2 mb-1">
              {article.title}
            </h3>
            {article.excerpt && (
              <p className="text-gray-400 text-sm line-clamp-2 mb-2">{article.excerpt}</p>
            )}
            <div className="flex items-center gap-3 text-xs text-gray-500">
              {isDraft ? (
                <span className="flex items-center gap-1 text-yellow-500">
                  <Clock size={12} /> Draft
                </span>
              ) : (
                <>
                  <span className="flex items-center gap-1">
                    <BookOpen size={12} /> {article.likes_count || 0} likes
                  </span>
                  <span>{article.comments_count || 0} comments</span>
                  {article.published_at && (
                    <span>{format(new Date(article.published_at), "MMM d, yyyy")}</span>
                  )}
                </>
              )}
            </div>
          </div>
          <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
            {isDraft && (
              <button
                onClick={() => handlePublish(article.id)}
                disabled={publishing === article.id}
                className="px-3 py-1.5 text-xs bg-green-700 hover:bg-green-600 text-white rounded-full font-medium transition-colors disabled:opacity-50"
              >
                {publishing === article.id ? "Publishing..." : "Publish"}
              </button>
            )}
            {!isDraft && (
              <Link
                href={`/articles/${article.slug || article.id}`}
                className="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-full transition-colors"
                title="View"
              >
                <Eye size={16} />
              </Link>
            )}
            <Link
              href={`/articles/edit/${article.id}`}
              className="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-full transition-colors"
              title="Edit"
            >
              <Edit size={16} />
            </Link>
            <button
              onClick={() => handleDelete(article.id)}
              disabled={deleting === article.id}
              className="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-700 rounded-full transition-colors disabled:opacity-50"
              title="Delete"
            >
              <Trash2 size={16} />
            </button>
          </div>
        </div>
      </div>
    </div>
  );

  return (
    <LandingLayout>
      <div className="pt-24 pb-16 px-4 max-w-3xl mx-auto">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold text-white">Your Stories</h1>
            <p className="text-gray-400 mt-1">Manage your articles and drafts</p>
          </div>
          <Link
            href="/articles/create"
            className="flex items-center gap-2 px-4 py-2 bg-white text-black rounded-full font-medium hover:bg-gray-200 transition-colors text-sm"
          >
            <Plus size={16} /> Write a Story
          </Link>
        </div>

        {/* Tabs */}
        <div className="flex gap-6 mb-8 border-b border-gray-800">
          {(["published", "drafts"] as const).map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`pb-3 text-sm font-medium capitalize transition-colors ${
                activeTab === tab
                  ? "text-white border-b-2 border-white"
                  : "text-gray-400 hover:text-gray-200"
              }`}
            >
              {tab}
              <span className="ml-2 text-xs bg-gray-800 px-2 py-0.5 rounded-full">
                {tab === "published" ? published.length : drafts.length}
              </span>
            </button>
          ))}
        </div>

        {/* Content */}
        {loading ? (
          <div className="space-y-6">
            {[1, 2, 3].map((i) => (
              <div key={i} className="animate-pulse">
                <div className="h-6 bg-gray-800 rounded w-3/4 mb-2" />
                <div className="h-4 bg-gray-800 rounded w-1/2" />
              </div>
            ))}
          </div>
        ) : activeTab === "published" ? (
          published.length === 0 ? (
            <div className="text-center py-20">
              <FileText size={48} className="mx-auto mb-4 text-gray-600" />
              <p className="text-gray-400 text-xl mb-2">No published stories yet</p>
              <p className="text-gray-500 mb-6">Write your first story and share it with the world</p>
              <Link
                href="/articles/create"
                className="inline-flex items-center gap-2 px-6 py-3 bg-white text-black rounded-full font-medium hover:bg-gray-200"
              >
                <Plus size={18} /> Start Writing
              </Link>
            </div>
          ) : (
            <div>
              {published.map((a) => (
                <ArticleCard key={a.id} article={a} />
              ))}
            </div>
          )
        ) : drafts.length === 0 ? (
          <div className="text-center py-20">
            <Clock size={48} className="mx-auto mb-4 text-gray-600" />
            <p className="text-gray-400 text-xl mb-2">No drafts</p>
            <p className="text-gray-500">Saved drafts will appear here</p>
          </div>
        ) : (
          <div>
            {drafts.map((a) => (
              <ArticleCard key={a.id} article={a} isDraft />
            ))}
          </div>
        )}
      </div>
    </LandingLayout>
  );
}
