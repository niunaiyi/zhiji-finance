# Technology Stack

**Analysis Date:** 2025-05-22

## Languages

**Primary:**
- PHP 8.2 - Backend core logic, API development, and framework infrastructure.
- TypeScript 5.9 - Frontend application logic, type safety, and component definitions.

**Secondary:**
- JavaScript - Build scripts and legacy/utility configurations.
- CSS/PostCSS - Styling via Tailwind CSS framework.
- Blade - Limited usage for potential server-rendered views or layouts (though mainly an API-centric app).

## Runtime

**Environment:**
- PHP 8.2+
- Node.js (Version managed via `.nvmrc` if present, otherwise recent LTS recommended for Vite 7).

**Package Manager:**
- Composer 2.x - Backend dependency management.
  - Lockfile: `composer.lock` present.
- npm - Frontend dependency management.
  - Lockfile: `frontend/package-lock.json` present.

## Frameworks

**Core:**
- Laravel 11 - Underlying PHP framework.
- Apiato 13 - Architectural framework on top of Laravel, implementing the "Porto" pattern.
- React 19.2 - Frontend library for building the user interface.

**Testing:**
- PHPUnit 11 - Primary backend testing framework.
- Playwright 1.58 - Frontend end-to-end (E2E) testing.
- Mockery 1.4 - Mocking framework for PHP tests.

**Build/Dev:**
- Vite 7.3 - Frontend build tool and development server.
- Laravel Vite Plugin 1.2 - Integration between Laravel and Vite.
- Tailwind CSS 4.1 - Utility-first CSS framework.

## Key Dependencies

**Critical:**
- `apiato/core` ^13.1 - Provides the Porto architecture and core API functionalities.
- `laravel/passport` ^13.0 - OAuth2 authentication and API guard implementation.
- `spatie/laravel-permission` ^6.0 - Role-based access control (RBAC).
- `antd` ^6.3.0 - Ant Design UI component library for React.
- `zustand` ^5.0.11 - Lightweight state management for the frontend.
- `axios` ^1.13.5 - Promise-based HTTP client for API requests.

**Infrastructure:**
- `wikimedia/composer-merge-plugin` ^2.1 - Merges container-specific `composer.json` files.
- `fractal` (via Apiato) - Data transformation layer for API responses.
- `hashids` - ID obfuscation for API endpoints.

## Configuration

**Environment:**
- Managed via `.env` files (see `.env.example` for required keys).
- Key configs: `APP_KEY`, `DB_CONNECTION`, `PASSPORT_PRIVATE_KEY`, `PASSPORT_PUBLIC_KEY`.

**Build:**
- `vite.config.ts` - Frontend build configuration.
- `composer.json` - Backend dependency and script configuration.
- `tailwind.config.js` - Styling configuration.

## Platform Requirements

**Development:**
- PHP 8.2 or higher.
- MySQL/SQLite/PostgreSQL/MariaDB.
- Node.js & npm.
- Composer.

**Production:**
- Linux-based environment (recommended).
- Web Server (Nginx/Apache) with PHP-FPM.
- Database (MySQL/PostgreSQL recommended).
- Redis (optional, for caching and queues).

---

*Stack analysis: 2025-05-22*
