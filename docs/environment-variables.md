# Environment Variables Reference

Dieses Dokument beschreibt alle Environment Variables die in den GitHub Actions Workflows verwendet werden.

## System Environment Variables

### PHP und Node.js Versionen
```yaml
env:
  NODE_VERSION: '20'
  PHP_VERSION: '8.2'
```

### Dateipfade
```yaml
# Backend Verzeichnis
working-directory: ./backend

# Build Verzeichnis
path: backend/public/build/

# Test Coverage
path: ./coverage.xml

# Database für Tests
MYSQL_DATABASE: testing
POSTGRES_DB: testing
```

## CI/CD Pipeline Environment

### Test Environment
```env
# Database Connection (Testing)
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# Laravel Testing
APP_ENV=testing
APP_DEBUG=true
LOG_CHANNEL=stack
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
```

### Services Configuration
```yaml
services:
  mysql:
    image: mysql:8.0
    env:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: testing
    ports:
      - 3306:3306
    options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

  redis:
    image: redis:7
    ports:
      - 6379:6379
    options: --health-cmd="redis-cli ping" --health-interval=10s --health-timeout=5s --health-retries=3

  postgres:
    image: postgres:15
    env:
      POSTGRES_PASSWORD: password
      POSTGRES_DB: testing
    ports:
      - 5432:5432
    options: --health-cmd="pg_isready" --health-interval=10s --health-timeout=5s --health-retries=3
```

## Deployment Environment Variables

### GitHub Secrets (Required)

#### Staging Environment
```bash
STAGING_HOST=staging.yourserver.com
STAGING_USERNAME=deploy
STAGING_SSH_KEY=-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----
STAGING_URL=https://staging.yourapp.com
```

#### Production Environment
```bash
PRODUCTION_HOST=production.yourserver.com
PRODUCTION_USERNAME=deploy
PRODUCTION_SSH_KEY=-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----
PRODUCTION_URL=https://yourapp.com
```

#### Integration Services
```bash
SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
LHCI_GITHUB_APP_TOKEN=v1.YOUR_LHCI_TOKEN
SNYK_TOKEN=YOUR_SNYK_TOKEN
```

### Application Environment Files

#### `.env.staging`
```env
# Application
APP_NAME=DomJek
APP_ENV=staging
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_URL=https://staging.yourapp.com
APP_VERSION=${GITHUB_SHA}

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domjek_staging
DB_USERNAME=staging_user
DB_PASSWORD=staging_password

# Cache
BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

#### `.env.production`
```env
# Application
APP_NAME=DomJek
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourapp.com
APP_VERSION=${GITHUB_SHA}

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domjek_production
DB_USERNAME=production_user
DB_PASSWORD=production_password

# Cache
BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=YOUR_REDIS_PASSWORD
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourapp.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourapp.com"
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

# Security
FORCE_HTTPS=true
```

#### `.env.testing`
```env
# Application
APP_NAME=DomJek
APP_ENV=testing
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# Cache
BROADCAST_DRIVER=log
CACHE_DRIVER=array
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
SESSION_LIFETIME=120

# Redis (disabled for testing)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (disabled for testing)
MAIL_MAILER=array
MAIL_HOST=null
MAIL_PORT=null
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="test@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

## Database Management Variables

### Multi-Database Support
```yaml
strategy:
  matrix:
    database: [mysql, postgres, sqlite]
```

### Database Configuration Matrix
```yaml
# MySQL Configuration
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i 's/DB_DATABASE=database\/database.sqlite/DB_DATABASE=testing/' .env
sed -i 's/DB_USERNAME=root/DB_USERNAME=root/' .env
sed -i 's/DB_PASSWORD=/DB_PASSWORD=password/' .env

# PostgreSQL Configuration
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=pgsql/' .env
sed -i 's/DB_DATABASE=database\/database.sqlite/DB_DATABASE=testing/' .env
sed -i 's/DB_USERNAME=root/DB_USERNAME=postgres/' .env
sed -i 's/DB_PASSWORD=/DB_PASSWORD=password/' .env

# SQLite Configuration (default)
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

## Performance Monitoring Variables

### Lighthouse CI Configuration
```env
LHCI_GITHUB_APP_TOKEN=v1.xxxxxx.xxxxxx
LHCI_BUILD_CONTEXT=${GITHUB_REF}
```

### Load Testing Configuration
```yaml
# k6 Configuration
export let options = {
  stages: [
    { duration: '2m', target: 100 },
    { duration: '5m', target: 100 },
    { duration: '2m', target: 0 },
  ],
};
```

### Bundle Analysis
```yaml
# Node.js Configuration
node-version: '20'
cache: 'npm'
working-directory: ./backend
```

## Security Variables

### Snyk Configuration
```bash
SNYK_TOKEN=xxxx-xxxx-xxxx-xxxx
```

### Secret Detection
```yaml
# TruffleHog Configuration
path: ./
base: main
head: HEAD
```

### License Checking
```env
# License Compliance
ALLOWED_LICENSES=MIT,Apache-2.0,BSD-2-Clause,BSD-3-Clause
RESTRICTED_LICENSES=GPL-3.0,AGPL-3.0
```

## Custom Workflow Variables

### Deployment Options
```yaml
# Workflow Dispatch Inputs
environment:
  description: 'Deployment environment'
  required: true
  default: 'staging'
  type: choice
  options:
  - staging
  - production

force_deploy:
  description: 'Force deployment (skip health checks)'
  required: false
  default: false
  type: boolean
```

### Dependency Management
```yaml
update_type:
  description: 'Type of update'
  required: true
  default: 'patch'
  type: choice
  options:
  - patch
  - minor
  - major

php_only:
  description: 'Update PHP packages only'
  required: false
  default: false
  type: boolean
```

### Database Operations
```yaml
operation:
  description: 'Database operation'
  required: true
  default: 'test-migrations'
  type: choice
  options:
  - test-migrations
  - backup
  - seed-test-data
  - cleanup

environment:
  description: 'Target environment'
  required: true
  default: 'staging'
  type: choice
  options:
  - staging
  - production
```

### Performance Testing
```yaml
test_type:
  description: 'Performance test type'
  required: true
  default: 'load'
  type: choice
  options:
  - load
  - bundle
  - lighthouse
  - all
```

## Artifact Configuration

### Retention Periods
```yaml
# Build Artifacts
retention-days: 30

# Security Reports
retention-days: 30

# Performance Reports
retention-days: 30

# Database Backups
retention-days: 30
```

### Artifact Paths
```yaml
# Build Assets
path: backend/public/build/

# Test Coverage
path: ./coverage.xml

# Security Reports
path: |
  security-report.md
  php-audit.json
  npm-audit.json

# Performance Reports
path: |
  backend/bundle-report.html
  backend/bundle-size-report.md
```

## Debug Variables

### Git Configuration
```bash
git config --local user.email "action@github.com"
git config --local user.name "GitHub Action"
```

### Debug Mode
```yaml
# Enable verbose logging
ACTIONS_STEP_DEBUG=true
ACTIONS_RUNNER_DEBUG=true
```

## Monitoring Variables

### Health Check Endpoints
```bash
# Default health check
/health

# API health check
/api/health

# Database health check
/api/health/database

# Cache health check
/api/health/cache
```

### Performance Metrics
```yaml
# Response time thresholds
RESPONSE_TIME_WARNING=500ms
RESPONSE_TIME_CRITICAL=2000ms

# Error rate thresholds
ERROR_RATE_WARNING=5%
ERROR_RATE_CRITICAL=10%
```

## Compliance Variables

### Data Protection
```env
# GDPR Compliance
DATA_RETENTION_DAYS=2555
ANONYMIZATION_ENABLED=true
```

### Security Headers
```env
# Security Headers
FORCE_HTTPS=true
STRICT_TRANSPORT_SECURITY=true
CONTENT_SECURITY_POLICY=true
X_FRAME_OPTIONS=DENY
X_CONTENT_TYPE_OPTIONS=nosniff
```

## Troubleshooting Variables

### Debug Configuration
```yaml
# Enable debug mode
APP_DEBUG=true

# Increase memory limits
memory_limit: 2G

# Extended timeouts
timeout: 600000
```

### Log Levels
```env
# Laravel Log Levels
LOG_LEVEL=debug

# Application Logs
LOG_CHANNEL=stack
LOG_STACK=single
```

## Version Management

### Semantic Versioning
```env
# Version Configuration
MAJOR_VERSION=1
MINOR_VERSION=0
PATCH_VERSION=0

# Build Information
BUILD_NUMBER=${GITHUB_RUN_NUMBER}
BUILD_DATE=${GITHUB_TIMESTAMP}
GIT_COMMIT=${GITHUB_SHA}
GIT_BRANCH=${GITHUB_REF_NAME}
```

### Release Configuration
```yaml
# Release Pattern
release_branches: [main, develop]

# Tag Pattern
tag_pattern: 'v[0-9]+\.[0-9]+\.[0-9]+'
```