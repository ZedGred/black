"use client";

import { FormEvent, useState } from "react";
import Link from "next/link";

type User = {
  username: string;
  email: string;
  password: string;
  confirm_password: string;
};

export default function Regisyer() {
  const [form, setForm] = useState<User>({
    username: "",
    email: "",
    password: "",
    confirm_password: "",
  });

  const [loading, Setloading] = useState<boolean>(false);
  const [error, setError] = useState<null | string>(null);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    Setloading(true);
    try {
      const res = await fetch("/api/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...form }),
      });

      if (!res.ok) {
        const data = await res.json();
        throw new Error(data.message || "Register Failed");
      }
    } catch (err: any) {
      setError(err.message);
    } finally {
      Setloading(false);
    }
  }

  return (
    <div className="flex min-h-screen flex-row">
      {/* Right Side - Banner / Info */}
      <div className="flex w-2/5 items-center justify-center bg-black text-white">
        <h2 className="text-9xl font-bold">BLACK</h2>
      </div>
      {/* Left Side - Login Form */}
      <div className="flex w-3/5 items-center justify-center bg-neutral-950 shadow-lg">
        <form onSubmit={handleSubmit} className="w-full max-w-md p-6">
          <h1 className="mb-6 text-center text-3xl font-bold text-white">
            Create an account
          </h1>

          {error && (
            <div className="mb-3 rounded bg-red-100 p-2 text-sm text-red-700">
              {error}
            </div>
          )}
          <div className="mb-4">
            <label
              htmlFor="username"
              className="mb-1 block text-sm font-medium"
            >
              Username
            </label>
            <input
              id="useername"
              name="username"
              type="text"
              placeholder="your name"
              className="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500"
              value={form.username}
              onChange={(e) =>
                setForm({ ...form, [e.target.name]: e.target.value })
              }
              required
            />
          </div>

          <div className="mb-4">
            <label htmlFor="email" className="mb-1 block text-sm font-medium">
              Email
            </label>
            <input
              id="email"
              name="email"
              type="email"
              placeholder="your@email.com"
              className="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500"
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
              className="mb-1 block text-sm font-medium"
            >
              Password
            </label>
            <input
              id="password"
              name="password"
              type="password"
              placeholder="********"
              className="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500"
              value={form.password}
              onChange={(e) =>
                setForm({ ...form, [e.target.name]: e.target.value })
              }
              required
            />
          </div>

          <div className="mb-6">
            <label
              htmlFor="confirm_password"
              className="mb-1 block text-sm font-medium"
            >
              Confirm Password
            </label>
            <input
              id="confirm_password"
              name="confirm_password"
              type="confirm_password"
              placeholder="********"
              className="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500"
              value={form.confirm_password}
              onChange={(e) =>
                setForm({ ...form, [e.target.name]: e.target.value })
              }
              required
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full rounded-3xl bg-white px-4 py-2 font-semibold text-black transition hover:bg-neutral-200 disabled:opacity-50 mb-2 "
          >
            {loading ? "Logging in..." : "create account"}
          </button>
          <div className="font-medium text-xs">
            <Link
              href={"/login"}
              className="ml-1 text-blue-300 hover:underline "
            >
              Already have an account? Login
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
