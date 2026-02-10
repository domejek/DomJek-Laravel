# GitHub Actions Dokumentation

Diese Dokumentation beschreibt alle GitHub Actions Workflows für das DomJek Laravel Projekt.

## Inhaltsverzeichnis

1. [Übersicht](#übersicht)
2. [CI/CD Pipeline](#cicd-pipeline)
3. [Deployment](#deployment)
4. [Dependency Management](#dependency-management)
5. [Database Management](#database-management)
6. [Performance Monitoring](#performance-monitoring)
7. [Konfiguration](#konfiguration)
8. [Troubleshooting](#troubleshooting)

## Übersicht

Das Projekt verwendet 6 Haupt-Workflows zur Automatisierung verschiedener Aspekte des Entwicklungs- und Deployment-Prozesses:

- **CI/CD Pipeline**: Kontinuierliche Integration und Lieferung
- **Deployment**: Automatisierte Deployment-Prozesse
- **Dependency Management**: Verwaltung von Abhängigkeiten
- **Database Management**: Datenbank-Operationen und Migrationen
- **Performance Monitoring**: Leistungsüberwachung und Optimierung

## CI/CD Pipeline

### Workflow: `.github/workflows/ci.yml`

**Trigger:**
- Push auf `main` und `develop` Branches
- Pull Requests auf `main` und `develop`

**Jobs:**

#### Test Job
- **Umgebung**: Ubuntu mit MySQL 8.0 und Redis 7
- **PHP Version**: 8.2
- **Node.js Version**: 20
- **Schritte**:
  1. Code Checkout
  2. PHP Setup mit Extensions
  3. Environment Konfiguration
  4. Composer Installation
  5. Datenbank Setup
  6. Laravel Pint (Code Style Checks)
  7. PHPUnit Tests mit Coverage
  8. Frontend Build mit Vite
  9. Security Audits (PHP & NPM)

#### Quality Job
- **Abhängigkeit**: Test Job
- **Schritte**:
  1. Static Code Analyse mit Laravel Stan
  2. Secret Detection mit TruffleHog

#### Build Job
- **Bedingung**: Nur auf `main` Branch
- **Abhängigkeit**: Test & Quality Jobs
- **Schritte**:
  1. Frontend Build
  2. Artifact Upload

## Deployment

### Workflow: `.github/workflows/deploy.yml`

**Trigger:**
- Nach erfolgreichem CI/CD Pipeline Run
- Manuelle Ausführung

**Jobs:**

#### Staging Deployment
- **Trigger**: Push auf `develop` Branch oder manuelle Auswahl
- **Features**:
  - Composer Install ohne Dev-Dependencies
  - Frontend Production Build
  - Automated Deployment via SSH
  - Health Checks
  - Slack Benachrichtigungen

#### Production Deployment
- **Trigger**: Push auf `main` Branch oder manuelle Auswahl
- **Features**:
  - Backup vor Deployment
  - Blue-Green Deployment
  - Rollback bei Fehlern
  - Umfassende Health Checks
  - Slack Benachrichtigungen

#### Smoke Tests
- **Abhängigkeit**: Staging Deployment
- **Tests**:
  - API Endpoints
  - Performance Checks

## Dependency Management

### Workflow: `.github/workflows/dependencies.yml`

**Trigger:**
- Wöchentlich (Montag 2:00 Uhr)
- Manuelle Ausführung

**Jobs:**

#### Security Audit
- **Funktionen**:
  - Composer Security Audit
  - NPM Security Audit
  - Snyk Integration
  - Automated Reports

#### Dependency Update
- **Funktionen**:
  - Automated Updates (Patch/Minor/Major)
  - Test nach Updates
  - Pull Request Erstellung
  - Git Konfiguration

#### License Check
- **Funktionen**:
  - PHP License Analyse
  - Node.js License Analyse
  - Policy Validation
  - Compliance Reports

#### Dependency Graph
- **Funktionen**:
  - Dependency Tree Generation
  - Visual Representation
  - Documentation

## Database Management

### Workflow: `.github/workflows/database.yml`

**Trigger:**
- Änderungen an Database-Files
- Pull Requests
- Wöchentlich (Sonntag 3:00 Uhr)
- Manuelle Ausführung

**Jobs:**

#### Migration Testing
- **Features**:
  - Multi-Database Support (MySQL, PostgreSQL, SQLite)
  - Migration Validation
  - Rollback Testing
  - Seeder Testing

#### Database Backup
- **Features**:
  - Environment-spezifische Backups
  - Artifact Storage
  - Retention Management

#### Data Seeding
- **Features**:
  - Test Data Generation
  - Environment-spezifische Seeders

#### Cleanup Operations
- **Features**:
  - Model Pruning
  - Cache Clearing
  - Queue Management

## Performance Monitoring

### Workflow: `.github/workflows/performance.yml`

**Trigger:**
- Frontend-Änderungen
- Pull Requests
- Regelmäßig (Mo, Mi, Fr 6:00 Uhr)
- Manuelle Ausführung

**Jobs:**

#### Bundle Analysis
- **Features**:
  - Vite Build Analyse
  - Bundle Size Tracking
  - Large Asset Detection
  - Webpack Bundle Analyzer

#### Lighthouse Audit
- **Features**:
  - Performance Scoring
  - SEO Analysis
  - Best Practices
  - Accessibility Checks

#### Load Testing
- **Features**:
  - k6 Integration
  - Multi-Stage Load Tests
  - Response Time Validation
  - Performance Regression Detection

#### Performance Regression
- **Features**:
  - Bundle Size Comparison
  - PR Comments
  - Automated Alerts

## Konfiguration

### Erforderliche Secrets

#### Environment Secrets
- `STAGING_HOST` - Staging Server Host
- `STAGING_USERNAME` - SSH Username für Staging
- `STAGING_SSH_KEY` - SSH Private Key für Staging
- `STAGING_URL` - Staging Application URL

- `PRODUCTION_HOST` - Production Server Host
- `PRODUCTION_USERNAME` - SSH Username für Production
- `PRODUCTION_SSH_KEY` - SSH Private Key für Production
- `PRODUCTION_URL` - Production Application URL

#### Integration Secrets
- `SLACK_WEBHOOK` - Slack Webhook URL für Benachrichtigungen
- `LHCI_GITHUB_APP_TOKEN` - Lighthouse CI Token
- `SNYK_TOKEN` - Snyk Security Token

### Environment Files

**`.env.staging`**: Staging Konfiguration
**`.env.production`**: Production Konfiguration
**`.env.testing`**: Test Konfiguration

### Server Requirements

#### Staging Server
- PHP 8.2 mit Extensions
- Node.js 20
- MySQL 8.0 oder PostgreSQL 15
- Redis 7
- Nginx oder Apache

#### Production Server
- Gleiche Requirements wie Staging
- Backup Storage
- Monitoring Tools

## Best Practices

### Branch Strategy
- `main` - Production Code
- `develop` - Development Code
- Feature Branches für neue Features
- Pull Requests für Code Reviews

### Security
- Regelmäßige Security Audits
- Secret Management
- Dependency Scanning
- Code Analysis

### Performance
- Bundle Size Monitoring
- Load Testing
- Lighthouse Audits
- Performance Budgets

### Monitoring
- Health Checks
- Uptime Monitoring
- Error Tracking
- Performance Metrics

## Troubleshooting

### Häufige Probleme

#### CI/CD Pipeline Failures
1. **Test Failures**: Prüfe Logs in GitHub Actions Tab
2. **Build Errors**: Überprüfe Composer/NPM Dependencies
3. **Security Issues**: Fix vulnerable Dependencies
4. **Code Style**: Laravel Pint Errors beheben

#### Deployment Issues
1. **SSH Connection**: Verify SSH Keys und Host Access
2. **Environment Issues**: Check Environment Variables
3. **Database Problems**: Verify Database Connection
4. **Permission Errors**: Check File Permissions

#### Performance Issues
1. **Bundle Size**: Optimize Assets
2. **Load Test Failures**: Check Server Resources
3. **Lighthouse Scores**: Fix Performance Issues
4. **Regression**: Compare with previous builds

### Debugging

#### Logs
- GitHub Actions Logs für Workflow Details
- Server Logs für Deployment Issues
- Application Logs für Runtime Errors
- Performance Logs für Optimization

#### Monitoring
- Real-time Health Checks
- Performance Dashboards
- Error Tracking Services
- Uptime Monitoring

### Support

Für Fragen und Issues:
1. GitHub Issues erstellen
2. Slack Channel nutzen
3. Dokumentation konsultieren
4. Team kontaktieren

## Weiterentwicklung

### Geplante Features
- Container-based Deployments
- Multi-Environment Support
- Advanced Monitoring
- Automated Rollbacks

### Verbesserungen
- Workflow Optimierung
- Performance Enhancement
- Security Hardening
- Documentation Updates