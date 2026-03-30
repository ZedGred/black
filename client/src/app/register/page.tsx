// src/register/page.tsx
"use client";

import { useState } from "react";
import Link from "next/link";
import AuthLayout from "@/layouts/auth";
import toast from 'react-hot-toast';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RegisterForm } from "@/types/user";


export default function Register() {
  const [form, setForm] = useState<RegisterForm>({
    username: "",
    email: "",
    password: "",
    confirm_password: "",
  });

  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<null | string>(null);

  if (error){
    toast.error(error)
  }

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const res = await fetch("/api/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(form),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data?.message ?? "Register failed");
      }

      // success handling
      // example:
      // router.push("/login");
      // toast.success("Register success");

    } catch (error: unknown) {
      if (error instanceof Error) {
        setError(error.message);
      } else {
        setError("Unexpected error occurred");
      }
    } finally {
      setLoading(false);
    }
  }

  return (
    <AuthLayout>
      {/* Right Side - Banner / Info */}
      <div className="flex w-2/5 items-center justify-center bg-black text-white">
        <h2 className="text-9xl font-bold">BLACK</h2>
      </div>
      {/* Left Side - Register Form */}
      <div className="flex w-3/5 items-center justify-center bg-neutral-950 shadow-lg">
        <form onSubmit={handleSubmit} className="w-full max-w-md p-6">
          <h1 className="mb-6 text-center text-3xl font-bold text-white">
            Create an account
          </h1>

          <div className="mb-4">
            <Label htmlFor="username" className="mb-1 block text-white">
              Username
            </Label>

            <Input
              id="username"
              name="username"
              type="text"
              placeholder="your name"
              className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              value={form.username}
              onChange={(e) =>
                setForm({ ...form, [e.target.name]: e.target.value })
              }
              required
            />
          </div>

          <div className="mb-4">
            <Label htmlFor="email" className="mb-1 block text-sm font-medium text-white">
              Email
            </Label>

            <Input
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

          <div className="mb-4">
            <Label htmlFor="password" className="mb-1 block text-sm font-medium text-white">
              Password
            </Label>

            <Input
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
          </div>

          <div className="mb-6">
            <Label htmlFor="confirm_password" className="mb-1 block text-sm font-medium text-white">
              Confirm Password
            </Label>

            <input
              id="confirm_password"
              name="confirm_password"
              type="password"
              placeholder="********"
              className="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              value={form.confirm_password}
              onChange={(e) =>
                setForm({ ...form, [e.target.name]: e.target.value })
              }
              required
            />
          </div>
          
          <Button 
            type="submit"
            disabled={loading}
            className="mb-2 w-full rounded-3xl bg-white px-4 py-2 font-semibold text-black transition hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {loading ? "Creating account..." : "Create Account"}
          </Button>

          <div className="text-xs font-medium text-white">
            Already have an account?
            <Link
              href="/login"
              className="ml-1 text-blue-300 hover:underline"
            >
              Login
            </Link>
          </div>
        </form>
      </div>
    </AuthLayout>
  );
}