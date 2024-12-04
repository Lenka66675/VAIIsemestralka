<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'VendorMasterData')</title>


    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('/css/general.css') }}">


</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('images/danfoss-logo.png') }}" alt="Logo" class="logo-img">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-link" href="#">External Vendors</a>
                <a class="nav-link" href="#">International Comparison</a>
                <a class="nav-link" href="#">Monthly Status</a>
                <a class="nav-link" href="#">Approval Timelines</a>
                <a class="nav-link" href="#">Process Improvement</a>
                <a class="nav-link" href="#">Employee Performance</a>
            </div>
        </div>
        <div class="login-container">
            <a href="{{ route('login') }}" id="loginButton" class="btn btn-primary">Log In</a>
        </div>
    </div>
</nav>
<main class="flex-grow-1">
    @yield('content')

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
        &copy; 2024 My Website. All rights reserved.
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
