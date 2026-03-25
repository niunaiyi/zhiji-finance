# External Integrations

**Analysis Date:** 2025-05-22

## APIs & External Services

**Email:**
- Postmark - Transactional email delivery.
  - SDK/Client: Laravel Core (integrated).
  - Auth: `POSTMARK_TOKEN`.
- Amazon SES - AWS-based email service.
  - SDK/Client: Laravel Core (AWS SDK requirement).
  - Auth: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`.
- Resend - Modern email API.
  - SDK/Client: Laravel integration.
  - Auth: `RESEND_KEY`.

**Messaging:**
- Slack Notifications - Webhook/Bot integration for notifications.
  - SDK/Client: Laravel Slack Notification Channel.
  - Auth: `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL`.

## Data Storage

**Databases:**
- SQLite (Default development/testing) - File-based database.
- MySQL / MariaDB (Production/Standard) - Scalable relational database.
- PostgreSQL / SQL Server (Supported) - Enterprise-ready databases.
  - Connection: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
  - Client: Eloquent ORM (Laravel standard).

**File Storage:**
- Local Filesystem - Default file storage for private/public assets.
- Amazon S3 - Cloud storage integration for scalable file management.
  - Service: `S3` driver.
  - Connection: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_REGION`, `AWS_ENDPOINT`.

**Caching:**
- Redis - High-performance key-value store for caching and queues.
  - Client: `phpredis`.
  - Connection: `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`, `REDIS_DB`.
- Local Cache - File-based cache as a fallback.

## Authentication & Identity

**Auth Provider:**
- Custom OAuth2 Server - Implemented using Laravel Passport.
  - Implementation: `Passport` API guard with JWT-based access tokens.
  - Storage: OAuth clients, access tokens, and refresh tokens are stored in the database.

**Role-Based Access Control (RBAC):**
- Spatie Permissions - Manages roles and permissions for users.
  - Implementation: User models use `HasRoles` and `HasPermissions` traits.

## Monitoring & Observability

**Error Tracking:**
- Laravel Debugbar - Developer-centric debugging tool.
  - Files: `app/Ship/Configs/debugbar.php`.

**Logs:**
- Laravel Logging - Supports various drivers (single, daily, slack, syslog).
  - Implementation: `Log` facade in PHP code.
  - Config: `config/logging.php`.

## CI/CD & Deployment

**Hosting:**
- Self-hosted on PHP-enabled servers or platforms (e.g., DigitalOcean, AWS, Forge).

**CI Pipeline:**
- PHPUnit - Unit and integration testing as part of CI workflows.
- Playwright - Frontend E2E testing in CI.

## Environment Configuration

**Required env vars:**
- `APP_KEY` - Encryption key for the application.
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - Database credentials.
- `PASSPORT_PRIVATE_KEY`, `PASSPORT_PUBLIC_KEY` - OAuth2 signing keys.
- `APP_URL` - Root URL for the application.

**Secrets location:**
- `.env` file for local development.
- Environment-level variables for production deployments.

## Webhooks & Callbacks

**Incoming:**
- `passport` endpoints: `/oauth/token`, `/oauth/authorize` (standard Laravel Passport endpoints).

**Outgoing:**
- Slack Notifications - Sending alerts or updates to Slack channels.

---

*Integration audit: 2025-05-22*
