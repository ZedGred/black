"use client"

import Link from "next/link";

export default function Navbar() {
    return (
        <nav className="fixed w-full p-4 bg-black/80 text-white flex justify-between items-center border-b-2 border-gray-800 backdrop-blu   r-md z-50">
            <div className="mx-30 text-2xl font-bold">Black</div>
            <div className="space-x-4">
                <Link href="/login" className="px-4 py-2 rounded text-sm"> About </Link>
                <Link href="/login" className="px-4 py-2 rounded text-sm mx-10"> Sign in </Link>
                <Link href="/register" className="px-4 py-2 bg-white rounded-3xl text-black text-sm"> Get Started </Link>
            </div>
        </nav>
    )
}
