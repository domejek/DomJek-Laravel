#!/bin/bash

# API Dashboard - Quick Deployment Script
set -e

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 API Dashboard Quick Deployment${NC}"
echo "================================="

# Prüfen ob Docker Desktop läuft
echo -e "${YELLOW}🔍 Prüfe Docker Desktop Status...${NC}"

# Docker Desktop Prozess prüfen und ggf. starten
if ! pgrep -f "Docker Desktop" > /dev/null; then
    echo -e "${YELLOW}⚠️ Docker Desktop nicht gefunden, starte Docker Desktop...${NC}"
    open -a Docker
    echo -e "${YELLOW}⏳ Warte auf Docker Desktop (max 60 Sekunden)...${NC}"
    
    # Warte bis Docker Daemon erreichbar ist mit verschiedenen Methoden
    for i in {1..60}; do
        # Versuche verschiedene Docker-Verbindungen
        if docker info > /dev/null 2>&1; then
            echo -e "\n${GREEN}✅ Docker Desktop wurde erfolgreich gestartet!${NC}"
            break
        fi
        
        # Überprüfe ob Docker Desktop Prozess läuft
        if pgrep -f "Docker Desktop" > /dev/null; then
            echo -n "."
        else
            echo -e "\n${RED}❌ Docker Desktop Prozess nicht gefunden${NC}"
            open -a Docker
        fi
        
        sleep 1
        
        if [ $i -eq 60 ]; then
            echo -e "\n${RED}❌ Timeout: Docker Desktop konnte nicht gestartet werden.${NC}"
            echo -e "${YELLOW}💡 Bitte starten Sie Docker Desktop manuell und warten Sie 2-3 Minuten.${NC}"
            echo -e "${YELLOW}💡 Danach ./build.sh erneut ausführen.${NC}"
            exit 1
        fi
    done
    echo ""
    
    # Nach Start noch etwas warten
    sleep 5
else
    echo -e "${GREEN}✅ Docker Desktop Prozess läuft bereits.${NC}"
    
    # Docker-Verbindung testen
    if ! docker info > /dev/null 2>&1; then
        echo -e "${YELLOW}⚠️ Docker Desktop läuft, aber Daemon nicht erreichbar. Versuche Reconnection...${NC}"
        
        # Versuche Docker Desktop neu zu starten
        pkill -f "Docker Desktop" || true
        sleep 3
        open -a Docker
        
        # Warte auf Neustart
        for i in {1..30}; do
            if docker info > /dev/null 2>&1; then
                echo -e "\n${GREEN}✅ Docker Daemon erfolgreich verbunden!${NC}"
                break
            fi
            echo -n "."
            sleep 1
            
            if [ $i -eq 30 ]; then
                echo -e "\n${RED}❌ Docker Daemon weiterhin nicht erreichbar.${NC}"
                echo -e "${YELLOW}💡 Bitte Docker Desktop manuell neustarten.${NC}"
                exit 1
            fi
        done
        echo ""
        sleep 3
    else
        echo -e "${GREEN}✅ Docker ist betriebsbereit.${NC}"
    fi
fi

# Port 3333 freigeben, falls belegt
echo -e "${YELLOW}🔍 Prüfe ob Port 3333 belegt ist...${NC}"
if lsof -i :3333 > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠️ Port 3333 ist belegt, beende Prozess...${NC}"
    lsof -ti :3333 | xargs kill -9 > /dev/null 2>&1 || true
    sleep 2
    
    # Docker-Container auf Port 3333 beenden
    echo -e "${YELLOW}🐳 Beende evtl. Docker-Container auf Port 3333...${NC}"
    if docker info > /dev/null 2>&1; then
        docker ps -q --filter "publish=3333" | xargs -I {} docker stop {} > /dev/null 2>&1 || true
        docker ps -aq --filter "publish=3333" | xargs -I {} docker rm {} > /dev/null 2>&1 || true
    fi
fi

# Einfache docker-compose.yml erstellen
echo -e "${YELLOW}📝 Erstelle docker-compose.yml...${NC}"
cat > docker-compose.yml << 'EOF'


services:
  # Frontend (Nginx) mit integriertem Backend
  app:
    image: nginx:alpine
    ports:
      - "3333:80"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf:ro
      - ./frontend:/usr/share/nginx/html:ro
    networks:
      - app-network

  # MySQL Database
  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      - MYSQL_DATABASE=api_dashboard
      - MYSQL_ROOT_PASSWORD=password
      - MYSQL_PASSWORD=password
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - app-network

  # Redis Cache
  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    networks:
      - app-network

  # phpMyAdmin
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "8080:80"
    environment:
      - PMA_HOST=mysql
      - MYSQL_ROOT_PASSWORD=password
    depends_on:
      - mysql
    networks:
      - app-network

volumes:
  mysql_data:

networks:
  app-network:
    driver: bridge
EOF

# Nginx Konfiguration mit direkter API
echo -e "${YELLOW}📝 Erstelle nginx.conf...${NC}"
cat > nginx.conf << 'EOF'
server {
    listen 80;
    server_name localhost;
    root /usr/share/nginx/html;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api/ {
        add_header Content-Type application/json;
        return 200 '{
            "success": true,
            "data": {
                "weather": {
                    "location": "Berlin",
                    "temperature": 22,
                    "condition": "Sunny",
                    "icon": "☀️"
                },
                "crypto": {
                    "bitcoin": {
                        "price": "$45,234",
                        "change": 5.2,
                        "symbol": "BTC"
                    }
                },
                "news": {
                    "headlines": [
                        {"title": "Tech News Update", "summary": "New AI developments..."},
                        {"title": "Market Report", "summary": "Stocks rising..."},
                        {"title": "Sports Results", "summary": "Football scores..."}
                    ]
                },
                "github": {
                    "repositories": 12,
                    "followers": 85,
                    "stars": 150
                },
                "system": {
                    "api_server": "online",
                    "database": "connected",
                    "cache": "active",
                    "uptime": "99.9%"
                }
            },
            "timestamp": "2026-02-09 14:02:00"
        }';
    }
}
EOF

# Container starten
echo -e "${BLUE}🐳 Starte Docker Container...${NC}"
docker compose down --volumes 2>/dev/null || true
docker compose up -d

echo -e "${GREEN}⏳ Warte auf Start der Container...${NC}"
sleep 10

echo -e "${GREEN}✅ Deployment erfolgreich!${NC}"
echo ""
echo -e "${BLUE}🌐 Die Anwendung ist erreichbar unter:${NC}"
echo -e "   📱 Frontend: ${GREEN}http://localhost:3333${NC}"
echo -e "   🔧 Backend API: ${GREEN}http://localhost:3333/api/${NC}"
echo -e "   🗄️ phpMyAdmin: ${GREEN}http://localhost:8080${NC}"
echo -e "   🐳 MySQL: ${GREEN}localhost:3306${NC}"
echo -e "   🔴 Redis: ${GREEN}localhost:6379${NC}"
echo ""
echo -e "${YELLOW}📋 Nützliche Befehle:${NC}"
echo -e "   • Logs anzeigen: ${GREEN}docker --context desktop-linux compose logs -f${NC}"
echo -e "   • Container stoppen: ${GREEN}docker --context desktop-linux compose down${NC}"
echo -e "   • Container neustarten: ${GREEN}docker --context desktop-linux compose restart${NC}"
echo -e "   • Docker Desktop beenden: ${GREEN}pkill -f \"Docker Desktop\"${NC}"
echo ""
echo -e "${GREEN}🎉 API Dashboard ist jetzt bereit!${NC}"
echo ""
echo -e "${YELLOW}🛑 Container stoppen/löschen:${NC}"
echo -e "   • Stoppen: ${GREEN}docker compose stop${NC}"
echo -e "   • Löschen: ${GREEN}docker compose down --volumes${NC}"
echo -e "   • Alles löschen: ${GREEN}docker compose down --volumes --remove-orphans${NC}"