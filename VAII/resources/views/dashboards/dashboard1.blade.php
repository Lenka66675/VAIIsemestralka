@extends('layouts.app')

@section('title', 'Dashboard 1')

@section('content')

    <link rel="stylesheet" href="{{ asset('css/dashboard1.css') }}">

    <div id="dashboardContent" class="container-fluid px-4 py-5">
        <div class="text-center mb-4">
            <img src="{{ asset('images/dashboard icon.png') }}" alt="Dashboard icon" class="me-2" width="64" height="64">
            <h1 class="text-2xl font-bold text-white m-0">Status Overview</h1>
        </div>


        <div class="row justify-content-center mb-4">
            <div class="col-md-3">
                <div class="card bg-dark bg-opacity-50 border-0 shadow">
                    <div class="card-body">
                        <label for="systemFilter" class="form-label text-white fw-semibold mb-2">Select system:</label>
                        <select id="systemFilter" class="form-select bg-dark text-white border-secondary">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark bg-opacity-50 border-0 shadow">
                    <div class="card-body">
                        <label for="countryFilter" class="form-label text-white fw-semibold mb-2">Select country:</label>
                        <select id="countryFilter" class="form-select bg-dark text-white border-secondary">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>




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
                        <h2 class="card-title text-lg font-semibold text-white mb-4">Created vs. Finalized</h2>
                        <div class="chart-container" style="position: relative; height:350px;">
                            <canvas id="createdFinalizedChart"></canvas>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let statusChart, createdFinalizedChart;
        const chartColors =[
            '#ffcccc', '#ff9999', '#ff6666', '#ff3333', '#ff0000',
            '#cc0000', '#990000', '#660000', '#330000', '#e60000',
            '#ff4d4d', '#b30000', '#ff1a1a', '#ff7373', '#ff9999'
        ];

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
                option.value = country.name;
                option.textContent = country.name;

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

            let statusResponse = await fetch(`/api/dashboard/summary?${queryParams}`);
            let statusData = await statusResponse.json();

            if (statusChart) statusChart.destroy();

            const enhancedColors = [
                '#ffcccc', '#ff9999', '#ff6666', '#ff3333', '#ff0000',
                '#cc0000', '#990000', '#660000', '#330000', '#e60000',
                '#ff4d4d', '#b30000', '#ff1a1a', '#ff7373', '#ff9999'
            ];


            const statusVisibility = {};

            statusData.forEach(item => {
                statusVisibility[item.status] = true;
            });

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
            let startTime = performance.now();

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
                    }, elements: {
                        arc: {
                            borderWidth: 0.5,
                            borderColor: '#000'
                        }
                    }
                }
            });
            let endTime = performance.now();
            console.log(`Vykreslenie statusChart trvalo ${Math.round(endTime - startTime)} ms`);
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

                legendItem.classList.add('active');

                legendItem.appendChild(colorBox);
                legendItem.appendChild(label);
                legendContainer.appendChild(legendItem);

                legendItem.addEventListener('mouseenter', () => {
                    if (legendItem.classList.contains('active')) {
                        legendItem.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                    }
                });

                legendItem.addEventListener('mouseleave', () => {
                    legendItem.style.backgroundColor = '';
                });

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

            let createdResponse = await fetch(`/api/dashboard/created-vs-finalized?${queryParams}`);
            let createdData = await createdResponse.json();
            createdData = createdData.slice(-7);

            if (createdFinalizedChart) createdFinalizedChart.destroy();

            createdFinalizedChart = new Chart(document.getElementById('createdFinalizedChart'), {
                type: 'bar',
                data: {
                    labels: createdData.map(d => d.created_date),
                    datasets: [
                        { label: 'Created', data: createdData.map(d => d.created_count), backgroundColor: '#FF9999FF' },
                        { label: 'Finalized', data: createdData.map(d => d.finalized_count), backgroundColor: '#990000FF' }
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
            setInterval(loadCharts, 300000);
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>
    <script>
        document.getElementById('saveDashboardBtn').addEventListener('click', function () {
            const dashboard = document.getElementById('dashboardContent');

            const bodyBgImage = window.getComputedStyle(document.body).backgroundImage;
            const bodyBgSize = window.getComputedStyle(document.body).backgroundSize;
            const bodyBgPosition = window.getComputedStyle(document.body).backgroundPosition;

            const bgCanvas = document.createElement('canvas');
            bgCanvas.width = dashboard.offsetWidth * 2;
            bgCanvas.height = dashboard.offsetHeight * 2;

            setTimeout(() => {
                html2canvas(dashboard, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: null
                }).then(dashCanvas => {
                    const finalCanvas = document.createElement('canvas');
                    finalCanvas.width = dashCanvas.width;
                    finalCanvas.height = dashCanvas.height;
                    const ctx = finalCanvas.getContext('2d');

                    const drawBackground = () => {
                        const bgImg = new Image();
                        bgImg.crossOrigin = "Anonymous";

                        bgImg.onload = function() {
                            ctx.drawImage(bgImg, 0, 0, finalCanvas.width, finalCanvas.height);

                            ctx.drawImage(dashCanvas, 0, 0);

                            let image = finalCanvas.toDataURL("image/png");

                            let link = document.createElement('a');
                            link.href = image;
                            link.download = `dashboard-${new Date().toISOString().slice(0, 10)}.png`;
                            link.click();
                        };

                        const bgUrl = bodyBgImage.replace(/^url\(['"]?/, '').replace(/['"]?\)$/, '');
                        bgImg.src = bgUrl;
                    };

                    drawBackground();
                }).catch(error => {
                    console.error("Chyba pri ukladaní obrázka:", error);
                });
            }, 500);
        });
    </script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        document.getElementById('saveToDatabaseBtn').addEventListener('click', function () {
            const dashboard = document.getElementById('dashboardContent');

            const bodyBgImage = window.getComputedStyle(document.body).backgroundImage;
            const bodyBgSize = window.getComputedStyle(document.body).backgroundSize;
            const bodyBgPosition = window.getComputedStyle(document.body).backgroundPosition;

            setTimeout(() => {
                html2canvas(dashboard, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: null
                }).then(dashCanvas => {
                    const finalCanvas = document.createElement('canvas');
                    finalCanvas.width = dashCanvas.width;
                    finalCanvas.height = dashCanvas.height;
                    const ctx = finalCanvas.getContext('2d');

                    const drawBackground = () => {
                        const bgImg = new Image();
                        bgImg.crossOrigin = "Anonymous";

                        bgImg.onload = function () {
                            ctx.drawImage(bgImg, 0, 0, finalCanvas.width, finalCanvas.height);

                            ctx.drawImage(dashCanvas, 0, 0);

                            let imageData = finalCanvas.toDataURL("image/png");

                            fetch('/screenshots', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ image: imageData })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.message) {
                                        alert('Screenshot uložený do databázy!');
                                    } else {
                                        alert('Chyba pri ukladaní screenshotu.');
                                    }
                                })
                                .catch(error => {
                                    console.error('Chyba pri odosielaní:', error);
                                    alert('Chyba pri ukladaní screenshotu.');
                                });
                        };

                        const bgUrl = bodyBgImage.replace(/^url\(['"]?/, '').replace(/['"]?\)$/, '');
                        if (bgUrl && bgUrl !== 'none') {
                            bgImg.src = bgUrl;
                        } else {
                            ctx.drawImage(dashCanvas, 0, 0);
                            let imageData = finalCanvas.toDataURL("image/png");
                            saveToDatabase(imageData);
                        }
                    };

                    drawBackground();
                }).catch(error => {
                    console.error("Chyba pri ukladaní obrázka:", error);
                    alert('Chyba pri ukladaní screenshotu.');
                });
            }, 500);
        });

        function saveToDatabase(imageData) {
            fetch('/screenshots', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ image: imageData })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert('Screenshot uložený do databázy!');
                    } else {
                        alert('Chyba pri ukladaní screenshotu.');
                    }
                })
                .catch(error => {
                    console.error('Chyba pri odosielaní:', error);
                    alert('Chyba pri ukladaní screenshotu.');
                });
        }
    </script>




@endsection
