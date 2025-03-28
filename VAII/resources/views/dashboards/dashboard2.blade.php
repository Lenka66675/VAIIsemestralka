@extends('layouts.app')

@section('title', 'Dashboard 2 - Mesačný Prehľad')

@section('content')
    <style>
        .custom-card {
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid red;
            color: white;
        }

        .form-label {
            font-weight: bold;
            color: #ffffff;
        }

        body {
            background-image: url("{{ asset('images/backG.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>

    <div class="container-fluid px-4 py-5">
        <div class="d-flex flex-column align-items-center mb-4">
            <img src="{{ asset('images/dashboard icon.png') }}" alt="Dashboard icon" width="64" height="64">
            <h1 class="text-2xl font-bold text-white mt-2">Dashboard 2 - Mesačný Prehľad</h1>
        </div>

        <!-- Filter -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-4">
                <div class="card bg-dark border-0 shadow text-center">
                    <div class="card-body">
                        <label for="monthFilter" class="form-label text-white fw-semibold">Vyber mesiac a rok:</label>
                        <input type="text" id="monthFilter" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>
            </div>
        </div>

        <!-- Kachličky -->
        <div class="row text-white text-center mb-4" id="statCards">
            <div class="col-md-3">
                <div class="card custom-card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Backlog</h5>
                        <p id="statBacklog" class="display-6">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Backlog v dňoch</h5>
                        <p id="statBacklogDays" class="display-6">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Priemerný čas spracovania</h5>
                        <p id="statAvgDays" class="display-6">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card shadow">
                    <div class="card-body">
                        <h5 class="card-title">% dokončených do 4 dní</h5>
                        <p id="statOnTime" class="display-6">0%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabuľka -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card bg-dark border-0 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg font-semibold text-white mb-4">Backlog Požiadavky</h2>
                        <table id="backlogTable" class="table table-dark table-striped w-100">
                            <thead>
                            <tr>
                                <th>Request</th>
                                <th>Vytvorené</th>
                                <th>Status</th>
                                <th>Krajina</th>
                                <th>Backlog (dni)</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Skripty -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let monthFilter = document.getElementById('monthFilter');

            flatpickr("#monthFilter", {
                dateFormat: "Y-m",
                altInput: true,
                altFormat: "F Y",
                defaultDate: new Date(),
                onChange: function(selectedDates, dateStr) {
                    loadStats(dateStr);
                    loadBacklogTable(dateStr);
                }
            });

            function loadStats(month) {
                fetch(`/api/dashboard/snapshot?month=${month}`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('statBacklog').textContent = data.backlog ?? 0;
                        document.getElementById('statBacklogDays').textContent = data.total_backlog_days ?? 0;
                        document.getElementById('statAvgDays').textContent = data.avg_processing_days ?? 0;
                        document.getElementById('statOnTime').textContent = (data.on_time_percentage ?? 0) + '%';
                    });
            }

            function loadBacklogTable(month) {
                fetch(`/api/dashboard/backlog-table?month=${month}`)
                    .then(res => res.json())
                    .then(data => {
                        let table = $('#backlogTable').DataTable();
                        table.clear();
                        data.forEach(row => {
                            table.row.add([
                                row.request,
                                row.created,
                                row.status,
                                row.country,
                                row.backlog_days
                            ]);
                        });
                        table.draw();
                    });
            }

            $('#backlogTable').DataTable({
                paging: true,
                searching: true,
                order: [[4, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Slovak.json'
                }
            });

            // prvotné načítanie
            const currentMonth = new Date().toISOString().slice(0, 7);
            loadStats(currentMonth);
            loadBacklogTable(currentMonth);
        });
    </script>
@endsection
