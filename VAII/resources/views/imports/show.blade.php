@extends('layouts.app')

@section('title', 'Detail importu')

@section('content')
    <style>

        .custom-card {
            background-color: rgba(255, 255, 255, 0.05); /* Jemná priehľadnosť */
            border: 2px solid red;
            color: white;
        }
        .pagination .page-link {
            color: red;
            border: 1px solid red;
            background-color: transparent;
            margin: 0 2px;
        }

        .pagination .page-item.active .page-link {
            background-color: red;
            color: white;
            border-color: red;
        }

        .pagination .page-link:hover {
            background-color: red;
            color: white;
            border-color: red;
        }
    </style>

    <div class="container py-4 text-white">
        <h2 class="mb-4">Detail of import – {{ $import->original_filename }}</h2>

        <p><strong>Type:</strong> {{ $import->source_type }}</p>
        <p><strong>Number of rows:</strong> {{ $import->uploadedData()->count() }}</p>
        <p><strong>Updated:</strong> {{ \Carbon\Carbon::parse($import->uploaded_at)->format('d.m.Y H:i') }}</p>

        <a href="{{ route('imports.download', $import->id) }}" class="btn btn-outline-light my-3">
            ⬇️ Save Excel
        </a>

        <hr>

        <h4 class="mt-4">Imported data:</h4>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card custom-card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg font-semibold text-white mb-4">Importované riadky</h2>
                        <div class="table-responsive">
                            <table class="table table-dark table-striped table-bordered text-white">
                                <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Created</th>
                                    <th>Finalized</th>
                                    <th>Vendor</th>
                                    <th>Country</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($uploadedData as $row)
                                    <tr>
                                        <td>{{ $row->request }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td>{{ $row->description }}</td>
                                        <td>{{ $row->type }}</td>
                                        <td>{{ $row->created }}</td>
                                        <td>{{ $row->finalized }}</td>
                                        <td>{{ $row->vendor }}</td>
                                        <td>{{ $row->country }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Žiadne dáta</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Stránkovanie -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $uploadedData->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('imports.index') }}" class="btn btn-secondary mt-3">← Späť</a>
    </div>
@endsection
