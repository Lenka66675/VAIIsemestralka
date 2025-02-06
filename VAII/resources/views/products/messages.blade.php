@extends('layouts.app')

@section('title', 'Messages')

@section('content')
    <link rel="stylesheet" href="{{ asset('/css/messages.css') }}">

    <div class="container">
        <h3 class="text-center">📩 Messages</h3>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($messages as $message)
                <tr id="messageRow-{{ $message->id }}">
                    <td>{{ $message->name }}</td>
                    <td>{{ $message->email }}</td>
                    <td>{{ $message->message }}</td>
                    <td>
                        @if ($message->replied)
                            <span class="badge badge-success">✅ Zodpovedané</span>
                        @else
                            <form action="{{ route('messages.reply', $message->id) }}" method="post" class="reply-form">
                                @csrf
                                <textarea name="reply" class="form-control" required placeholder="Write a reply..."></textarea>
                                <button type="submit" class="btn btn-success mt-2">Send Reply</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- ✅ AJAX na odoslanie odpovede bez reloadu -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".reply-form").forEach(form => {
                form.addEventListener("submit", function (e) {
                    e.preventDefault(); // Zastaví predvolený submit formulára

                    let formData = new FormData(this);
                    let url = this.getAttribute("action");
                    let messageRow = this.closest("tr");

                    fetch(url, {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                messageRow.querySelector(".reply-form").remove(); // Odstráni formulár
                                let badge = document.createElement("span");
                                badge.className = "badge badge-success";
                                badge.textContent = "✅ Zodpovedané";
                                messageRow.querySelector("td:last-child").appendChild(badge); // Pridá "Zodpovedané"
                            }
                        })
                        .catch(error => console.error("Error:", error));
                });
            });
        });
    </script>
@endsection
