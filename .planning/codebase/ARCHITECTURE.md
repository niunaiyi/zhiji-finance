# Architecture Overview

This document outlines the architectural patterns and high-level design of the Zhiji Finance project.

## Core Architectural Pattern: Porto SAP
The project follows the **Software Architecture Pattern (Porto)**, implemented through the **Apiato** framework. This pattern emphasizes a highly modular, container-based approach to Laravel development.

### Primary Layers

1.  **Ship Layer (`app/Ship`)**
    *   Contains shared infrastructure, parent classes, and common logic used across all containers.
    *   Includes base Controllers, Models, Actions, and Tasks that domain-specific components extend.
    *   Houses core Middleware (e.g., `SwitchTenantMiddleware.php`) and Providers.

2.  **Containers Layer (`app/Containers`)**
    *   The core business logic is divided into **Containers** (domain modules).
    *   Each container is a self-contained unit following a standard internal structure:
        *   **UI/API**: Entry points for HTTP requests (Controllers, Requests, Routes).
        *   **Actions**: Orchestrators that execute a specific use case (e.g., `ClosePeriodAction`). They are the only entry point into the business logic.
        *   **Tasks**: Small, reusable logic units (e.g., `FindAccountByCodeTask`). Actions call multiple Tasks.
        *   **Models**: Eloquent models and database migrations.
        *   **Data/Repositories**: Data access layer (though often combined with Models in this project).
        *   **Mails/Notifications/Events/Listeners**: Domain-specific communication.

## Frontend Architecture
The frontend is a modern **React Single Page Application (SPA)** located in the `frontend/` directory.

*   **Build Tool**: Vite.
*   **Routing**: React Router (implied by standard SPA structure).
*   **State Management**: React Context API for global state (e.g., Authentication).
*   **API Communication**: Axios with interceptors for handling tenant headers (`X-Company-Id`) and authentication tokens.
*   **Styling**: Vanilla CSS (as per project conventions).

## Key Domain Hubs
*   **Finance Container**: The primary engine for accounting logic (General Ledger, Vouchers, Reports).
*   **AppSection**: Likely handles general application features (Authentication, Users).
*   **Vendor**: Custom integrations or third-party logic wrappers.

## Data Isolation (Multi-tenancy)
The system implements multi-tenancy based on `company_id`. Tenant switching is handled via a `SwitchTenantMiddleware.php` that reads an `X-Company-Id` header from incoming requests.
