@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <link rel="stylesheet" href="{{ asset('/css/logIn.css') }}">
    <div class="content">
        <div class="login-container">
            <div class="login-form">
                <!-- Login Icon -->
                <img src="{{ asset('images/account-security.png') }}" alt="Prihlásenie ikona" class="login-icon">

                <!-- Login Form -->
                <form id="loginForm" method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="input-group">
                        <label for="email">E-mail:</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="@error('email') is-invalid @enderror">
                        @error('email')
                        <span class="error-tooltip">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="input-group">
                        <label for="password">Password:</label>
                        <input id="password" type="password" name="password" required
                               class="@error('password') is-invalid @enderror">
                        @error('password')
                        <span class="error-tooltip">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="input-group checkbox">
                        <input type="checkbox" id="remember_me" name="remember">
                        <label for="remember_me">Remember me</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="login-button">Log in</button>
                </form>

                <!-- Register Link -->
                <div class="register-link">
                    <p>Don't have an account? <a href="{{ route('register') }}" class="text-underline">Register here</a>.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
