@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="text-white">🛠 Správa používateľov</h2>

        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
        @endif

        <table class="table table-dark table-bordered mt-4">
            <thead>
            <tr>
                <th>Meno</th>
                <th>Email</th>
                <th>Rola</th>
                <th>Akcia</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->isAdmin() ? 'bg-danger' : 'bg-secondary' }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>
                        @if(auth()->id() !== $user->id)
                            <form action="{{ route('users.toggleRole', $user) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-danger">
                                    Zmeniť na {{ $user->isAdmin() ? 'user' : 'admin' }}
                                </button>
                            </form>
                        @else
                            <span class="text-muted">Ty</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
