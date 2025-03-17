@extends('layouts.app')

@section('title', 'Dashboard 2 - Mesačný Prehľad')

@section('content')
    <div class="container-fluid px-4 py-5">
        <div class="d-flex flex-column align-items-center mb-4">
            <img src="/img/chart-icon.svg" alt="Dashboard icon" width="32" height="32">
            <h1 class="text-2xl font-bold text-white mt-2">Dashboard 2 - Mesačný Prehľad</h1>
        </div>

        <!-- Filters -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-4">
                <div class="card bg-dark border-0 shadow text-center">
                    <div class="card-body">
                        <label for="monthFilter" class="form-label text-gray-300 fw-semibold">Vyber mesiac a rok:</label>
                        <input type="text" id="monthFilter" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card bg-dark border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg font-semibold text-gray-300 mb-4">Vytvorené vs. Backlog</h2>
                        <div class="chart-container" style="position: relative; height:350px;">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card bg-dark border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg font-semibold text-gray-300 mb-4">Backlog Požiadavky</h2>
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let monthlyChart;
            let monthFilter = document.getElementById('monthFilter');

            // Flatpickr pre výber mesiaca a roka
            flatpickr("#monthFilter", {
                dateFormat: "Y-m",
                altInput: true,
                altFormat: "F Y",
                defaultDate: new Date(),
                onChange: function(selectedDates, dateStr) {
                    loadData(dateStr);
                }
            });

            function loadData(selectedMonth) {
                console.log("📊 Načítavam dáta pre:", selectedMonth);

                // API - Mesačný graf
                fetch(`/api/dashboard/monthly-summary?month=${selectedMonth}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log("📊 Dáta pre graf:", data);
                        let labels = data.map(d => d.created_date);
                        let createdCounts = data.map(d => d.created_count);
                        let backlogCounts = data.map(d => d.backlog_count);

                        if (monthlyChart) {
                            monthlyChart.data.labels = labels;
                            monthlyChart.data.datasets[0].data = createdCounts;
                            monthlyChart.data.datasets[1].data = backlogCounts;
                            monthlyChart.update();
                        } else {
                            let ctx = document.getElementById('monthlyChart').getContext('2d');
                            monthlyChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [
                                        {
                                            label: 'Vytvorené',
                                            data: createdCounts,
                                            backgroundColor: '#60a5fa'
                                        },
                                        {
                                            label: 'Backlog',
                                            data: backlogCounts,
                                            backgroundColor: '#f87171'
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        x: { ticks: { color: '#e2e2e2' } },
                                        y: { ticks: { color: '#e2e2e2' } }
                                    },
                                    plugins: {
                                        legend: { labels: { color: '#e2e2e2' } }
                                    }
                                }
                            });
                        }
                    });

                // API - Backlog tabuľka
                fetch(`/api/dashboard/backlog-table?month=${selectedMonth}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log("📋 Dáta pre tabuľku:", data);
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

            // Inicializácia tabuľky DataTables.js
            $('#backlogTable').DataTable({
                paging: true,
                searching: true,
                order: [[4, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Slovak.json'
                }
            });

            // Prvé načítanie dát
            loadData(new Date().toISOString().slice(0, 7));
        });
    </script>
@endsection
