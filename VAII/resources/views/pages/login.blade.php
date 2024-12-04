@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <link rel="stylesheet" href="{{ asset('/css/logIn.css') }}">
    <div class="content">

        <div class="login-container">
            <div class="login-form">


                <img src="{{ asset('images/account-security.png') }}" alt="Prihlásenie ikona" class="login-icon">


                <form action="login.php" method="POST">
                    <div class="input-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="login-button">Log in</button>
                </form>
            </div>
        </div>
    </div>
@endsection
