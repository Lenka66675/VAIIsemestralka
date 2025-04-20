@extends('layouts.app')

@section('title', 'Importované súbory')

@section('content')
    <div class="container py-5">
        <h1 class="text-white mb-4">📦 Importované súbory</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-dark table-bordered table-striped">
            <thead>
            <tr>
                <th>Názov súboru</th>
                <th>Počet záznamov</th>
                <th>Typ zdroja</th>
                <th>Dátum nahratia</th>
                <th>Akcia</th>
            </tr>
            </thead>
            <tbody>
            @forelse($imports as $import)
                <tr>
                    <td>{{ $import->original_filename }}</td>
                    <td>{{ $import->uploaded_data_count }}</td>
                    <td>{{ $import->source_type }}</td>
                    <td>{{ $import->uploaded_at->format('d.m.Y H:i') }}</td>
                    <td>
                        <form action="{{ route('imports.destroy', $import->id) }}" method="POST" onsubmit="return confirm('Naozaj chceš zmazať tento import a jeho dáta?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">🗑 Zmazať</button>

                            <a href="{{ route('imports.show', $import->id) }}" class="btn btn-sm btn-outline-light">
                                👁 Zobraziť
                            </a>


                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Žiadne importy</td></tr>
            @endforelse
            </tbody>
        </table>

        {{ $imports->links() }}
    </div>
@endsection
