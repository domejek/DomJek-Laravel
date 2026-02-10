# API Dashboard Aggregator - Systemarchitektur

## Übersicht
Ein modulares Dashboard-System zur Aggregation und Visualisierung von Daten aus verschiedenen öffentlichen APIs.

## Architektur-Diagramm
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   External APIs │
│                 │    │   (Laravel)     │    │                 │
│ - Dashboard UI  │◄──►│ - REST API      │◄──►│ - Weather APIs  │
│ - Widget System │    │ - Queue System  │    │ - Crypto APIs   │
│ - Real-time     │    │ - Cache Layer   │    │ - News APIs     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │              ┌─────────────────┐              │
         │              │   Database      │              │
         └──────────────►│   (MySQL/PG)    │◄─────────────┘
                        │ - Users         │
                        │ - Widgets       │
                        │ - API Data      │
                        └─────────────────┘
```

## Backend-Architektur (Laravel)

### 1. Verzeichnisstruktur
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── WidgetController.php
│   │   └── ApiController.php
│   └── Middleware/
├── Models/
│   ├── User.php
│   ├── Widget.php
│   ├── ApiConnector.php
│   └── WidgetData.php
├── Services/
│   ├── ApiConnectorService.php
│   ├── WidgetService.php
│   └── CacheService.php
├── Jobs/
│   ├── FetchApiData.php
│   └── ProcessWidgetData.php
└── Modules/
    ├── Weather/
    ├── Crypto/
    ├── News/
    └── Core/
```

### 2. Datenbank-Design

#### Users Tabelle
```sql
- id (bigint, primary)
- name (string)
- email (string, unique)
- password (string)
- preferences (json)
- created_at, updated_at
```

#### Widgets Tabelle
```sql
- id (bigint, primary)
- user_id (bigint, foreign)
- title (string)
- type (string) - weather, crypto, news, github
- position (json) - x, y, width, height
- config (json) - API-Parameter
- refresh_interval (integer) - seconds
- is_active (boolean)
- created_at, updated_at
```

#### Api_Connectors Tabelle
```sql
- id (bigint, primary)
- name (string)
- api_endpoint (string)
- auth_required (boolean)
- rate_limit (integer) - requests per hour
- cache_duration (integer) - seconds
- config_schema (json)
- created_at, updated_at
```

#### Widget_Data Tabelle
```sql
- id (bigint, primary)
- widget_id (bigint, foreign)
- data (json)
- fetched_at (timestamp)
- expires_at (timestamp)
```

### 3. API-Konnektor System

#### Abstract Base Class
```php
abstract class ApiConnector
{
    abstract public function fetch(array $params): array;
    abstract public function validateConfig(array $config): bool;
    abstract public function getCacheDuration(): int;
}
```

#### Beispiel: Weather Connector
```php
class WeatherConnector extends ApiConnector
{
    public function fetch(array $params): array
    {
        // OpenWeatherMap API call
    }
}
```

## Frontend-Architektur

### 1. Komponentenstruktur
```
resources/js/
├── components/
│   ├── Dashboard.js
│   ├── Widget.js
│   ├── WidgetContainer.js
│   └── Modal.js
├── services/
│   ├── ApiService.js
│   ├── WebSocketService.js
│   └── StorageService.js
├── utils/
│   ├── helpers.js
│   └── constants.js
└── widgets/
    ├── WeatherWidget.js
    ├── CryptoWidget.js
    └── NewsWidget.js
```

### 2. Widget-System
- **Drag & Drop**: Sortable.js für Widget-Positionierung
- **Real-time Updates**: WebSocket oder Server-Sent Events
- **Responsive**: Grid-basiertes Layout
- **Caching**: Browser-Storage für Offline-Ansicht

### 3. State Management
Einfacher State-Management mit Vanilla JS:
```javascript
class DashboardState {
    constructor() {
        this.widgets = [];
        this.layout = {};
    }
    
    addWidget(widget) { /* ... */ }
    updateWidget(id, data) { /* ... */ }
    removeWidget(id) { /* ... */ }
}
```

## Docker-Architektur

### Container-Setup
```yaml
# docker-compose.yml
services:
    app:
        build: .
        ports: ["8080:80"]
    mysql:
        image: mysql:8.0
    redis:
        image: redis:7
    npm:
        image: node:18
```

### Volume-Struktur
```
volumes/
├── mysql_data/
├── redis_data/
└── app_storage/
```

## Caching-Strategie

### Multi-Level Caching
1. **Browser Cache**: Static Assets (1 Stunde)
2. **Redis Cache**: API-Antworten (15-60 Minuten)
3. **Database Cache**: Widget-Konfigurationen
4. **CDN**: Production Assets

### Cache Keys
```
widget:data:{widget_id}
api:response:{connector}:{params_hash}
user:preferences:{user_id}
```

## Security-Architektur

### 1. Authentication
- Laravel Sanctum für API-Tokens
- Session-basierte Auth für Web
- Rate Limiting für API-Calls

### 2. API Security
- Request Signierung für externe APIs
- Input Validation & Sanitization
- CORS Configuration

### 3. Data Protection
- Environment Variables für API-Keys
- Encryption für sensible Daten
- Audit Logging

## Performance-Optimierungen

### 1. Backend
- Queue System für API-Calls
- Database Indexing
- Lazy Loading für Widgets
- API Response Compression

### 2. Frontend
- Code Splitting per Widget-Type
- Lazy Loading von Widget-Komponenten
- Image Optimization
- Service Worker für Offline

## Monitoring & Logging

### 1. Application Monitoring
- Laravel Telescope für Development
- Error Tracking mit Sentry
- Performance Metrics

### 2. API Monitoring
- Response Times
- Error Rates
- Cache Hit Rates
- Queue Processing Times

## Deployment-Architektur

### 1. Environment Setup
- Development: Docker Compose
- Staging: Docker Swarm
- Production: Kubernetes oder Cloud Hosting

### 2. CI/CD Pipeline
- GitHub Actions
- Automated Testing
- Docker Image Building
- Zero-Downtime Deployment