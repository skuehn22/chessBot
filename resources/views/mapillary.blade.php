<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mapillary Test - Gerüststandort Tracking</title>
    <meta name="description" content="Mapillary Integration für automatisches Gerüststandort-Tracking in Berlin">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS (from main app) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Vue 3 and Axios -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        #mapillary-map {
            height: 70vh;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .control-panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .image-popup {
            max-width: 300px;
        }
        
        .image-popup img {
            width: 100%;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .scaffolding-score {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }
        
        .score-high {
            background-color: #d4edda;
            color: #155724;
        }
        
        .score-medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .score-low {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .hotspot-marker {
            background-color: #ff6b6b;
            border: 2px solid white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loading-overlay.active {
            display: flex;
        }
    </style>
</head>
<body>
    <div id="mapillary-app">
        <!-- Navigation Bar -->
        <nav class="navbar navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <i class="fas fa-arrow-left me-2"></i>Zurück zum ChessBot
                </a>
                <span class="navbar-text text-warning">
                    <i class="fas fa-hard-hat me-2"></i>Mapillary Gerüst-Tracking (Test)
                </span>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="container mt-4">
            <!-- Stats Overview -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-images me-2"></i>Bilder gefunden</h5>
                        <h2>@{{ totalImages }}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-building me-2"></i>Potenzielle Gerüste</h5>
                        <h2>@{{ scaffoldingCount }}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-map-marker-alt me-2"></i>Hotspots</h5>
                        <h2>@{{ hotspots.length }}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-clock me-2"></i>Letzte Aktualisierung</h5>
                        <h6>@{{ lastUpdate }}</h6>
                    </div>
                </div>
            </div>
            
            <!-- Control Panel -->
            <div class="control-panel">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Suchradius (Meter)</label>
                        <input 
                            type="range" 
                            class="form-range" 
                            v-model="searchRadius" 
                            min="100" 
                            max="2000" 
                            step="100"
                        >
                        <small class="text-muted">@{{ searchRadius }}m</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Berlin Bezirk</label>
                        <select class="form-select" v-model="selectedDistrict" @change="changeDistrict">
                            <option value="">-- Alle Bezirke --</option>
                            <option value="mitte">Mitte</option>
                            <option value="friedrichshain">Friedrichshain-Kreuzberg</option>
                            <option value="pankow">Pankow</option>
                            <option value="charlottenburg">Charlottenburg-Wilmersdorf</option>
                            <option value="spandau">Spandau</option>
                            <option value="neukoelln">Neukölln</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Aktionen</label>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" @click="searchCurrentArea">
                                <i class="fas fa-search me-2"></i>Bereich durchsuchen
                            </button>
                            <button class="btn btn-warning" @click="loadHotspots">
                                <i class="fas fa-fire me-2"></i>Hotspots anzeigen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Map Container -->
            <div id="mapillary-map"></div>
            
            <!-- Info Panel -->
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Hinweis:</strong> Dies ist eine Test-Integration der Mapillary API. 
                Klicken Sie auf die Marker, um Straßenbilder anzuzeigen. 
                Die Gerüst-Erkennungswahrscheinlichkeit wird momentan noch simuliert.
            </div>
        </div>
        
        <!-- Loading Overlay -->
        <div class="loading-overlay" :class="{ active: isLoading }">
            <div class="text-center">
                <div class="spinner-border text-light" style="width: 3rem; height: 3rem;"></div>
                <h4 class="text-light mt-3">Lade Mapillary-Daten...</h4>
            </div>
        </div>
    </div>
    
    <script>
    const { createApp } = Vue;
    
    createApp({
        data() {
            return {
                map: null,
                markersLayer: null,
                totalImages: 0,
                scaffoldingCount: 0,
                hotspots: [],
                searchRadius: 500,
                selectedDistrict: '',
                lastUpdate: 'Noch nicht aktualisiert',
                isLoading: false,
                districts: {
                    mitte: { lat: 52.531677, lng: 13.381777 },
                    friedrichshain: { lat: 52.515816, lng: 13.454065 },
                    pankow: { lat: 52.569491, lng: 13.402362 },
                    charlottenburg: { lat: 52.516266, lng: 13.304522 },
                    spandau: { lat: 52.534990, lng: 13.199920 },
                    neukoelln: { lat: 52.447085, lng: 13.444399 }
                }
            }
        },
        mounted() {
            this.initMap();
            this.loadInitialData();
        },
        methods: {
            initMap() {
                // Initialize Leaflet map centered on Berlin
                this.map = L.map('mapillary-map').setView([52.520008, 13.404954], 13);
                
                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.map);
                
                // Create a layer group for markers
                this.markersLayer = L.layerGroup().addTo(this.map);
                
                // Add click handler for map
                this.map.on('click', (e) => {
                    this.searchAtLocation(e.latlng.lat, e.latlng.lng);
                });
            },
            
            async loadInitialData() {
                await this.loadHotspots();
                await this.searchCurrentArea();
            },
            
            async searchCurrentArea() {
                const center = this.map.getCenter();
                await this.searchAtLocation(center.lat, center.lng);
            },
            
            async searchAtLocation(lat, lng) {
                this.isLoading = true;
                
                try {
                    const response = await axios.get('/api/mapillary/search-berlin', {
                        params: {
                            lat: lat,
                            lng: lng,
                            radius: this.searchRadius
                        }
                    });
                    
                    if (response.data.success) {
                        this.displayImages(response.data.images);
                        this.totalImages = response.data.total_images;
                        this.scaffoldingCount = response.data.scaffolding_potential;
                        this.lastUpdate = new Date().toLocaleString('de-DE');
                        
                        // Center map on search location
                        this.map.setView([lat, lng], 15);
                    }
                } catch (error) {
                    console.error('Error searching Mapillary:', error);
                    alert('Fehler beim Abrufen der Mapillary-Daten');
                } finally {
                    this.isLoading = false;
                }
            },
            
            displayImages(images) {
                // Clear existing markers
                this.markersLayer.clearLayers();
                
                images.forEach(image => {
                    // Determine marker color based on scaffolding probability
                    let markerColor = 'blue';
                    if (image.scaffolding_probability > 0.7) {
                        markerColor = 'green';
                    } else if (image.scaffolding_probability > 0.4) {
                        markerColor = 'orange';
                    } else {
                        markerColor = 'red';
                    }
                    
                    // Create custom icon
                    const icon = L.divIcon({
                        html: `<div style="background-color: ${markerColor}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;"></div>`,
                        className: 'custom-div-icon',
                        iconSize: [20, 20]
                    });
                    
                    // Create marker
                    const marker = L.marker([image.lat, image.lng], { icon: icon })
                        .bindPopup(this.createPopupContent(image));
                    
                    this.markersLayer.addLayer(marker);
                });
            },
            
            createPopupContent(image) {
                const scoreClass = image.scaffolding_probability > 0.7 ? 'score-high' : 
                                 image.scaffolding_probability > 0.4 ? 'score-medium' : 'score-low';
                
                const capturedDate = image.captured_at ? 
                    new Date(image.captured_at).toLocaleDateString('de-DE') : 
                    'Unbekannt';
                
                return `
                    <div class="image-popup">
                        ${image.thumb_url ? `<img src="${image.thumb_url}" alt="Mapillary Bild">` : ''}
                        <div class="scaffolding-score ${scoreClass}">
                            Gerüst-Wahrscheinlichkeit: ${Math.round(image.scaffolding_probability * 100)}%
                        </div>
                        <p class="mt-2 mb-1"><small>Aufgenommen: ${capturedDate}</small></p>
                        <p class="mb-1"><small>ID: ${image.id}</small></p>
                        <a href="https://www.mapillary.com/app/?image_key=${image.id}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt me-1"></i>In Mapillary öffnen
                        </a>
                    </div>
                `;
            },
            
            async loadHotspots() {
                try {
                    const response = await axios.get('/api/mapillary/construction-hotspots');
                    
                    if (response.data.success) {
                        this.hotspots = response.data.hotspots;
                        this.displayHotspots();
                    }
                } catch (error) {
                    console.error('Error loading hotspots:', error);
                }
            },
            
            displayHotspots() {
                this.hotspots.forEach(hotspot => {
                    const circle = L.circle([hotspot.lat, hotspot.lng], {
                        color: 'red',
                        fillColor: '#f03',
                        fillOpacity: 0.2,
                        radius: 300
                    }).addTo(this.map);
                    
                    circle.bindPopup(`
                        <strong>${hotspot.name}</strong><br>
                        ${hotspot.description}<br>
                        <button class="btn btn-sm btn-primary mt-2" onclick="app.searchAtLocation(${hotspot.lat}, ${hotspot.lng})">
                            Hier suchen
                        </button>
                    `);
                });
            },
            
            changeDistrict() {
                if (this.selectedDistrict && this.districts[this.selectedDistrict]) {
                    const district = this.districts[this.selectedDistrict];
                    this.map.setView([district.lat, district.lng], 14);
                    this.searchAtLocation(district.lat, district.lng);
                }
            }
        }
    }).mount('#mapillary-app');
    
    // Store app instance globally for access from popup buttons
    window.app = document.querySelector('#mapillary-app').__vue_app__._instance.proxy;
    </script>
</body>
</html>