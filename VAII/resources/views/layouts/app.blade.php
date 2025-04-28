<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'VendorMasterData')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('/css/general.css') }}">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>

</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-link" href="{{ url('/dashboard1') }}">Status Overview</a>
                <a class="nav-link" href="{{ url('/dashboard2') }}">Request Timeline</a>
                <a class="nav-link" href="{{ url('/dashboard3') }}">Country Comparison</a>
                <a class="nav-link" href="{{ url('/dashboard4') }}">SLA Compliance</a>
                @auth
                    <a class="nav-link" href="{{ url('/screenshots') }}">Library</a>

                @if(auth()->user()?->isAdmin())
                        <a class="nav-link" href="{{ url('/upload') }}">Upload</a>
                        <a class="nav-link" href="{{ url('/imports') }}">Imports</a>
                    @endif
                @endauth


            </div>
        </div>
        @auth
            @if(auth()->user()?->isAdmin())
                <a class="navbar-brand" href="{{ url('/users') }}">
                    <img src="{{ asset('images/people.png') }}" alt="Users" class="users-img">
                </a>
            @endif
        @endauth

        <!-- Authentication Links -->
        <div class="login-container d-flex flex-wrap align-items-center gap-2 ms-2">
            @auth
                <!-- Ak je používateľ prihlásený -->
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-primary">Log Out</button>
                </form>
            @else
                <!-- Ak používateľ nie je prihlásený -->
                <a href="{{ route('login') }}" class="btn btn-primary">Log In</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
            @endauth
        </div>


    </div>
</nav>

<main class="flex-grow-1">
    @yield('content') <!-- Dynamický obsah -->
</main>

<footer class="bg-light text-center text-lg-start">
    <div class="container p-4">
        <div class="row">
            <div class="col-lg-6 col-md-12 mb-4">
                <h5 class="text-uppercase">About us</h5>
                <p>We are a team of professionals dedicated to processing and managing supplier data.</p>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="text-uppercase">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-dark">Privacy Policy</a></li>
                    <li><a href="#" class="text-dark">Terms of Use</a></li>
                    <li><a href="{{ url('contact') }}" class="text-dark">Contact</a></li>
                    @auth
                        <li><a href="{{ url('messages') }}" class="text-dark">Messages</a></li>
                    @endauth
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="text-uppercase">Social Media</h5>
                <ul class="list-unstyled">
                    <li><a href="https://facebook.com" class="text-dark">Facebook</a></li>
                    <li><a href="https://twitter.com" class="text-dark">Twitter</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="text-center p-3 bg-dark text-white">
        &copy; {{ date('Y') }} My Website. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
