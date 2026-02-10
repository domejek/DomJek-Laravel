# Docker Setup Guide

## Übersicht
Complete Docker-Konfiguration für den API Dashboard Aggregator mit Laravel Sail.

## Voraussetzungen
- Docker Desktop (Mac/Windows) oder Docker Engine (Linux)
- Docker Compose
- Git

## Projekt-Setup

### 1. Laravel Projekt erstellen
```bash
# Neues Laravel Projekt
composer create-project laravel/laravel api-dashboard-aggregator

# In Projektverzeichnis wechseln
cd api-dashboard-aggregator

# Laravel Sail installieren
composer require laravel/sail --dev
php artisan sail:install
```

### 2. Docker Konfiguration

#### docker-compose.yml
```yaml
version: '3'

services:
    app:
        build:
            context: ./docker/8.2
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP:-1000}'
        image: sail-8.2/app
        extra_hosts:
            - 'host.docker.internal:host-gateway'
        ports:
            - '${APP_PORT:-80}:80'
        environment:
            WWWUSER: '${WWWUSER:-1000}'
            LARAVEL_SAIL: 1
        volumes:
            - '.:/var/www/html'
        networks:
            - sail
        depends_on:
            - mysql
            - redis
            - meilisearch

    mysql:
        image: 'mysql/mysql-server:8.0'
        ports:
            - '${FORWARD_DB_PORT:-3306}:3306'
        environment:
            MYSQL_ROOT_PASSWORD: '${DB_PASSWORD}'
            MYSQL_ROOT_HOST: '%'
            MYSQL_DATABASE: '${DB_DATABASE}'
            MYSQL_USER: '${DB_USERNAME}'
            MYSQL_PASSWORD: '${DB_PASSWORD}'
            MYSQL_ALLOW_EMPTY_PASSWORD: 'yes'
        volumes:
            - 'sail-mysql:/var/lib/mysql'
            - './docker/mysql/create-testing-database.sh:/docker-entrypoint-initdb.d/10-create-testing-database.sh'
        networks:
            - sail
        healthcheck:
            test: ["CMD", "mysqladmin", "ping", "-p${DB_PASSWORD}"]
            retries: 3
            timeout: 5s

    redis:
        image: 'redis:7-alpine'
        ports:
            - '${FORWARD_REDIS_PORT:-6379}:6379'
        volumes:
            - 'sail-redis:/data'
        networks:
            - sail
        healthcheck:
            test: ["CMD", "redis-cli", "ping"]
            retries: 3
            timeout: 5s

    meilisearch:
        image: 'getmeili/meilisearch:latest'
        ports:
            - '${FORWARD_MEILISEARCH_PORT:-7700}:7700'
        environment:
            MEILI_MASTER_KEY: '${MEILISEARCH_KEY}'
        volumes:
            - 'sail-meilisearch:/meili_data'
        networks:
            - sail

    mailhog:
        image: 'mailhog/mailhog:latest'
        ports:
            - '${FORWARD_MAILHOG_PORT:-1025}:1025'
            - '${FORWARD_MAILHOG_DASHBOARD_PORT:-8025}:8025'
        networks:
            - sail

    # Queue Worker
    queue:
        build:
            context: ./docker/8.2
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP:-1000}'
        image: sail-8.2/app
        command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
        volumes:
            - '.:/var/www/html'
        networks:
            - sail
        depends_on:
            - mysql
            - redis

    # Node.js für Frontend Assets
    node:
        image: node:18-alpine
        ports:
            - '${VITE_PORT:-5173}:5173'
        volumes:
            - '.:/var/www/html'
        working_dir: /var/www/html
        command: sh -c "npm install && npm run dev"
        networks:
            - sail

    # Nginx (optional für Production)
    nginx:
        image: nginx:alpine
        ports:
            - '${NGINX_PORT:-8080}:80'
        volumes:
            - './docker/nginx/nginx.conf:/etc/nginx/nginx.conf'
            - './docker/nginx/sites:/etc/nginx/sites-available'
            - './public:/var/www/html/public'
        networks:
            - sail
        depends_on:
            - app

networks:
    sail:
        driver: bridge

volumes:
    sail-mysql:
        driver: local
    sail-redis:
        driver: local
    sail-meilisearch:
        driver: local
```

#### .env Konfiguration
```env
# App Configuration
APP_NAME="API Dashboard Aggregator"
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=api_dashboard
DB_USERNAME=sail
DB_PASSWORD=password

# Cache Configuration
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Redis Configuration
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# MeiliSearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=masterKey

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Docker Configuration
WWWGROUP=1000
WWWUSER=1000

# Port Configuration
APP_PORT=80
FORWARD_DB_PORT=3306
FORWARD_REDIS_PORT=6379
FORWARD_MEILISEARCH_PORT=7700
FORWARD_MAILHOG_PORT=1025
FORWARD_MAILHOG_DASHBOARD_PORT=8025
VITE_PORT=5173
NGINX_PORT=8080

# API Keys (External Services)
OPENWEATHER_API_KEY=your_openweather_key
NEWS_API_KEY=your_news_api_key
GITHUB_TOKEN=your_github_token
COINGECKO_API_URL=https://api.coingecko.com/api/v3
```

### 3. Dockerfile Konfiguration

#### docker/8.2/Dockerfile
```dockerfile
FROM laravelsail/php82-composer:latest

ARG WWWGROUP

# Install additional PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor \
    cron \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd xml

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install Composer dependencies
COPY . /var/www/html
WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && usermod -u 1000 www-data \
    && groupmod -g 1000 www-data

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### 4. Nginx Konfiguration (Optional)

#### docker/nginx/nginx.conf
```nginx
events {
    worker_connections 1024;
}

http {
    upstream app {
        server app:80;
    }

    server {
        listen 80;
        server_name localhost;
        root /var/www/html/public;
        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass app:80;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.ht {
            deny all;
        }
    }
}
```

### 5. Supervisor Konfiguration

#### docker/supervisor/supervisord.conf
```ini
[supervisord]
nodaemon=true
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
priority=5
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

## Befehle

### Basis-Operationen
```bash
# Container starten
./vendor/bin/sail up

# Container im Hintergrund starten
./vendor/bin/sail up -d

# Container stoppen
./vendor/bin/sail down

# Container neu bauen
./vendor/bin/sail build --no-cache

# Logs anzeigen
./vendor/bin/sail logs

# Logs für spezifischen Service
./vendor/bin/sail logs app
./vendor/bin/sail logs mysql
./vendor/bin/sail logs redis
```

### Development Befehle
```bash
# Laravel Artisan Befehle
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tinker
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan schedule:work

# Composer
./vendor/bin/sail composer install
./vendor/bin/sail composer update

# NPM
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
./vendor/bin/sail npm run build

# PHP
./vendor/bin/sail php --version
./vendor/bin/sail php -v
```

### Database Befehle
```bash
# Migrationen ausführen
./vendor/bin/sail artisan migrate:fresh --seed

# Database Dump erstellen
./vendor/bin/sail exec mysql mysqldump -u root -p api_dashboard > backup.sql

# Database wiederherstellen
./vendor/bin/sail exec -T mysql mysql -u root -p api_dashboard < backup.sql
```

## Development Workflow

### 1. Initial Setup
```bash
# Projekt klonen
git clone <repository-url>
cd api-dashboard-aggregator

# Dependencies installieren
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# Environment setup
cp .env.example .env
./vendor/bin/sail artisan key:generate

# Database setup
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

### 2. Development
```bash
# Start alle Services
./vendor/bin/sail up -d

# Watch für Frontend Assets
./vendor/bin/sail npm run dev

# Queue Worker (in neuem Terminal)
./vendor/bin/sail artisan queue:work

# Scheduler (in neuem Terminal)
./vendor/bin/sail artisan schedule:work
```

### 3. Testing
```bash
# Unit Tests
./vendor/bin/sail artisan test

# Feature Tests
./vendor/bin/sail artisan test --testsuite=Feature

# Database Testing
./vendor/bin/sail artisan test --testsuite=Unit --filter=DatabaseTest
```

## Production Deployment

### 1. Production Build
```bash
# Production Dependencies
./vendor/bin/sail composer install --optimize-autoloader --no-dev
./vendor/bin/sail npm run build

# Cache optimieren
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
./vendor/bin/sail artisan view:cache
```

### 2. Security
```bash
# Environment optimieren
cp .env .env.production

# App Key generieren
./vendor/bin/sail artisan key:generate --env=production

# Storage verlinken
./vendor/bin/sail artisan storage:link
```

### 3. Monitoring
```bash
# Health Checks
./vendor/bin/sail artisan about
./vendor/bin/sail artisan route:list
./vendor/bin/sail artisan schedule:list
```

## Troubleshooting

### Häufige Probleme

#### 1. Port Conflicts
```bash
# Andere Ports verwenden
APP_PORT=8080
FORWARD_DB_PORT=3307
```

#### 2. Permission Issues
```bash
# Berechtigungen korrigieren
sudo chown -R $USER:$USER .
./vendor/bin/sail artisan storage:link
```

#### 3. Memory Issues
```bash
# PHP Memory erhöhen
# In php.ini oder .env hinzufügen:
PHP_MEMORY_LIMIT=512M
```

#### 4. Database Connection
```bash
# MySQL neustarten
./vendor/bin/sail restart mysql

# Database Logs prüfen
./vendor/bin/sail logs mysql
```

### Performance Optimierung

#### 1. Docker Optimierung
```yaml
# docker-compose.yml Optimierungen
services:
    app:
        # Multi-stage build für Production
        build:
            target: production
        
        # Resource Limits
        deploy:
            resources:
                limits:
                    cpus: '1.0'
                    memory: 1G
```

#### 2. Volume Optimierung
```yaml
volumes:
    # Named volumes für Persistenz
    sail-mysql:
        driver: local
    sail-redis:
        driver: local
```

#### 3. Network Optimierung
```yaml
networks:
    sail:
        driver: bridge
        ipam:
            config:
                - subnet: 172.20.0.0/16
```

## Backup & Restore

### 1. Database Backup
```bash
# Automatisches Backup Script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
./vendor/bin/sail exec mysql mysqldump -u root -p api_dashboard > backup_$DATE.sql
```

### 2. Volume Backup
```bash
# Docker Volumes sichern
docker run --rm -v sail-mysql:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup.tar.gz -C /data .
```

### 3. Configuration Backup
```bash
# Environment & Config sichern
cp .env .env.backup
cp docker-compose.yml docker-compose.yml.backup
```

Dieses Setup bietet eine complete Docker-Umgebung für Development, Testing und Production des API Dashboard Aggregators.