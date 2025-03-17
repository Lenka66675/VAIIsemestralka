@extends('layouts.app')

@section('title', 'Dashboard 1')

@section('content')
    <style>
        body {
            background-image: url("{{ asset('images/backG.jpg') }}");

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>

    <div class="container-fluid px-4 py-5">
        <div class="text-center mb-4">
            <img src="/img/chart-icon.svg" alt="Dashboard icon" class="me-2" width="32" height="32">
            <h1 class="text-2xl font-bold text-white m-0">Dashboard 1</h1>
        </div>

        <!-- Filters -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-3">
                <div class="card bg-dark bg-opacity-50 border-0 shadow">
                    <div class="card-body">
                        <label for="systemFilter" class="form-label text-gray-300 fw-semibold mb-2">Vyber systém:</label>
                        <select id="systemFilter" class="form-select bg-dark text-white border-secondary">
                            <option value="">Všetky</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark bg-opacity-50 border-0 shadow">
                    <div class="card-body">
                        <label for="countryFilter" class="form-label text-gray-300 fw-semibold mb-2">Vyber krajinu:</label>
                        <select id="countryFilter" class="form-select bg-dark text-white border-secondary">
                            <option value="">Všetky</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>




        <!-- Charts Section -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card bg-dark bg-opacity-50 border-0 shadow h-100">
                    <div class="card-body d-flex">
                        <div class="chart-container" style="width: 60%; position: relative; height:350px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div id="statusLegend" class="text-white ms-4" style="width: 40%; font-size: 14px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card bg-dark bg-opacity-50 border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="card-title text-lg font-semibold text-gray-300 mb-4">Vytvorené vs. Finalizované</h2>
                        <div class="chart-container" style="position: relative; height:350px;">
                            <canvas id="createdFinalizedChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mb-4">
        <button id="saveDashboardBtn" class="btn btn-primary">
            📸 Uložiť ako obrázok
        </button>
    </div>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let statusChart, createdFinalizedChart;
        const chartColors = ['#f87171', '#60a5fa', '#34d399', '#fbbf24', '#a855f7', '#fb923c'];

        async function loadFilters() {
            let filterResponse = await fetch('/api/dashboard/filters');
            let filterData = await filterResponse.json();

            let systemFilter = document.getElementById('systemFilter');
            let countryFilter = document.getElementById('countryFilter');

            filterData.systems.forEach(system => {
                let option = document.createElement('option');
                option.value = system;
                option.textContent = system;
                systemFilter.appendChild(option);
            });

            filterData.countries.forEach(country => {
                let option = document.createElement('option');
                option.value = country;
                option.textContent = country;
                countryFilter.appendChild(option);
            });

            systemFilter.addEventListener('change', loadCharts);
            countryFilter.addEventListener('change', loadCharts);
        }

        async function loadCharts() {
            let selectedSystem = document.getElementById('systemFilter').value;
            let selectedCountry = document.getElementById('countryFilter').value;

            let queryParams = new URLSearchParams();
            if (selectedSystem) queryParams.append('system', selectedSystem);
            if (selectedCountry) queryParams.append('country', selectedCountry);

            // Status pie chart
            let statusResponse = await fetch(`/api/dashboard/summary?${queryParams}`);
            let statusData = await statusResponse.json();

            if (statusChart) statusChart.destroy();

            // Create a more diverse color palette with better contrast
            const enhancedColors = [
                '#4C78DB', // blue
                '#F87171', // red
                '#34D399', // green
                '#FBBF24', // yellow
                '#A855F7', // purple
                '#FB923C', // orange
                '#E879F9', // pink
                '#38BDF8', // light blue
                '#6EE7B7', // light green
                '#FCD34D', // light yellow
                '#D8B4FE', // light purple
                '#FDBA74', // light orange
                '#2563EB', // dark blue
                '#DC2626', // dark red
                '#059669', // dark green
                '#CA8A04'  // dark yellow
            ];

            // Prepare data with hidden states saved
            const statusVisibility = {};

            // Initialize all statuses as visible
            statusData.forEach(item => {
                statusVisibility[item.status] = true;
            });

            // Filter only visible data for the chart
            const getVisibleData = () => {
                return statusData.filter(item => statusVisibility[item.status]);
            };

            const updateChart = () => {
                const visibleData = getVisibleData();

                statusChart.data.labels = visibleData.map(d => d.status);
                statusChart.data.datasets[0].data = visibleData.map(d => d.total);
                statusChart.data.datasets[0].backgroundColor = visibleData.map((_, i) => enhancedColors[i % enhancedColors.length]);
                statusChart.update();
            };

            // Create initial chart with all data
            statusChart = new Chart(document.getElementById('statusChart'), {
                type: 'pie',
                data: {
                    labels: statusData.map(d => d.status),
                    datasets: [{
                        data: statusData.map(d => d.total),
                        backgroundColor: statusData.map((_, i) => enhancedColors[i % enhancedColors.length]),
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Create interactive legend with toggle functionality
            const legendContainer = document.getElementById('statusLegend');
            legendContainer.innerHTML = '';
            legendContainer.style.maxHeight = '350px';
            legendContainer.style.overflowY = 'auto';
            legendContainer.style.padding = '0 10px';

            statusData.forEach((item, index) => {
                const colorIndex = index % enhancedColors.length;
                const legendItem = document.createElement('div');
                legendItem.style.display = 'flex';
                legendItem.style.alignItems = 'center';
                legendItem.style.margin = '8px 0';
                legendItem.style.cursor = 'pointer';
                legendItem.style.padding = '5px';
                legendItem.style.borderRadius = '4px';
                legendItem.style.transition = 'background-color 0.2s';

                const colorBox = document.createElement('span');
                colorBox.style.width = '14px';
                colorBox.style.height = '14px';
                colorBox.style.backgroundColor = enhancedColors[colorIndex];
                colorBox.style.display = 'inline-block';
                colorBox.style.marginRight = '8px';
                colorBox.style.borderRadius = '2px';

                const label = document.createElement('span');
                label.textContent = `${item.status}: ${item.total}`;
                label.style.flex = '1';
                label.style.whiteSpace = 'normal';
                label.style.wordBreak = 'break-word';

                // Initially all are visible
                legendItem.classList.add('active');

                legendItem.appendChild(colorBox);
                legendItem.appendChild(label);
                legendContainer.appendChild(legendItem);

                // Add hover effect
                legendItem.addEventListener('mouseenter', () => {
                    if (legendItem.classList.contains('active')) {
                        legendItem.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                    }
                });

                legendItem.addEventListener('mouseleave', () => {
                    legendItem.style.backgroundColor = '';
                });

                // Add click functionality for toggling visibility
                legendItem.addEventListener('click', () => {
                    statusVisibility[item.status] = !statusVisibility[item.status];

                    if (statusVisibility[item.status]) {
                        legendItem.classList.add('active');
                        legendItem.style.opacity = '1';
                        label.style.textDecoration = 'none';
                    } else {
                        legendItem.classList.remove('active');
                        legendItem.style.opacity = '0.5';
                        label.style.textDecoration = 'line-through';
                    }

                    updateChart();
                });
            });

            // Created vs finalized bar chart (last 7 days)
            let createdResponse = await fetch(`/api/dashboard/created-vs-finalized?${queryParams}`);
            let createdData = await createdResponse.json();
            createdData = createdData.slice(-7);

            if (createdFinalizedChart) createdFinalizedChart.destroy();

            createdFinalizedChart = new Chart(document.getElementById('createdFinalizedChart'), {
                type: 'bar',
                data: {
                    labels: createdData.map(d => d.created_date),
                    datasets: [
                        { label: 'Vytvorené', data: createdData.map(d => d.created_count), backgroundColor: '#60a5fa' },
                        { label: 'Finalizované', data: createdData.map(d => d.finalized_count), backgroundColor: '#34d399' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { color: '#e2e2e2' } },
                        y: { ticks: { color: '#e2e2e2' } }
                    }
                }
            });
        }


        document.addEventListener('DOMContentLoaded', () => {
            loadFilters();
            loadCharts();
            setInterval(loadCharts, 30000); // Refresh every 30 seconds
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>
    <script>
        document.getElementById('saveDashboardBtn').addEventListener('click', function () {
            const dashboard = document.querySelector('main');

            // Získame štýl pozadia z body
            const bodyBgImage = window.getComputedStyle(document.body).backgroundImage;
            const bodyBgSize = window.getComputedStyle(document.body).backgroundSize;
            const bodyBgPosition = window.getComputedStyle(document.body).backgroundPosition;

            // Vytvoríme offscreen canvas pre pozadie
            const bgCanvas = document.createElement('canvas');
            bgCanvas.width = dashboard.offsetWidth * 2; // Pre vyššiu kvalitu
            bgCanvas.height = dashboard.offsetHeight * 2;

            // Počkáme 500ms, aby sa vykreslili grafy
            setTimeout(() => {
                // Najprv zachytíme samotný dashboard
                html2canvas(dashboard, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: null
                }).then(dashCanvas => {
                    // Vytvoríme nový canvas pre kombinovaný výsledok
                    const finalCanvas = document.createElement('canvas');
                    finalCanvas.width = dashCanvas.width;
                    finalCanvas.height = dashCanvas.height;
                    const ctx = finalCanvas.getContext('2d');

                    // Funkcia na vykreslenie pozadia
                    const drawBackground = () => {
                        // Načítame obrázok pozadia
                        const bgImg = new Image();
                        bgImg.crossOrigin = "Anonymous";

                        bgImg.onload = function() {
                            // Vykreslíme pozadie na canvas
                            ctx.drawImage(bgImg, 0, 0, finalCanvas.width, finalCanvas.height);

                            // Potom vykreslíme dashboard
                            ctx.drawImage(dashCanvas, 0, 0);

                            // Exportujeme ako PNG
                            let image = finalCanvas.toDataURL("image/png");

                            // Vytvorenie odkazu na stiahnutie
                            let link = document.createElement('a');
                            link.href = image;
                            link.download = `dashboard-${new Date().toISOString().slice(0, 10)}.png`;
                            link.click();
                        };

                        // Extrahujeme URL obrázka z CSS
                        const bgUrl = bodyBgImage.replace(/^url\(['"]?/, '').replace(/['"]?\)$/, '');
                        bgImg.src = bgUrl;
                    };

                    // Spustíme vykresľovanie pozadia
                    drawBackground();
                }).catch(error => {
                    console.error("❌ Chyba pri ukladaní obrázka:", error);
                });
            }, 500);
        });
    </script>





@endsection
