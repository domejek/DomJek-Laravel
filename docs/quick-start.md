# Quick Start Guide

## Schnelleinstieg in GitHub Actions

### 1. Voraussetzungen

- GitHub Repository mit Admin-Rechten
- Server-Zugang (SSH) für Deployment
- GitHub Personal Access Token
- Slack Webhook (optional)

### 2. Erste Schritte

#### Repository Konfiguration
```bash
# GitHub Actions aktivieren
# Settings → Actions → General → Actions permissions
```

#### Secrets einrichten
Gehe zu: `Settings → Secrets and variables → Actions`

**Erforderliche Secrets:**
```
STAGING_HOST=your-staging-server.com
STAGING_USERNAME=deploy
STAGING_SSH_KEY=-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----
STAGING_URL=https://staging.yourapp.com

PRODUCTION_HOST=your-production-server.com
PRODUCTION_USERNAME=deploy
PRODUCTION_SSH_KEY=-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----
PRODUCTION_URL=https://yourapp.com

SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
```

### 3. Environment Dateien erstellen

#### `.env.staging`
```env
APP_NAME=DomJek
APP_ENV=staging
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=https://staging.yourapp.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domjek_staging
DB_USERNAME=staging_user
DB_PASSWORD=staging_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### `.env.production`
```env
APP_NAME=DomJek
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://yourapp.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domjek_production
DB_USERNAME=production_user
DB_PASSWORD=production_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Server Setup

#### Staging Server
```bash
# User erstellen
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG sudo deploy

# SSH Keys einrichten
sudo mkdir -p /home/deploy/.ssh
sudo chmod 700 /home/deploy/.ssh
sudo echo "your-public-key" >> /home/deploy/.ssh/authorized_keys
sudo chown -R deploy:deploy /home/deploy/.ssh

# Verzeichnis erstellen
sudo mkdir -p /var/www/staging
sudo chown -R deploy:deploy /var/www/staging
```

#### Production Server
Gleiche Schritte wie Staging, aber mit `/var/www/production`

### 5. Deployment Scripts

#### `/var/www/staging/scripts/backup.sh`
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/staging"
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u staging_user -p'staging_password' domjek_staging > $BACKUP_DIR/db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/staging

# Clean old backups (keep 7 days)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

#### `/var/www/production/scripts/blue-green-deploy.sh`
```bash
#!/bin/bash
set -e

CURRENT_DIR="/var/www/production"
RELEASE_DIR="/var/www/releases/$(date +%Y%m%d_%H%M%S)"
SHARED_DIR="/var/www/shared"
LIVE_DIR="/var/www/live"

# Create new release
mkdir -p $RELEASE_DIR
git clone https://github.com/your-username/DomJek-Laravel.git $RELEASE_DIR

# Link shared directories
ln -nfs $SHARED_DIR/.env $RELEASE_DIR/.env
ln -nfs $SHARED_DIR/storage $RELEASE_DIR/storage
ln -nfs $SHARED_DIR/bootstrap/cache $RELEASE_DIR/bootstrap/cache

# Install dependencies
cd $RELEASE_DIR
composer install --no-dev --optimize-autoloader
cd backend && npm ci --production && npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Switch to new release (atomic)
ln -nfs $RELEASE_DIR $LIVE_DIR

# Restart services
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart redis-server
php artisan queue:restart

echo "Deployment successful!"
```

#### `/var/www/production/scripts/rollback.sh`
```bash
#!/bin/bash
set -e

RELEASE_DIR="/var/www/releases"
LIVE_DIR="/var/www/live"

# Get current release
CURRENT_RELEASE=$(readlink -f $LIVE_DIR)

# Get previous release
PREVIOUS_RELEASE=$(ls -t $RELEASE_DIR | grep -B1 $(basename $CURRENT_RELEASE) | head -n1)

if [ -z "$PREVIOUS_RELEASE" ]; then
    echo "No previous release found"
    exit 1
fi

# Switch to previous release
ln -nfs $RELEASE_DIR/$PREVIOUS_RELEASE $LIVE_DIR

# Restart services
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm
php artisan queue:restart

echo "Rollback to $PREVIOUS_RELEASE completed"
```

### 6. Erster Test

#### CI/CD Pipeline testen
```bash
# Änderung an main Branch pushen
git add .
git commit -m "test: initial GitHub Actions setup"
git push origin main
```

#### Deployment testen
```bash
# Manual trigger via GitHub UI
# Actions → Deployments → Run workflow
```

### 7. Monitoring

#### Health Check Endpoint erstellen
```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version'),
        'environment' => config('app.env'),
    ]);
});
```

#### Uptime Check
```bash
# In crontab
*/5 * * * * curl -f https://yourapp.com/api/health
```

### 8. Häufige Probleme & Lösungen

#### Permission Denied
```bash
sudo chown -R deploy:deploy /var/www/staging
sudo chmod -R 755 /var/www/staging
```

#### Composer Memory Limit
```bash
php -d memory_limit=2G /usr/local/bin/composer install
```

#### Node.js Version
```bash
# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 9. Best Practices

#### Security
- SSH Keys nur für Deployment User
- Database User mit minimalen Rechten
- Regelmäßige Secret Rotation
- Environment Variablen nicht im Code speichern

#### Performance
- Composer Autoloader optimieren
- Laravel Caching nutzen
- CDN für Assets
- Database Indexes

#### Monitoring
- Error Tracking (Sentry, Bugsnag)
- Performance Monitoring (New Relic, DataDog)
- Log Aggregation (ELK Stack)
- Uptime Monitoring (UptimeRobot)

### 10. Support

#### Dokumentation
- [Full Documentation](./github-actions.md)
- [GitHub Actions Docs](https://docs.github.com/en/actions)
- [Laravel Deployment](https://laravel.com/docs/deployment)

#### Community
- GitHub Issues
- Laravel Discord
- Stack Overflow

#### Team
- DevOps Team für Server Issues
- Development Team für Code Issues
- Security Team für Security Concerns