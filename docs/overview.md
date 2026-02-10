# GitHub Actions Documentation

## Overview

This directory contains comprehensive documentation for all GitHub Actions workflows in the DomJek Laravel project.

## Documentation Files

### 1. [GitHub Actions Full Documentation](./github-actions.md)
Complete documentation of all workflows, jobs, and configuration options.

### 2. [Quick Start Guide](./quick-start.md)
Step-by-step guide to set up GitHub Actions for your project.

### 3. [Environment Variables Reference](./environment-variables.md)
Detailed reference for all environment variables and secrets.

## Workflow Structure

```
.github/workflows/
├── ci.yml              # CI/CD Pipeline
├── deploy.yml           # Deployment Automation
├── dependencies.yml     # Dependency Management
├── database.yml         # Database Operations
└── performance.yml      # Performance Monitoring
```

## Getting Started

1. Read the [Quick Start Guide](./quick-start.md) for initial setup
2. Configure required secrets and environment variables
3. Test workflows with a sample commit
4. Reference the [Full Documentation](./github-actions.md) for advanced configuration

## Support

- Check [Troubleshooting](./github-actions.md#troubleshooting) for common issues
- Review [Environment Variables](./environment-variables.md) for configuration
- Follow [Best Practices](./github-actions.md#best-practices) for optimal usage

## Security

Keep your secrets secure:
- Use GitHub Secrets for sensitive data
- Rotate keys regularly
- Follow principle of least privilege
- Monitor access logs

## Monitoring

- Monitor workflow runs in GitHub Actions tab
- Set up notifications for failures
- Review performance metrics regularly
- Track deployment success rates