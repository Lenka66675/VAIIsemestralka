@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <link rel="stylesheet" href="{{ asset('/css/logIn.css') }}">
    <div class="content">
        <div class="login-container">
            <div class="login-form">

                <!-- Register Form -->
                <form id="registerForm" method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="input-group">
                        <label for="name">Name:</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                               class="@error('name') is-invalid @enderror">
                        @error('name')
                        <span class="error-tooltip">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div class="input-group">
                        <label for="email">Email:</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
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

                    <!-- Confirm Password -->
                    <div class="input-group">
                        <label for="password_confirmation">Confirm Password:</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="@error('password_confirmation') is-invalid @enderror">
                        @error('password_confirmation')
                        <span class="error-tooltip">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="login-button">Register</button>
                </form>

                <!-- Login Link -->
                <div class="register-link">
                    <p>Already have an account? <a href="{{ route('login') }}" class="text-underline">Log in here</a>.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
