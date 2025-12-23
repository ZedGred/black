"use client";

import { useState } from "react";
import LandingLayout from "@/layouts/landing";

export default function Home() {
  const [count, setCount] = useState<number>(0);

  const handleIncrease = () => {
    setCount((prev) => prev - 1);
    setCount((prev) => prev + 3);
  };

  const handleDecrease = () => {
    setCount((prev) => Math.max(prev - 1, 0));
  };

  return (
    <LandingLayout>
      <div className="flex min-h-screen items-center justify-center bg-black-100 flex-col space-y-5">
        {/* Gradient Header */}
        <div className="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 h-64 w-full flex items-center justify-center">
          <h1 className="text-9xl font-bold text-black/50 bg-clip-text  transition duration-300 hover:from-red-700 hover:animate-growShrink ">
            GREETINGS !
          </h1>
        </div>
        {/* Card */}
        {/* <div className="w-full max-w-sm p-8 rounded-2xl flex flex-col items-center space-y-4">
                <h1 className="text-2xl font-bold bg-gradient-to-r from-white to-blue-500 text-transparent bg-clip-text">
                    Hallo Next
                </h1>
                <span className="text-white font-bold">Jumlah: {count}</span>

                <div className="flex space-x-4">
                    <button
                        onClick={handleIncrease}
                        className="px-4 py-2 bg-white text-black rounded-2xl shadow hover:bg-gray-300 hover:scale-103 transition tracking-wide font-bold "
                    >
                        Increase
                    </button>
                    <button
                        onClick={handleDecrease}
                        className="px-4 py-2 bg-white text-black rounded-2xl shadow hover:bg-gray-300 hover:scale-103 transition duration-300 tracking-wide font-bold"
                    >
                        Decrease
                    </button>
                </div>
            </div>
             */}
      </div>
    </LandingLayout>
  );
}
