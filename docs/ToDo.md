# API Dashboard Aggregator - Projekt To-Do

## Projektbeschreibung
Ein modulares API-Dashboard, das Daten von verschiedenen öffentlichen APIs sammelt und in Widgets anzeigt. Backend: Laravel, Frontend: JavaScript, läuft unter Docker.

## Phase 1: Grundsetup ✅
- [x] Dokumentationsstruktur erstellen
- [ ] Laravel-Projekt initialisieren
- [ ] Docker-Setup mit Laravel Sail
- [ ] Basis-Verzeichnisstruktur für Module

## Phase 2: Backend-Entwicklung
- [ ] Datenbank-Migrationen erstellen
  - [ ] Users-Tabelle
  - [ ] Widgets-Tabelle  
  - [ ] Api_Connectors-Tabelle
  - [ ] Widget_Data-Tabelle
- [ ] Models erstellen (User, Widget, ApiConnector, WidgetData)
- [ ] Controller anlegen
  - [ ] DashboardController
  - [ ] WidgetController
  - [ ] ApiController
- [ ] API-Routes definieren
- [ ] Basis-Authentifizierung implementieren

## Phase 3: API-Integration Framework
- [ ]抽象 ApiConnector Basisklasse
- [ ] Wetter-API-Connector (OpenWeatherMap)
- [ ] Krypto-API-Connector (CoinGecko)
- [ ] Nachrichten-API-Connector (NewsAPI)
- [ ] GitHub-API-Connector
- [ ] API-Antwort-Caching implementieren

## Phase 4: Frontend-Entwicklung
- [ ] Dashboard-Layout erstellen
- [ ] Widget-System mit JavaScript
- [ ] Drag & Drop für Widgets
- [ ] Real-time Updates mit WebSocket/Polling
- [ ] Responsive Design

## Phase 5: Module & Erweiterungen
- [ ] Widget-Konfigurationssystem
- [ ] User-Präferenzen speichern
- [ ] Export-Funktionen (PDF, CSV)
- [ ] Dark Mode Theme
- [ ] Multi-Language Support

## Phase 6: Deployment & Testing
- [ ] Unit Tests für Backend
- [ ] Frontend Tests
- [ ] Docker Optimierung
- [ ] Production Deployment Guide

## Technische Anforderungen

### Backend (Laravel)
- PHP 8.2+
- MySQL/PostgreSQL
- Redis für Caching
- Queue System für API-Calls

### Frontend
- Vanilla JavaScript oder Alpine.js
- Tailwind CSS
- Chart.js für Visualisierungen

### Docker
- Laravel Sail Setup
- Separate Container für Web, DB, Redis, Queue

## API-Liste für Integration
1. **Wetter**: OpenWeatherMap API
2. **Krypto**: CoinGecko API
3. **Nachrichten**: NewsAPI.org
4. **GitHub**: GitHub REST API
5. **JSONPlaceholder**: Für Testing
6. **SpaceX**: SpaceX API
7. **Rick & Morty**: Für Testing

## Module Struktur
```
app/Modules/
├── Weather/
├── Crypto/
├── News/
├── GitHub/
└── Core/
```

## Nächste Schritte
1. Laravel Projekt mit `composer create-project`
2. `composer require laravel/sail` für Docker
3. Basis-Migrationen anlegen
4. Ersten API-Connector implementieren