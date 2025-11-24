<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Default Title')</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <!-- Vendor Styles -->
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- Global Styles -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('styles')
</head>

<body>
    <div class="d-flex" style="min-height: 80vh; padding: 3rem;">
        @include('layouts.partials.sidebar')

        <div style="flex-grow: 1; padding-left: 2rem;">
            @yield('dashboard-content')
        </div>
    </div>

    <!-- Global JS -->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/axios-setup.js') }}"></script>


    <!-- Vendor JS -->
    <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>

    <!-- Custom JS -->\
    <script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
    
    {{-- <script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/custom/utilities/modals/upgrade-plan.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/custom/utilities/modals/create-app.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/custom/utilities/modals/new-target.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/custom/utilities/modals/users-search.js') }}"></script> --}}


    @stack('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const logoutBtn = document.getElementById('btnLogout');

            if (logoutBtn) {
                logoutBtn.addEventListener("click", async (e) => {
                    e.preventDefault();

                    try {
                        await axios.post('/api/logout');

                        // Optional: clear cookie XSRF
                        window.location.href = "/login";
                    } catch (error) {
                        console.error(error.response ? error.response.data : error);
                        alert("Gagal logout, coba lagi.");
                    }
                });
            }
        });
    </script>
</body>

</html>
