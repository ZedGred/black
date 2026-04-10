"use client";

import { useState } from "react";
import Link from "next/link";
import LandingLayout from "@/layouts/landing";
import { http } from "@/lib/http";
import { Mail, ArrowLeft, CheckCircle, Loader2 } from "lucide-react";
import toast from "react-hot-toast";

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email.trim()) return;
    try {
      setLoading(true);
      await http.post("/forgot-password", { email });
      setSent(true);
    } catch (err: any) {
      toast.error(err?.response?.data?.message || "Failed to send reset email");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-black flex items-center justify-center px-4">
      <div className="w-full max-w-md">
        {sent ? (
          <div className="text-center">
            <div className="w-16 h-16 rounded-full bg-green-900/30 border border-green-700 flex items-center justify-center mx-auto mb-6">
              <CheckCircle className="text-green-400" size={32} />
            </div>
            <h1 className="text-2xl font-bold text-white mb-3">Check your email</h1>
            <p className="text-gray-400 mb-2">We sent a password reset link to</p>
            <p className="text-white font-medium mb-6">{email}</p>
            <p className="text-gray-500 text-sm mb-8">
              The link will expire in 1 hour. Check your spam folder if you don't see it.
            </p>
            <button
              onClick={() => setSent(false)}
              className="text-gray-400 hover:text-white text-sm underline"
            >
              Try a different email
            </button>
          </div>
        ) : (
          <>
            <Link href="/login" className="flex items-center gap-2 text-gray-400 hover:text-white mb-8 text-sm transition-colors w-fit">
              <ArrowLeft size={16} /> Back to login
            </Link>

            <div className="mb-8">
              <div className="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center mb-6">
                <Mail className="text-white" size={24} />
              </div>
              <h1 className="text-3xl font-bold text-white mb-2">Forgot password?</h1>
              <p className="text-gray-400">
                Enter your email and we'll send you a link to reset your password.
              </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-400 mb-2">
                  Email address
                </label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="you@example.com"
                  required
                  className="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-gray-500 transition-colors placeholder-gray-600"
                />
              </div>

              <button
                type="submit"
                disabled={loading || !email.trim()}
                className="w-full flex items-center justify-center gap-2 py-3 bg-white text-black rounded-xl font-semibold hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {loading ? (
                  <><Loader2 size={18} className="animate-spin" /> Sending...</>
                ) : (
                  <>Send Reset Link</>
                )}
              </button>
            </form>
          </>
        )}
      </div>
    </div>
  );
}
