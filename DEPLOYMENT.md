# API Dashboard - Docker Deployment

## 🚀 Schnellstart

1. **Stellen Sie sicher, dass Docker Desktop läuft**
2. **Führen Sie das Build-Skript aus:**
   ```bash
   ./build.sh
   ```
3. **Öffnen Sie die Anwendung:**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8000
   - phpMyAdmin: http://localhost:8080

## 📋 Was das Skript macht

### 🐳 Docker-Container
- **Frontend (Nginx)**: Serviert die statische HTML/CSS/JS Oberfläche auf Port 3000
- **Backend (Laravel)**: PHP-FPM Server mit Laravel auf Port 8000
- **MySQL 8.0**: Datenbank auf Port 3306
- **Redis 7**: Caching auf Port 6379
- **phpMyAdmin**: Datenbank-Verwaltung auf Port 8080

### 🔧 Konfiguration
- Erstellt alle notwendigen Dockerfile und Konfigurationsdateien
- Generiert Laravel Application Key
- Führt Datenbank-Migrationen durch
- Setzt Environment-Variablen für alle Services
- Konfiguriert Netzwerk-Verbindung zwischen Containern

### 📁 Ordner-Struktur nach Deployment
```
/
├── backend/
│   ├── Dockerfile
│   ├── .env
│   └── Laravel Anwendung
├── frontend/
│   ├── Dockerfile
│   ├── nginx.conf
│   └── Statische Webanwendung
├── docker-compose.yml
└── build.sh
```

## 🛠️ Nützliche Befehle

```bash
# Alle Container stoppen und entfernen
docker-compose down --volumes

# Container neu starten
docker-compose restart

# Logs anzeigen
docker-compose logs -f
docker-compose logs -f frontend
docker-compose logs -f backend

# Laravel Commands ausführen
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan tinker

# MySQL Shell
docker-compose exec mysql mysql -u root -p

# Redis Shell
docker-compose exec redis redis-cli

# Container neu bauen (nach Änderungen)
docker-compose build --no-cache
docker-compose up -d
```

## 🔌 API Endpoints

### Frontend (Port 3000)
- `/` - Haupt-Dashboard

### Backend API (Port 8000)
- `/` - Dashboard View
- `/api/dashboard/data` - Dashboard-Daten (JSON)
- `/api/{provider}` - API-Endpunkte für spezifische Provider

## 🗄️ Datenbank-Zugriff

**MySQL Credentials:**
- Host: localhost:3306
- Database: api_dashboard
- Username: root
- Password: password

**phpMyAdmin:**
- URL: http://localhost:8080
- Server: mysql
- Username: root
- Password: password

## 🔍 Troubleshooting

### Port-Konflikte
```bash
# Prüfen ob Ports belegt sind
lsof -i :3000
lsof -i :8000
lsof -i :3306

# Andere Ports in docker-compose.yml anpassen
```

### Container nicht startend
```bash
# Logs prüfen
docker-compose logs

# Container neu bauen
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Backend Probleme
```bash
# Cache leeren
docker-compose exec backend php artisan config:clear
docker-compose exec backend php artisan route:clear
docker-compose exec backend php artisan view:clear

# Migration rollback
docker-compose exec backend php artisan migrate:rollback
```

## 🎯 Features nach Deployment

✅ **Responsive Dashboard** mit Drag & Drop
✅ **Live Updates** (alle 30 Sekunden)
✅ **Weather Widget** mit simulierten Daten
✅ **Crypto Widget** mit Preis-Updates
✅ **News Widget** mit Top-Headlines
✅ **GitHub Widget** mit Repo-Stats
✅ **System Status Widget**
✅ **Performance Chart** mit Chart.js
✅ **RESTful API** für alle Widget-Daten
✅ **MySQL Datenbank** für persistente Daten
✅ **Redis Caching** für Performance
✅ **phpMyAdmin** für Datenbank-Management

Viel Spaß mit Ihrem API Dashboard! 🎉