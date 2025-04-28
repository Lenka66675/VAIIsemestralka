@extends('layouts.app')

@section('title', 'Moja knižnica screenshotov')

@section('content')
    <style>
        body {
            background-image: url("{{ asset('images/backG.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .screenshot-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
        }
        .screenshot-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }
        .screenshot-item:hover {
            transform: scale(1.05);
        }
        .screenshot-item img {
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
        }
        .screenshot-info {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            text-align: center;
            font-size: 14px;
            padding: 5px 0;
        }
        .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #7e7b7b;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .delete-btn:hover {
            background: darkred;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }
        .modal img {
            max-width: 80%;
            max-height: 80%;
            border-radius: 10px;
        }
        .modal-close {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 30px;
            color: white;
            cursor: pointer;
        }
    </style>

    <div class="container">
        <h1 class="text-center text-white mt-4">My Library</h1>

        @if($screenshots->isEmpty())
            <p class="text-center text-white mt-4">❌ Any saved screenshots.</p>
        @else
            <div class="screenshot-container">
                @foreach($screenshots as $screenshot)
                    <div class="screenshot-item" id="screenshot-{{ $screenshot->id }}">
                        <img src="{{ asset('storage/' . $screenshot->image_path) }}" alt="Screenshot" onclick="openModal(this)">
                        <div class="screenshot-info">
                            📅 {{ \Carbon\Carbon::parse($screenshot->created_at)->format('d. m. Y H:i') }}
                        </div>
                        <button class="delete-btn" onclick="deleteScreenshot({{ $screenshot->id }})">❌</button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- MODAL PRE ZOBRAZENIE OBRÁZKA -->
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
                        alert('✅ Screenshot bol vymazaný!');
                        document.getElementById(`screenshot-${screenshotId}`).remove(); // Odstránime zo stránky
                    } else {
                        alert('❌ Chyba pri mazaní screenshotu.');
                    }
                })
                .catch(error => {
                    console.error('❌ Chyba:', error);
                    alert('❌ Screenshot nebolo možné zmazať.');
                });
        }
    </script>

@endsection
