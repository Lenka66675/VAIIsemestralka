@extends('layouts.app')

@section('title', 'Dashboard 2 - Mesačný Prehľad')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/dashboard2.css') }}">

    <div class="container-fluid px-4 py-5" id="dashboardWrapper">
        <div class="d-flex flex-column align-items-center mb-4">
            <img src="{{ asset('images/dashboard icon.png') }}" alt="Dashboard icon" width="64" height="64">
            <h1 class="text-2xl font-bold text-white mt-2">Request Timeline</h1>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-md-4">
                <div class="card bg-dark border-0 shadow text-center">
                    <div class="card-body">
                        <label for="monthFilter" class="form-label text-white fw-semibold">Select date:</label>
                        <input type="text" id="monthFilter" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>
            </div>
        </div>

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
                        <h5 class="card-title">Backlog in days</h5>
                        <p id="statBacklogDays" class="display-6">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Avg. completion in days</h5>
                        <p id="statAvgDays" class="display-6">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card custom-card shadow">
                    <div class="card-body">
                        <h5 class="card-title">% CRs completed in SLA</h5>
                        <p id="statOnTime" class="display-6">0%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card custom-card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg font-semibold text-white mb-4">Requests overview</h2>
                        <div class="table-responsive">
                            <table id="backlogTable" class="table table-dark table-striped table-bordered text-white">
                                <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Country</th>
                                    <th>Backlog</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@auth
        <div class="text-end mb-4">
            <button id="saveDashboardBtn" class="btn btn-danger">
                Save
            </button>
            <button id="saveToDatabaseBtn" class="btn btn-danger">
                Save to library
            </button>
        </div>
@endauth
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

            const currentMonth = new Date().toISOString().slice(0, 7);
            loadStats(currentMonth);
            loadBacklogTable(currentMonth);
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        document.getElementById('saveDashboardBtn').addEventListener('click', function () {
            const dashboard = document.getElementById('dashboardWrapper') || document.getElementById('mapDashboardCapture');
            const bodyBgImage = window.getComputedStyle(document.body).backgroundImage;
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = dashboard.offsetWidth * 2;
            finalCanvas.height = dashboard.offsetHeight * 2;
            const ctx = finalCanvas.getContext('2d');
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
                                alert('Chyba pri ukladaní screenshotu.');
                            });
                    };
                    const bgUrl = bodyBgImage.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
                    bgImg.src = bgUrl;
                });
            }, 500);
        });
    </script>
@endsection
