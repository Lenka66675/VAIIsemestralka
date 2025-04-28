@extends('layouts.app')

@section('title', 'Dashboard 4 - Regióny (Live)')

@section('content')
    <style>
        body {
            background-image: url("{{ asset('images/backG.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .custom-card {
            background-color: rgba(40, 40, 40, 0.6);
            border: 2px solid rgba(220, 53, 69, 0.7);
            color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .chart-container {
            background-color: rgba(40, 40, 40, 0.6);
            border: 2px solid rgba(220, 53, 69, 0.7);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .dashboard-title {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            letter-spacing: 1px;
        }
    </style>

    <div class="container-fluid px-4 py-5" id="dashboardWrapper">
        <div class="d-flex flex-column align-items-center mb-4">
            <img src="{{ asset('images/dashboard icon.png') }}" alt="Dashboard icon" width="64" height="64" class="mb-2">
            <h1 class="text-2xl font-bold text-white mt-2 dashboard-title">SLA Compliance</h1>
        </div>

        <!-- KACHLIČKY -->
        <div class="row text-white text-center mb-5" id="topCountryCards">
            <div class="col-md-3"><div class="card custom-card shadow"><div class="card-body"><h5 class="card-title">Lowest Backlog</h5><p id="bestBacklog" class="display-6">-</p></div></div></div>
            <div class="col-md-3"><div class="card custom-card shadow"><div class="card-body"><h5 class="card-title">Fastest Processing</h5><p id="bestAvgDays" class="display-6">-</p></div></div></div>
            <div class="col-md-3"><div class="card custom-card shadow"><div class="card-body"><h5 class="card-title">Lowest Backlog in Days</h5><p id="bestBacklogDays" class="display-6">-</p></div></div></div>
            <div class="col-md-3"><div class="card custom-card shadow"><div class="card-body"><h5 class="card-title">Highest SLA (%)</h5><p id="bestOnTime" class="display-6">-</p></div></div></div>
        </div>

        <!-- GRAF -->
        <div class="chart-container text-white">
            <h4 class="text-white mb-4 ps-2">Comparison of Metrics by Region (Latest Export)</h4>
            <div style="height: 300px;">
                <canvas id="regionChart"></canvas>
            </div>
        </div>

        <!-- BUTTONY -->
        <div class="text-end mb-4">
            <button id="saveDashboardBtn" class="btn btn-danger">
                Save
            </button>
            <button id="saveToDatabaseBtn" class="btn btn-danger">
                Save to library
            </button>
        </div>

    <!-- 📦 Skripty -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('regionChart').getContext('2d');
            let regionChart;

            const colors = {
                backlog: 'rgba(154,21,50,0.75)',
                backlogDays: 'rgb(213,113,113)',
                avgDays: 'rgb(253,183,183)',
                onTime: 'rgb(163,23,23)'
            };

            function loadRegionSnapshot() {
                fetch(`/api/dashboard/region-snapshot-latest`)
                    .then(res => res.json())
                    .then(data => {
                        const labels = data.map(item => item.region);
                        const chartData = {
                            labels,
                            datasets: [
                                { label: 'Backlog', data: data.map(i => i.backlog), backgroundColor: colors.backlog },
                                { label: 'Backlog in days', data: data.map(i => i.backlog_in_days), backgroundColor: colors.backlogDays },
                                { label: 'Avg. completion in days', data: data.map(i => i.avg_processing_days), backgroundColor: colors.avgDays },
                                { label: '% CRs completed in SLA', data: data.map(i => i.on_time_percentage), backgroundColor: colors.onTime }
                            ]
                        };

                        const chartOptions = {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { color: 'white', font: { weight: 'bold' } } },
                                datalabels: {
                                    color: 'white',
                                    font: { weight: 'bold' },
                                    formatter: (value, ctx) => {
                                        if (value === 0) return '';
                                        return ctx.dataset.label.includes('%') ? value.toFixed(1) + '%' : value.toFixed(1);
                                    }
                                }
                            },
                            scales: {
                                x: { ticks: { color: 'white', font: { weight: 'bold' } } },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: 'white' },
                                    grid: { color: 'rgba(255,255,255,0.1)' }
                                }
                            }
                        };

                        if (regionChart) {
                            regionChart.data = chartData;
                            regionChart.options = chartOptions;
                            regionChart.update();
                        } else {
                            Chart.register(ChartDataLabels);
                            regionChart = new Chart(ctx, { type: 'bar', data: chartData, options: chartOptions });
                        }
                    });
            }

            function loadCountrySnapshot() {
                fetch(`/api/dashboard/best-countries-latest`)
                    .then(res => res.json())
                    .then(data => {
                        const validBacklog = data.filter(r => r.backlog !== null);
                        const validBacklogDays = data.filter(r => r.backlog_in_days !== null);
                        const validAvg = data.filter(r => r.avg_processing_days > 0);
                        const validSLA = data.filter(r => r.on_time_percentage > 0);

                        const bestBacklog = validBacklog.reduce((min, r) => r.backlog < min.backlog ? r : min, validBacklog[0]);
                        const bestBacklogDays = validBacklogDays.reduce((min, r) => r.backlog_in_days < min.backlog_in_days ? r : min, validBacklogDays[0]);
                        const bestAvgDays = validAvg.reduce((min, r) => r.avg_processing_days < min.avg_processing_days ? r : min, validAvg[0]);
                        const bestOnTime = validSLA.reduce((max, r) => r.on_time_percentage > max.on_time_percentage ? r : max, validSLA[0]);

                        document.getElementById('bestBacklog').textContent = `${bestBacklog.country} (${bestBacklog.backlog})`;
                        document.getElementById('bestBacklogDays').textContent = `${bestBacklogDays.country} (${bestBacklogDays.backlog_in_days})`;
                        document.getElementById('bestAvgDays').textContent = `${bestAvgDays.country} (${bestAvgDays.avg_processing_days} )`;
                        document.getElementById('bestOnTime').textContent = `${bestOnTime.country} (${bestOnTime.on_time_percentage}%)`;
                    });
            }

            loadRegionSnapshot();
            loadCountrySnapshot();
            setInterval(loadRegionSnapshot, 300000);
        });

        // 📸 ULOŽENIE + POZADIE
        function getBackgroundImageUrl() {
            const bodyBgImage = window.getComputedStyle(document.body).backgroundImage;
            return bodyBgImage.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
        }

        function captureDashboard(callback) {
            const element = document.querySelector('main');
            const bgUrl = getBackgroundImageUrl();
            const canvas = document.createElement('canvas');
            canvas.width = element.offsetWidth * 2;
            canvas.height = element.offsetHeight * 2;
            const ctx = canvas.getContext('2d');

            setTimeout(() => {
                html2canvas(element, { scale: 2, useCORS: true, backgroundColor: null }).then(dashCanvas => {
                    const bgImg = new Image();
                    bgImg.crossOrigin = 'Anonymous';
                    bgImg.onload = () => {
                        ctx.drawImage(bgImg, 0, 0, canvas.width, canvas.height);
                        ctx.drawImage(dashCanvas, 0, 0);
                        callback(canvas.toDataURL('image/png'));
                    };
                    bgImg.src = bgUrl;
                });
            }, 500);
        }

        document.getElementById('saveDashboardBtn').addEventListener('click', () => {
            captureDashboard(imageData => {
                const link = document.createElement('a');
                link.href = imageData;
                link.download = 'dashboard4-' + new Date().toISOString().slice(0, 10) + '.png';
                link.click();
            });
        });

        document.getElementById('saveToDatabaseBtn').addEventListener('click', () => {
            captureDashboard(imageData => {
                fetch('/screenshots', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ image: imageData })
                })
                    .then(res => res.json())
                    .then(data => alert(data.message ?? '✅ Screenshot uložený do databázy!'))
                    .catch(() => alert('❌ Chyba pri ukladaní screenshotu.'));
            });
        });
    </script>
@endsection
