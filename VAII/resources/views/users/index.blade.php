@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="text-white">User management</h2>

        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
        @endif

        <table class="table table-dark table-bordered mt-4">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Approved</th>
                <th>Active</th>
                <th>Actions</th>
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
                        @if(!$user->is_approved)
                            <form action="{{ route('users.approve', $user) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-success" onclick="return confirm('Naozaj chceš schváliť tohto používateľa?')">Approve</button>
                            </form>
                        @else
                            <span class="badge bg-success">✅ Approved</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('users.toggleActive', $user) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                    onclick="return confirm('Chceš zmeniť aktívny stav tohto používateľa?')">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        @if(auth()->id() !== $user->id && $user->email !== 'admin@example.com')
                            <form action="{{ route('users.toggleRole', $user) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-light"
                                        onclick="return confirm('Chceš zmeniť rolu používateľa?')">
                                    Change to {{ $user->isAdmin() ? 'user' : 'admin' }}
                                </button>
                            </form>
                        @elseif(auth()->id() === $user->id)
                            <span class="text-muted">Ty</span>
                        @else
                            <span class="text-muted">Chránený účet</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
