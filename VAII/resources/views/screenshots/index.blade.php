@extends('layouts.app')

@section('title', 'Moja knižnica screenshotov')

@section('content')

    <link rel="stylesheet" href="{{ asset('css/screenshots.css') }}">

    <div class="container">
        <h1 class="text-center text-white mt-4">My Library</h1>

        @if($screenshots->isEmpty())
            <p class="text-center text-white mt-4">Any saved screenshots.</p>
        @else
            <div class="screenshot-container">
                @foreach($screenshots as $screenshot)
                    <div class="screenshot-item" id="screenshot-{{ $screenshot->id }}">
                        <img src="{{ asset('storage/' . $screenshot->image_path) }}" alt="Screenshot" onclick="openModal(this)">
                        <div class="screenshot-info">
                            {{ \Carbon\Carbon::parse($screenshot->created_at)->format('d. m. Y H:i') }}
                        </div>
                        <button class="delete-btn" onclick="deleteScreenshot({{ $screenshot->id }})">❌</button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div id="imageModal" class="modal" onclick="closeModal()">
        <span class="modal-close">&times;</span>
        <img id="modalImage">
    </div>

    <script>
        function openModal(imgElement) {
            document.getElementById('modalImage').src = imgElement.src;
            document.getElementById('imageModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function deleteScreenshot(screenshotId) {
            if (!confirm('Naozaj chceš vymazať tento screenshot?')) return;

            fetch(`/screenshots/${screenshotId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert('Screenshot bol vymazaný!');
                        document.getElementById(`screenshot-${screenshotId}`).remove();
                    } else {
                        alert('Chyba pri mazaní screenshotu.');
                    }
                })
                .catch(error => {
                    console.error('Chyba:', error);
                    alert('Screenshot nebolo možné zmazať.');
                });
        }
    </script>

@endsection
