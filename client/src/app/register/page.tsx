// src/register/page.tsx
"use client";

import { useState } from "react";
import Link from "next/link";
import AuthLayout from "@/layouts/auth";
import toast from 'react-hot-toast';

export default function Register() {
  const [form, setForm] = useState({
    name: "",
    email: "",
  });

  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<null | string>(null);
  const [success, setSuccess] = useState<boolean>(false);
  const [nameFocused, setNameFocused] = useState<boolean>(false);
  const [emailFocused, setEmailFocused] = useState<boolean>(false);

  if (error) {
    toast.error(error);
  }

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setSuccess(false);

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001'}/api/v1/register/users`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify(form),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data?.message ?? "Register failed");
      }

      setSuccess(true);
      toast.success("Verification email sent!");
    } catch (error: any) {
      setError(error.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <AuthLayout>
      {/* Left Side - Banner */}
      <div className="flex w-2/5 items-center justify-center bg-black text-white">
        <h2 className="text-9xl font-bold">BLACK</h2>
      </div>

      {/* Right Side - Register Form */}
      <div className="flex w-3/5 items-center justify-center bg-neutral-950 shadow-lg">
        {success ? (
          <div className="w-full max-w-md p-6 text-center">
            <h1 className="mb-4 text-3xl font-bold text-white">Check Your Email</h1>
            <p className="mb-6 text-gray-300">
              We&apos;ve sent a verification link to <strong>{form.email}</strong>. Please check
              your inbox and click the link to set up your password and complete your registration.
            </p>
            <Link href="/login" className="text-blue-300 hover:underline">
              Return to Login
            </Link>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="w-full max-w-md p-6">
            <h1 className="mb-6 text-center text-3xl font-bold text-white">Create an account</h1>

            {error && (
              <div className="mb-4 rounded bg-red-100 p-2 text-sm text-red-700">{error}</div>
            )}

            {/* Username Field with Floating Label */}
            <div className="mb-6">
              <div className="relative">
                <input
                  id="name"
                  name="name"
                  type="text"
                  className="w-full rounded-lg border border-neutral-600 bg-neutral-800 px-3 pb-2 pt-5 text-sm text-white placeholder-transparent focus:border-white focus:outline-none focus:ring-0"
                  placeholder="Your name"
                  value={form.name}
                  onChange={(e) => setForm({ ...form, [e.target.name]: e.target.value })}
                  onFocus={() => setNameFocused(true)}
                  onBlur={() => setNameFocused(false)}
                  required
                />
                <label
                  htmlFor="name"
                  className={`pointer-events-none absolute left-3 text-sm transition-all duration-200 ${
                    nameFocused || form.name
                      ? 'top-1.5 text-xs text-neutral-400'
                      : 'top-3.5 text-neutral-500'
                  }`}
                >
                  Username
                </label>
              </div>
            </div>

            {/* Email Field with Floating Label */}
            <div className="mb-6">
              <div className="relative">
                <input
                  id="email"
                  name="email"
                  type="email"
                  className="w-full rounded-lg border border-neutral-600 bg-neutral-800 px-3 pb-2 pt-5 text-sm text-white placeholder-transparent focus:border-white focus:outline-none focus:ring-0"
                  placeholder="your@email.com"
                  value={form.email}
                  onChange={(e) => setForm({ ...form, [e.target.name]: e.target.value })}
                  onFocus={() => setEmailFocused(true)}
                  onBlur={() => setEmailFocused(false)}
                  required
                />
                <label
                  htmlFor="email"
                  className={`pointer-events-none absolute left-3 text-sm transition-all duration-200 ${
                    emailFocused || form.email
                      ? 'top-1.5 text-xs text-neutral-400'
                      : 'top-3.5 text-neutral-500'
                  }`}
                >
                  Email
                </label>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="mb-2 w-full rounded-3xl bg-white px-4 py-2 font-semibold text-black transition hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {loading ? "Sending link..." : "Send Verification Link"}
            </button>

            <div className="my-4 flex items-center gap-2">
              <div className="h-px flex-1 bg-gray-600"></div>
              <span className="text-xs text-gray-400">or</span>
              <div className="h-px flex-1 bg-gray-600"></div>
            </div>

            <a
              href={`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001'}/api/v1/auth/google`}
              className="mb-6 flex w-full items-center justify-center gap-2 rounded-3xl border border-gray-500 bg-transparent px-4 py-2 font-semibold text-white transition hover:bg-gray-800"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
              </svg>
              Continue with Google
            </a>

            <div className="text-center text-xs font-medium text-white">
              Already have an account?
              <Link href="/login" className="ml-1 text-blue-300 hover:underline">
                Login
              </Link>
            </div>
          </form>
        )}
      </div>
    </AuthLayout>
  );
}