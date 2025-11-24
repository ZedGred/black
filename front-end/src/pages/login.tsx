"use client";

import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";
import Link from "next/link";
import { AuthUtils } from "@/lib/auth";
import toast from "react-hot-toast";

type User = {
  email: string;
  password: string;
};

export default function Login() {
  const [form, setForm] = useState<User>({
    email: "",
    password: "",
  });

  const router = useRouter();
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<null | string>(null);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_URL}/api/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
      toast.success("berhasil")
      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || "Login failed");
      }

      AuthUtils.setToken(data.data.token.access_token);

      toast.success("Login berhasil!");
      router.push("/");
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : "An error occurred";

      setError(errorMessage);
      toast.error(errorMessage);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="flex min-h-screen flex-row">
      {/* Left Side - Banner / Info */}
      <div className="flex w-2/5 items-center justify-center bg-black text-white">
        <h2 className="text-9xl font-bold">BLACK</h2>
      </div>

      {/* Right Side - Login Form */}
      <div className="flex w-3/5 items-center justify-center bg-neutral-950 shadow-lg">
        <form onSubmit={handleSubmit} className="w-full max-w-md p-6">
          <h1 className="mb-6 text-center text-3xl font-bold text-white">
            Sign in to Black
          </h1>

          {error && (
            <div className="mb-3 rounded bg-red-100 p-2 text-sm text-red-700">
              {error}
            </div>
          )}

          <div className="mb-4">
            <label
              htmlFor="email"
              className="mb-1 block text-sm font-medium text-white"
            >
              Email
            </label>
            <input
              id="email"
              name="email"
              type="email"
              placeholder="your@email.com"
              className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              value={form.email}
              onChange={(e) =>
                setForm({ ...form, [e.target.name]: e.target.value })
              }
              required
            />
          </div>

          <div className="mb-6">
            <label
              htmlFor="password"
              className="mb-1 block text-sm font-medium text-white"
            >
              Password
            </label>
            <input
              id="password"
              name="password"
              type="password"
              placeholder="********"
              className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              value={form.password}
              onChange={(e) =>
                setForm({ ...form, [e.target.name]: e.target.value })
              }
              required
            />
            <Link
              href="/reset-password"
              className="mt-2 inline-block text-xs font-medium text-blue-300 hover:underline"
            >
              Forgot Your Password?
            </Link>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="mb-2 w-full rounded-3xl bg-white px-4 py-2 font-semibold text-black transition hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {loading ? "Logging in..." : "Log In"}
          </button>

          <div className="text-xs font-medium text-white">
            Need an account?
            <Link
              href="/register"
              className="ml-1 text-blue-300 hover:underline"
            >
              Register
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
