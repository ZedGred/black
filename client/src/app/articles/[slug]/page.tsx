"use client";

import { useEffect, useState } from "react";
import { useRouter, useParams } from "next/navigation";
import LandingLayout from "@/layouts/landing";
import { articleService } from "@/services/article.service";
import { followService } from "@/services/follow.service";
import { Article, Comment } from "@/types/article";
import { format } from "date-fns";
import { Heart, MessageCircle, UserPlus, UserMinus } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function ArticleDetailPage() {
  const params = useParams();
  const router = useRouter();
  const [article, setArticle] = useState<Article | null>(null);
  const [comments, setComments] = useState<Comment[]>([]);
  const [loading, setLoading] = useState(true);
  const [isFollowing, setIsFollowing] = useState(false);
  const [commentText, setCommentText] = useState("");
  const [submittingComment, setSubmittingComment] = useState(false);

  const articleId = params.slug as string;

  useEffect(() => {
    if (articleId) {
      fetchArticle();
    }
  }, [articleId]);

  const fetchArticle = async () => {
    try {
      const response = await articleService.getArticle(articleId);
      setArticle(response.data);
      
      const commentsResponse = await articleService.getArticleComments(articleId);
      setComments(commentsResponse.data);
    } catch (error) {
      console.error("Failed to fetch article:", error);
      router.push("/articles");
    } finally {
      setLoading(false);
    }
  };

  const handleLike = async () => {
    try {
      if (article?.is_liked) {
        await articleService.unlikeArticle(article.id);
      } else {
        await articleService.likeArticle(article.id);
      }
      setArticle((prev) => prev ? ({
        ...prev,
        is_liked: !prev.is_liked,
        likes_count: (prev.likes_count || 0) + (prev.is_liked ? -1 : 1)
      }) : null);
    } catch (error) {
      console.error("Failed to like article:", error);
    }
  };

  const handleFollow = async () => {
    if (!article?.user?.username) return;
    try {
      if (isFollowing) {
        await followService.unfollow(article.user.username);
      } else {
        await followService.follow(article.user.username);
      }
      setIsFollowing(!isFollowing);
    } catch (error) {
      console.error("Failed to follow/unfollow:", error);
    }
  };

  const handleSubmitComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!commentText.trim()) return;
    
    try {
      setSubmittingComment(true);
      await articleService.createComment(article!.id, { content: commentText });
      setCommentText("");
      fetchArticle();
    } catch (error) {
      console.error("Failed to post comment:", error);
    } finally {
      setSubmittingComment(false);
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

  if (!article) {
    return (
      <LandingLayout>
        <div className="pt-24 flex justify-center items-center min-h-screen">
          <div className="text-white">Article not found</div>
        </div>
      </LandingLayout>
    );
  }

  return (
    <LandingLayout>
      <div className="pt-24 pb-12 px-4 max-w-4xl mx-auto">
        {article.thumbnail && (
          <div className="w-full h-64 md:h-96 rounded-lg overflow-hidden mb-8">
            <img
              src={article.thumbnail}
              alt={article.title}
              className="w-full h-full object-cover"
            />
          </div>
        )}

        <h1 className="text-4xl font-bold text-white mb-4">{article.title}</h1>

        <div className="flex items-center justify-between mb-6 pb-6 border-b border-gray-800">
          <div className="flex items-center gap-4">
            <div className="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center overflow-hidden">
              {article.user?.avatar ? (
                <img src={article.user.avatar} alt="" className="w-full h-full object-cover" />
              ) : (
                <span className="text-lg">{article.user?.name?.[0]}</span>
              )}
            </div>
            <div>
              <p className="text-white font-medium">{article.user?.name}</p>
              <p className="text-gray-400 text-sm">
                {article.published_at
                  ? format(new Date(article.published_at), "MMMM d, yyyy")
                  : ""}
              </p>
            </div>
          </div>
          
          <button
            onClick={handleFollow}
            className={`flex items-center gap-2 px-4 py-2 rounded-lg ${
              isFollowing
                ? "bg-gray-800 text-white hover:bg-gray-700"
                : "bg-white text-black hover:bg-gray-200"
            }`}
          >
            {isFollowing ? <UserMinus size={18} /> : <UserPlus size={18} />}
            {isFollowing ? "Following" : "Follow"}
          </button>
        </div>

        <div className="flex items-center gap-6 mb-8 text-gray-400">
          <button onClick={handleLike} className="flex items-center gap-2">
            <Heart
              size={20}
              className={article.is_liked ? "fill-red-500 text-red-500" : ""}
            />
            <span>{article.likes_count || 0}</span>
          </button>
          <div className="flex items-center gap-2">
            <MessageCircle size={20} />
            <span>{article.comments_count || 0}</span>
          </div>
        </div>

        <article className="max-w-none">
          <div dangerouslySetInnerHTML={{ __html: article.content }} className="article-content" />
          <style dangerouslySetInnerHTML={{ __html: `
            .article-content {
              line-height: 1.8;
              font-size: 1.125rem;
              color: #d1d5db;
            }
            .article-content p {
              margin-bottom: 1.5em;
            }
            .article-content h2 {
              font-size: 1.5rem;
              font-weight: bold;
              color: white;
              margin-top: 2em;
              margin-bottom: 1em;
            }
            .article-content h3 {
              font-size: 1.25rem;
              font-weight: bold;
              color: white;
              margin-top: 1.5em;
              margin-bottom: 0.75em;
            }
            .article-content img {
              max-width: 100%;
              border-radius: 0.5rem;
              margin: 2em auto;
            }
            .article-content iframe {
              max-width: 100%;
              border-radius: 0.5rem;
              margin: 2em auto;
              aspect-ratio: 16/9;
            }
            .article-content a {
              color: #3b82f6;
              text-decoration: underline;
            }
            .article-content blockquote {
              border-left: 4px solid #4b5563;
              padding-left: 1.25rem;
              color: #9ca3af;
              font-style: italic;
              margin: 1.5em 0;
            }
            .article-content pre {
              background-color: #111827;
              padding: 1.5rem;
              border-radius: 0.5rem;
              overflow-x: auto;
              margin: 1.5em 0;
            }
            .article-content code {
              background-color: #1f2937;
              padding: 0.2rem 0.4rem;
              border-radius: 0.25rem;
              font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
              font-size: 0.9em;
            }
            .article-content pre code {
              background-color: transparent;
              padding: 0;
            }
            .article-content ul {
              list-style-type: disc;
              padding-left: 1.5rem;
              margin-bottom: 1.5rem;
            }
            .article-content ol {
              list-style-type: decimal;
              padding-left: 1.5rem;
              margin-bottom: 1.5rem;
            }
          `}} />
        </article>

        {/* Comments Section */}
        <div className="mt-12 pt-8 border-t border-gray-800">
          <h3 className="text-2xl font-bold text-white mb-6">Comments</h3>
          
          <form onSubmit={handleSubmitComment} className="mb-8">
            <textarea
              value={commentText}
              onChange={(e) => setCommentText(e.target.value)}
              placeholder="Write a comment..."
              className="w-full bg-gray-900 text-white rounded-lg p-4 border border-gray-800 focus:outline-none focus:border-gray-600"
              rows={3}
            />
            <Button
              type="submit"
              disabled={submittingComment || !commentText.trim()}
              className="mt-2"
            >
              {submittingComment ? "Posting..." : "Post Comment"}
            </Button>
          </form>

          <div className="space-y-6">
            {comments.map((comment) => (
              <div key={comment.id} className="flex gap-4">
                <div className="w-10 h-10 rounded-full bg-gray-700 flex-shrink-0 flex items-center justify-center">
                  {comment.user?.avatar ? (
                    <img src={comment.user.avatar} alt="" className="w-full h-full rounded-full" />
                  ) : (
                    <span>{comment.user?.name?.[0]}</span>
                  )}
                </div>
                <div className="flex-1">
                  <div className="flex items-center gap-2">
                    <span className="text-white font-medium">{comment.user?.name}</span>
                    <span className="text-gray-500 text-sm">
                      {format(new Date(comment.created_at), "MMM d, yyyy")}
                    </span>
                  </div>
                  <p className="text-gray-300 mt-1">{comment.content}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </LandingLayout>
  );
}
