<!DOCTYPE html>
<html lang="en">

<head>
    <title>@yield('title', 'dashboard')</title>

    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="https://preview.keenthemes.com/metronic8" />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <!-- Bootstrap CSS -->
    {{-- <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
   <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet"> --}}
    <!-- jQuery -->
    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-resizable.js') }}"></script>

    <script>
        // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
    <style>
        .image-input-placeholder {
            background-image: url('/assets/media/svg/avatars/blank.svg');
        }

        [data-bs-theme="dark"] .image-input-placeholder {
            background-image: url('/assets/media/svg/avatars/blank-dark.svg');
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('css')
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <div id="kt_app_header" class="app-header" data-kt-sticky="true"
                data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize"
                data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
                <div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
                    id="kt_app_header_container">
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
                        <div class="btn btn-icon btn-active-color-primary w-35px h-35px"
                            id="kt_app_sidebar_mobile_toggle">
                            <i class="ki-duotone ki-abstract-14 fs-2 fs-md-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="/" class="d-lg-none">
                            <img alt="Logo" src={{ asset('assets/media/logos/default-small.svg') }}
                                class="h-30px" />
                        </a>
                    </div>
                    @include('layouts.themes.header')
                </div>
            </div>
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                @include('layouts.themes.sidebar')

                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                                    <h1
                                        class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                                        @yield('title')</h1>
                                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                        <li class="breadcrumb-item text-muted">
                                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">
                                                <i class="fa fa-home"></i>
                                            </a>
                                        </li>
                                        @if (isset($breadcrumbs))
                                            @foreach ($breadcrumbs as $breadcrumb)
                                                <li class="breadcrumb-item">
                                                    <span class="bullet bg-gray-500 w-5px h-2px"></span>
                                                </li>
                                                <li class="breadcrumb-item text-muted">{{ $breadcrumb }}</li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <div class="d-flex align-items-center gap-2 gap-lg-3">
                                    @yield('actions')
                                </div>
                            </div>
                        </div>
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                @yield('main')
                                @stack('modal')
                            </div>
                        </div>
                    </div>

                    <div id="kt_app_footer" class="app-footer">
                        <div
                            class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
                            <div class="text-gray-900 order-2 order-md-1">
                                <span class="text-muted fw-semibold me-1">2024&copy;</span>
                                <a href={{ route('dashboard') }} class="text-gray-800 text-hover-primary">Anihost</a>
                            </div>
                            <!-- <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
                        <li class="menu-item">
                           <a href="https://keenthemes.com" target="_blank" class="menu-link px-2">About</a>
                        </li>
                        <li class="menu-item">
                           <a href="https://devs.keenthemes.com" target="_blank" class="menu-link px-2">Support</a>
                        </li>
                        <li class="menu-item">
                           <a href="https://1.envato.market/EA4JP" target="_blank"
                              class="menu-link px-2">Purchase</a>
                        </li>
                     </ul> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-duotone ki-arrow-up">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </div>

    <!--begin::Javascript-->
    <script>
        var hostUrl = "{{ asset('assets/') }}";
    </script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/helper.js?v=1.1.4') }}"></script>
    <script src="{{ asset('assets/js/custom/swal-custom.js?v=1.0.0') }}"></script>

    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/widgets.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>

    @stack('js')

</body>

</html>
