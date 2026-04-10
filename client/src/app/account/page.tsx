"use client";

import { useEffect, useState, useRef } from "react";
import { useRouter } from "next/navigation";
import LandingLayout from "@/layouts/landing";
import { profileService, articleService } from "@/services/article.service";
import { AuthUtils } from "@/lib/auth";
import { format } from "date-fns";
import { Camera, Save, BookOpen, FileText, Users, UserCheck, Loader2, Link as LinkIcon } from "lucide-react";
import Link from "next/link";
import toast from "react-hot-toast";

export default function AccountPage() {
  const router = useRouter();
  const [user, setUser] = useState<any>(null);
  const [articles, setArticles] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [activeTab, setActiveTab] = useState<"profile" | "articles">("profile");
  const [form, setForm] = useState({ name: "", bio: "", avatar: "" });

  useEffect(() => {
    if (!AuthUtils.isAuthenticated()) {
      router.push("/login");
      return;
    }
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [me, myArticles] = await Promise.all([
        profileService.getMe(),
        articleService.getMyArticles(),
      ]);
      const u = me.user ?? me;
      setUser(u);
      setForm({ name: u.name || "", bio: u.bio || "", avatar: u.avatar || "" });
      setArticles(myArticles.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      setSaving(true);
      const res = await profileService.updateProfile(form);
      const updated = res.data ?? res;
      setUser(updated);
      // Update localStorage cache
      const cached = AuthUtils.getUser();
      if (cached) AuthUtils.setUser({ ...cached, ...updated });
      toast.success("Profile updated successfully!");
    } catch (err: any) {
      toast.error(err?.response?.data?.message || "Failed to update profile");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <LandingLayout>
        <div className="pt-24 flex items-center justify-center min-h-screen">
          <Loader2 className="animate-spin text-gray-400" size={32} />
        </div>
      </LandingLayout>
    );
  }

  return (
    <LandingLayout>
      <div className="pt-24 pb-16 px-4 max-w-4xl mx-auto">
        {/* Profile Header */}
        <div className="relative mb-8">
          <div className="h-40 bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 rounded-2xl border border-gray-800" />
          <div className="absolute -bottom-12 left-8 flex items-end gap-4">
            <div className="relative group">
              <div className="w-24 h-24 rounded-full border-4 border-black bg-gray-700 flex items-center justify-center overflow-hidden">
                {form.avatar ? (
                  <img src={form.avatar} alt={user?.name} className="w-full h-full object-cover" />
                ) : (
                  <span className="text-3xl font-bold text-white">{user?.name?.[0]?.toUpperCase()}</span>
                )}
              </div>
            </div>
          </div>
        </div>

        <div className="mt-16 mb-8">
          <h1 className="text-2xl font-bold text-white">{user?.name}</h1>
          {user?.bio && <p className="text-gray-400 mt-1">{user?.bio}</p>}
          <div className="flex items-center gap-6 mt-3 text-sm text-gray-500">
            <span className="flex items-center gap-1"><FileText size={14} /> {articles.length} stories</span>
            <span className="flex items-center gap-1"><Users size={14} /> {user?.followers_count ?? 0} followers</span>
            <span className="flex items-center gap-1"><UserCheck size={14} /> {user?.following_count ?? 0} following</span>
          </div>
        </div>

        {/* Tabs */}
        <div className="flex gap-6 border-b border-gray-800 mb-8">
          {(["profile", "articles"] as const).map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`pb-3 text-sm font-medium capitalize transition-colors ${
                activeTab === tab
                  ? "text-white border-b-2 border-white"
                  : "text-gray-400 hover:text-gray-200"
              }`}
            >
              {tab === "profile" ? "Edit Profile" : "My Articles"}
            </button>
          ))}
        </div>

        {activeTab === "profile" ? (
          <form onSubmit={handleSave} className="max-w-lg space-y-6">
            <div>
              <label className="block text-sm font-medium text-gray-400 mb-2">Display Name</label>
              <input
                type="text"
                value={form.name}
                onChange={(e) => setForm({ ...form, name: e.target.value })}
                className="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-gray-500 transition-colors"
                placeholder="Your display name"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-400 mb-2">Bio</label>
              <textarea
                value={form.bio}
                onChange={(e) => setForm({ ...form, bio: e.target.value })}
                className="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-gray-500 transition-colors resize-none"
                rows={3}
                placeholder="Write a short bio about yourself..."
                maxLength={500}
              />
              <p className="text-xs text-gray-600 mt-1">{form.bio.length}/500</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-400 mb-2">Avatar URL</label>
              <input
                type="url"
                value={form.avatar}
                onChange={(e) => setForm({ ...form, avatar: e.target.value })}
                className="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-gray-500 transition-colors"
                placeholder="https://example.com/your-photo.jpg"
              />
              {form.avatar && (
                <div className="mt-3 flex items-center gap-3">
                  <img src={form.avatar} alt="Preview" className="w-12 h-12 rounded-full object-cover border border-gray-700" />
                  <span className="text-xs text-gray-500">Avatar preview</span>
                </div>
              )}
            </div>

            <div className="pt-2">
              <button
                type="submit"
                disabled={saving}
                className="flex items-center gap-2 px-6 py-3 bg-white text-black rounded-full font-semibold hover:bg-gray-100 disabled:opacity-50 transition-colors"
              >
                {saving ? <Loader2 size={18} className="animate-spin" /> : <Save size={18} />}
                {saving ? "Saving..." : "Save Changes"}
              </button>
            </div>
          </form>
        ) : (
          <div className="space-y-1">
            {articles.length === 0 ? (
              <div className="text-center py-16">
                <BookOpen size={48} className="mx-auto mb-4 text-gray-600" />
                <p className="text-gray-400 text-xl mb-2">No articles published yet</p>
                <Link href="/articles/create" className="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-white text-black rounded-full font-medium hover:bg-gray-100 text-sm">
                  Write your first story
                </Link>
              </div>
            ) : (
              articles.map((article) => (
                <Link
                  key={article.id}
                  href={`/articles/${article.slug || article.id}`}
                  className="flex gap-4 py-5 border-b border-gray-800/50 group hover:bg-gray-900/30 px-3 rounded-xl transition-colors"
                >
                  {article.thumbnail && (
                    <div className="flex-shrink-0 w-24 h-16 rounded-lg overflow-hidden bg-gray-800">
                      <img src={article.thumbnail} alt="" className="w-full h-full object-cover" />
                    </div>
                  )}
                  <div className="flex-1 min-w-0">
                    <h3 className="font-semibold text-white line-clamp-1 group-hover:text-gray-200">{article.title}</h3>
                    {article.excerpt && <p className="text-gray-400 text-sm line-clamp-1 mt-0.5">{article.excerpt}</p>}
                    <div className="flex items-center gap-3 mt-2 text-xs text-gray-500">
                      <span>{article.likes_count || 0} likes</span>
                      <span>{article.comments_count || 0} comments</span>
                      {article.published_at && <span>{format(new Date(article.published_at), "MMM d, yyyy")}</span>}
                    </div>
                  </div>
                </Link>
              ))
            )}
          </div>
        )}
      </div>
    </LandingLayout>
  );
}
