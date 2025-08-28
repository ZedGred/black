@extends('layouts.app')

@section('title', $title ?? 'Dashboard')

@section('dashboard-content')


    {{-- <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const logoutBtn = document.getElementById('btnLogout');

            if (logoutBtn) {
                logoutBtn.addEventListener("click", async (e) => {
                    e.preventDefault();

                    try {
                        // Panggil API logout
                        await axios.post('/api/logout', {}, {
                            headers: {
                                'Accept': 'application/json',
                                'X-XSRF-TOKEN': getCookie(
                                    'XSRF-TOKEN') // ambil token CSRF dari cookie
                            },
                            withCredentials: true // supaya cookie session ikut dikirim
                        });

                        // Hapus cookie auth (kalau ada token lain simpan manual)
                        document.cookie = "XSRF-TOKEN=; Max-Age=0; path=/;";

                        // Redirect ke halaman login
                        window.location.href = "/login";
                    } catch (error) {
                        console.error(error);
                        alert("Gagal logout, coba lagi.");
                    }
                });
            }

            // Helper ambil cookie by name
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
            }
        });
    </script>

@endsection
