@extends('layouts.app')

@section('title', 'Mapa podľa krajín')

@section('content')

    <link rel="stylesheet" href="{{ asset('css/dashboard3.css') }}">

    <div id="mapDashboardCapture">
        <div class="container">
            <div class="text-center my-4">
                <img src="{{ asset('images/dashboard icon.png') }}" alt="Dashboard icon" class="me-2" width="64" height="64">
                <h1 class="text-2xl font-bold text-white m-0">Country Comparison</h1>
            </div>

            <div class="row text-white text-center mb-4" id="statCards">
                <div class="col-md-6">
                    <div class="card custom-card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Created</h5>
                            <p id="statCreated" class="display-6">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card custom-card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Finalized</h5>
                            <p id="statFinalized" class="display-6">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center filter-box">
                <div class="col-md-3">
                    <label for="regionFilter" class="form-label">Region:</label>
                    <select id="regionFilter" class="form-select">
                        <option value="">All regions</option>
                        <option value="EMEA">EMEA</option>
                        <option value="APAC">APAC</option>
                        <option value="AMER">AMER</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Countries :</label>
                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle w-100" type="button" id="countryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Select country
                        </button>
                        <ul class="dropdown-menu w-100" id="countryDropdownMenu" aria-labelledby="countryDropdown">
                        </ul>
                    </div>
                </div>


            <div id="map"></div>
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        let map, markersLayer, allData = [];

        document.addEventListener("DOMContentLoaded", () => {
            map = L.map('map', { preferCanvas: true }).setView([20, 10], 2);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://carto.com/">CARTO</a>, © OpenStreetMap contributors'
            }).addTo(map);

            markersLayer = L.layerGroup().addTo(map);

            loadCountries();
            loadMapData();

            document.getElementById("regionFilter").addEventListener("change", applyFilters);
        });

        async function loadCountries() {
            let response = await fetch('/api/countries');
            let countries = await response.json();

            const menu = document.getElementById('countryDropdownMenu');
            menu.innerHTML = '';

            countries.forEach(country => {
                const li = document.createElement('li');
                li.innerHTML = `
        <label class="dropdown-item">
            <input type="checkbox" class="country-checkbox" value="${country.name}">
            ${country.name} (${country.region})
        </label>`;
                countryDropdownMenu.appendChild(li);
            });


            menu.addEventListener('change', applyFilters);
        }

        async function loadMapData() {
            const response = await fetch('/api/map-data');
            allData = await response.json();
            applyFilters();
        }

        function applyFilters() {
            const selectedRegion = document.getElementById("regionFilter").value;
            const selectedCountries = Array.from(document.querySelectorAll(".country-checkbox:checked")).map(cb => cb.value);

            markersLayer.clearLayers();
            updateStats();

            const filtered = allData.filter(item => {
                const matchRegion = !selectedRegion || item.region === selectedRegion;
                const matchCountry = selectedCountries.length === 0 || selectedCountries.includes(item.country);
                return matchRegion && matchCountry;
            });

            filtered.forEach(item => {
                if (item.latitude && item.longitude) {
                    const lat = parseFloat(item.latitude);
                    const lon = parseFloat(item.longitude);
                    if (!isNaN(lat) && !isNaN(lon)) {
                        const markerSize = Math.min(10, 4 + item.count * 0.5);

                        L.circleMarker([lat, lon], {
                            radius: markerSize,
                            color: 'red',
                            fillColor: 'black',
                            fillOpacity: 0.6
                        }).addTo(markersLayer)
                            .bindPopup(`<strong>${item.country}</strong><br>Počet výskytov: ${item.count}`);
                    }
                }
            });

            if (markersLayer.getLayers().length > 0) {
                map.fitBounds(markersLayer.getBounds(), { padding: [20, 20] });
                setTimeout(() => {
                    console.log("Mapa ustálená");
                }, 300);
            }
        }

        function updateStats() {
            const region = document.getElementById("regionFilter").value;
            const selectedCountries = Array.from(document.querySelectorAll(".country-checkbox:checked")).map(cb => cb.value);

            const params = new URLSearchParams();
            if (region) params.append('region', region);
            selectedCountries.forEach(c => params.append('countries[]', c));

            fetch('/api/dashboard-stats?' + params.toString())
                .then(res => res.json())
                .then(data => {
                    document.getElementById('statCreated').textContent = data.created ?? 0;
                    document.getElementById('statFinalized').textContent = data.finalized ?? 0;
                });
        }

        document.getElementById('saveDashboardBtn').addEventListener('click', function () {
            const dashboard = document.getElementById('mapDashboardCapture');
            const bodyBgImage = window.getComputedStyle(document.body).backgroundImage;
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = dashboard.offsetWidth * 2;
            finalCanvas.height = dashboard.offsetHeight * 2;
            const ctx = finalCanvas.getContext('2d');
            map.stop();

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
                        link.download = `map-dashboard-${new Date().toISOString().slice(0, 10)}.png`;
                        link.click();
                    };
                    const bgUrl = bodyBgImage.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
                    bgImg.src = bgUrl;
                });
            }, 500);
        });

        // SAVE TO DB
        document.getElementById('saveToDatabaseBtn').addEventListener('click', function () {
            const dashboard = document.getElementById('mapDashboardCapture');
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
