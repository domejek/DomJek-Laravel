# Wetter- und Kartenintegration

## Backend-Implementierung

### 1. OpenWeatherMap API Integration
- **ApiController.php**: Wetter- und Geolocation-API-Endpunkte
- **services.php**: OpenWeatherMap Konfiguration
- **.env.example**: API Key Konfiguration

### 2. API-Endpunkte
- `GET /api/weather?lat={lat}&lon={lon}` - Wetterdaten abrufen
- `GET /api/geolocation?address={address}` - Adresskoordinaten abrufen

### 3. Features
- Caching für Wetterdaten (10 Minuten)
- Caching für Geodaten (1 Stunde)
- Fehlerbehandlung bei API-Ausfällen
- Wetter-Icon Mapping

## Frontend-Implementierung

### 1. Kartenintegration
- **Leaflet.js**: OpenStreetMap Integration
- **Geolocation API**: Browser-Standortserkennung
- **Pin-Marker**: Aktuelle Position auf Karte

### 2. Wetter-Widget
- **Live-Daten**: Automatische Aktualisierung alle 10 Minuten
- **Standortbasiert**: Wetter für aktuellen Standort
- **Detaillierte Infos**: Temperatur, Bedingungen, Feuchtigkeit, Wind

### 3. Interaktive Features
- **Standort aktualisieren**: Button für manuelle Aktualisierung
- **Kartenansicht**: Zoom und Navigation möglich
- **Koordinatenanzeige**: Genaue Positionskoordinaten

## Installation

### 1. OpenWeatherMap API Key
1. Registrieren Sie sich unter https://openweathermap.org/api
2. Kopieren Sie Ihren API Key
3. Fügen Sie ihn in die `.env` Datei ein:
   ```
   OPENWEATHERMAP_API_KEY=ihr_api_key_hier
   ```

### 2. Backend starten
```bash
cd backend
composer install
php artisan serve
```

### 3. Frontend öffnen
Öffnen Sie `frontend/index.html` im Browser

## API-Dokumentation

### Wetter API
```
GET /api/weather?lat=52.5200&lon=13.4050
```

Response:
```json
{
  "location": "Berlin",
  "temperature": 22,
  "condition": "sonnig",
  "icon": "☀️",
  "humidity": 45,
  "wind_speed": 3.2,
  "coordinates": {
    "lat": 52.5200,
    "lon": 13.4050
  }
}
```

### Geolocation API
```
GET /api/geolocation?address=Berlin
```

Response:
```json
{
  "address": "Berlin",
  "coordinates": {
    "lat": 52.5200,
    "lon": 13.4050
  },
  "name": "Berlin",
  "country": "DE"
}
```

## Technologie-Stack

### Backend
- Laravel 12
- OpenWeatherMap API
- HTTP Client
- Cache System

### Frontend
- HTML5/CSS3
- Tailwind CSS
- Leaflet.js
- JavaScript (Vanilla)
- Geolocation API