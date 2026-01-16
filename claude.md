You are assisting with the development of a Laravel package tentatively called “insight-api”.

The goal of insight-api is to provide runtime insight into how Laravel API endpoints are actually used in practice, by capturing real HTTP traffic via explicit, opt-in middleware.

This package is middleware-first and opt-in by design. Nothing should be automatically enabled or invasive. Users explicitly attach middleware to routes or route groups they want to observe.

Core goals of the package:
- Capture HTTP request and response data at runtime (method, route pattern, headers, query params, JSON body, status codes, duration).
- Safely store this data locally (initially using SQLite in the application’s storage directory).
- Apply redaction and filtering rules to avoid storing sensitive data (headers like Authorization, body fields like passwords, etc).
- Group captured data by logical endpoint (HTTP method + route URI pattern, not raw paths).
- Use captured traffic to infer:
    - Endpoint usage patterns
    - Request/response schemas
    - Common status codes
- Generate derived artifacts from real traffic, primarily:
    - OpenAPI (Swagger) specifications
    - Laravel HTTP test fixtures (feature tests)

Non-goals (at least for early versions):
- No external services
- No dashboards or UI
- No background jobs or queues
- No perfect OpenAPI compliance
- No automatic inference beyond best-effort heuristics

Technical constraints and design principles:
- The middleware should be thin and defer all logic to application services.
- Runtime performance matters; sampling and early exits should be supported.
- Storage, capture, and analysis concerns should be cleanly separated.
- The package should feel idiomatic and trustworthy to Laravel developers.
- The package should be usable in legacy Laravel apps with minimal setup.

Architecture overview:
- Middleware captures request/response snapshots.
- Snapshots are normalized into DTOs / value objects.
- Data is persisted via a storage abstraction (SQLite first, others possible later).
- Analyzers transform stored data into OpenAPI specs or test fixtures.
- Public APIs should be intention-revealing (e.g. ApiUsage::openApi()->generate()).

Security and compliance considerations:
- Never store raw Authorization headers or cookies by default.
- Support configurable redaction rules.
- Support disabling the package entirely via config or environment.
- Avoid capturing binary responses, file uploads, or streamed responses.

This package is related in philosophy—but not runtime code—to another tool called “Insight”, which focuses on static analysis of models and schema conventions. insight-api is runtime-only and should remain a separate package, with optional future integration via contracts or shared metadata.

When suggesting implementations, prefer clarity, explicitness, and Laravel-native patterns over cleverness.
