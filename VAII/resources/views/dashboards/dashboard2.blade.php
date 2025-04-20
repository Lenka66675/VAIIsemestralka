@extends('layouts.app')

@section('title', 'Dashboard 2 - Mesačný Prehľad')

@section('content')
    <style>
        /* Celkové pozadie stránky */
        body {
            background-image: url("{{ asset('images/backG.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        /* Karta s červeným orámovaním a priehľadným pozadím */
        .custom-card {
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid red;
            color: white;
        }

        .form-label {
            font-weight: bold;
            color: #ffffff;
        }

        /* Stránkovanie v DataTables - elegantný červený štýl */
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 20px;
            text-align: center;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            display: inline-block;
            padding: 6px 12px;
            margin: 0 4px;
            border: 2px solid red;
            border-radius: 5px;
            color: white !important;
            background-color: transparent;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: red !important;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: red !important;
            color: white !important;
            font-weight: bold;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.4;
            pointer-events: none;
        }




        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange,
        .flatpickr-day.selected:hover {
            background: red !important;
            color: white !important;
            border-radius: 50% !important;
        }



        /* Kaskádovanie tabuliek a kontajnerov */
        .table-responsive {
            margin-top: 20px;
        }
    </style>

    <div class="container-fluid px-4 py-5" id="dashboardWrapper">
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
                <div class="card custom-card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg font-semibold text-white mb-4">Backlog Požiadavky</h2>
                        <div class="table-responsive">
                            <table id="backlogTable" class="table table-dark table-striped table-bordered text-white">
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

        <div class="text-end mb-4">
            <button id="saveDashboardBtn" class="btn btn-danger">
                📸 Uložiť ako obrázok
            </button>
            <button id="saveToDatabaseBtn" class="btn btn-danger">
                💾 Uložiť do databázy
            </button>
        </div>

    <!-- Skripty a knižnice -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Používame základný DataTables skript bez Bootstrap integrácie -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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

            // Prvé načítanie
            const currentMonth = new Date().toISOString().slice(0, 7);
            loadStats(currentMonth);
            loadBacklogTable(currentMonth);
        });
    </script>

    <!-- SAVE TO IMAGE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        document.getElementById('saveDashboardBtn').addEventListener('click', function () {
            const dashboard = document.getElementById('dashboardWrapper') || document.getElementById('mapDashboardCapture');
            const bodyBgImage = window.getComputedStyle(document.body).backgroundImage;
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = dashboard.offsetWidth * 2;
            finalCanvas.height = dashboard.offsetHeight * 2;
            const ctx = finalCanvas.getContext('2d');
            // Zastavenie mapových animácií (ak sú)
            if (window.map && window.map.stop) window.map.stop();

            setTimeout(() => {
                html2canvas(dashboard, { scale: 2, useCORS: true, backgroundColor: null }).then(dashCanvas => {
                    const bgImg = new Image();
                    bgImg.crossOrigin = "Anonymous";
                    bgImg.onload = () => {
                        ctx.drawImage(bgImg, 0, 0, finalCanvas.width, finalCanvas.height);
                        ctx.drawImage(dashCanvas, 0, 0);
                        const image = finalCanvas.toDataURL("image/png");
                        const link = document.createElement('a');
                        link.href = image;
                        link.download = `dashboard-${new Date().toISOString().slice(0, 10)}.png`;
                        link.click();
                    };
                    const bgUrl = bodyBgImage.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
                    bgImg.src = bgUrl;
                });
            }, 500);
        });
    </script>

    <!-- SAVE TO DB -->
    <script>
        document.getElementById('saveToDatabaseBtn').addEventListener('click', function () {
            const dashboard = document.getElementById('dashboardWrapper') || document.getElementById('mapDashboardCapture');
            const bodyBgImage = window.getComputedStyle(document.body).backgroundImage;
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = dashboard.offsetWidth * 2;
            finalCanvas.height = dashboard.offsetHeight * 2;
            const ctx = finalCanvas.getContext('2d');

            setTimeout(() => {
                html2canvas(dashboard, { scale: 2, useCORS: true, backgroundColor: null }).then(dashCanvas => {
                    const bgImg = new Image();
                    bgImg.crossOrigin = "Anonymous";
                    bgImg.onload = () => {
                        ctx.drawImage(bgImg, 0, 0, finalCanvas.width, finalCanvas.height);
                        ctx.drawImage(dashCanvas, 0, 0);
                        const image = finalCanvas.toDataURL("image/png");
                        fetch('/screenshots', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ image: image })
                        })
                            .then(res => res.json())
                            .then(data => {
                                alert(data.message ?? '✅ Screenshot uložený do databázy!');
                            })
                            .catch(err => {
                                console.error(err);
                                alert('❌ Chyba pri ukladaní screenshotu.');
                            });
                    };
                    const bgUrl = bodyBgImage.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
                    bgImg.src = bgUrl;
                });
            }, 500);
        });
    </script>
@endsection
