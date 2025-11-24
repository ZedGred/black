import React from "react";

export default function Footer() {
  return (
    <footer className="fixed bottom-0 bg-black text-white py-6 mt-10 border-t-2 border-gray-500  w-full">
      <div className="container mx-auto text-center">
        <p>&copy; {new Date().getFullYear()} MyWebsite. All rights reserved.</p>
        <div className="flex justify-center space-x-4 mt-2">
          <a href="#" className="hover:underline">Privacy Policy</a>
          <a href="#" className="hover:underline">Terms of Service</a>
          <a href="#" className="hover:underline">Contact</a>
        </div>
      </div>
    </footer>
  );
};


