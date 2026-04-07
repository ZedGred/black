"use client";

import React from "react";

export default function Loading() {
  return (
    <div className="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-black">
      <div className="relative flex items-center justify-center">
        <svg
          className="w-full h-32 sm:h-48"
          viewBox="0 0 600 200"
          xmlns="http://www.w3.org/2000/svg"
        >
          <text
            x="50%"
            y="50%"
            dominantBaseline="middle"
            textAnchor="middle"
            className="text-7xl sm:text-8xl font-black uppercase tracking-[0.2em] write-animation"
            fill="transparent"
            stroke="white"
            strokeWidth="2.5"
            strokeDasharray="1500"
            strokeDashoffset="1500"
          >
            BLACK
          </text>
        </svg>
      </div>

      <style>{`
        .write-animation {
          animation: drawAndFill 2.5s cubic-bezier(0.7, 0, 0.3, 1) infinite alternate;
        }

        @keyframes drawAndFill {
          0% {
            stroke-dashoffset: 1500;
            fill: transparent;
            opacity: 0.5;
          }
          60% {
            stroke-dashoffset: 0;
            fill: transparent;
            opacity: 1;
            text-shadow: none;
          }
          100% {
            stroke-dashoffset: 0;
            fill: white;
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.8),
                         0 0 30px rgba(255, 255, 255, 0.5);
          }
        }
      `}</style>
    </div>
  );
}
