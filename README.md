Symfony Backend Challenge – User & WorkEntry Management
=======================================================

This project is a **backend API built with Symfony 6.4 and PHP 8.4**, following a clean architecture approach using **Domain-Driven Design (DDD)** and **Hexagonal Architecture**. It includes secure authentication, full user and work entry management, and an asynchronous domain event system.

> ⚠️ This project was built as part of a backend challenge. Any references to the original company have been redacted intentionally to keep the repository public.

✨ Features
----------

*   ✅ JWT-based authentication (`/login`)
*   👤 Full User management (CRUD + password reset)
*   ⏱️ Work entry tracking with time validation and soft delete
*   ➡️ Event-driven deletion of work entries when a user is removed
*   📊 Paginated list endpoints for users and work entries
*   🌐 OpenAPI 3.0.3 (Swagger) documentation included
*   ✅ Fully tested with PHPUnit 10
*   🫠 Architecture: DDD + CQRS + hexagonal separation


📄 Requirements
---------------

*   Docker with Docker Compose
*   GNU Make (for running Makefile commands)


🚀 Running Locally
------------------

A `Makefile` is included to simplify common tasks.

### Initial setup:

```bash
   make build                # Build Docker containers  
   make up                   # Start containers in background  
   make install              # Install dependencies inside container
```

Then access the app via:

```
http://localhost:8001
```

By default, it uses `.env` for environment configuration. If a `.env.local` file is present, it will override automatically and be used by `make` and `docker compose`.

No additional configuration is needed — all Docker-related env variables are pre-configured.

> 🛠️ **Note**: A dedicated `consumer` service runs inside Docker Compose to process async messages using Symfony Messenger:
>
> ```yaml
> consumer:    
>   entrypoint: [ "php", "bin/console", "messenger:consume", "async", "-vv", "--memory-limit=64M" ]
> ```

🔧 Useful Make Commands
-----------------------

```bash
  make install          # Install dependencies  
  make update           # Update dependencies  
  make phpunit          # Run test suite 
  make style            # Run PHP-CS-Fixer  
  make migrations       # Run Doctrine migrations  
  make bash             # Open bash inside PHP container  
  make logs             # Tail dev logs   
```

Stop or remove containers:

```bash
  make stop             # Stop containers  
  make down             # Remove containers   
```

➡️ Running Tests
----------------

```bash
make phpunit
```

This runs PHPUnit with test environment configured via `.env.test`. All controllers and handlers are fully unit tested.

📃 API Overview
---------------

The OpenAPI spec is located at `openapi.yaml`.

### Base endpoints:

*   `POST /login`
*   `POST /users`
*   `GET /users`
*   `GET /users/{id}`
*   `PATCH /users/{id}`
*   `PUT /users/{id}`
*   `DELETE /users/{id}`
*   `POST /users/{id}/password-reset`


### Work entries:

*   `POST /work-entries`
*   `GET /work-entries`
*   `GET /work-entries/{id}`
*   `PATCH /work-entries/{id}`
*   `PUT /work-entries/{id}`
*   `DELETE /work-entries/{id}`


🔄 Event System
---------------

Domain events are dispatched asynchronously using Symfony Messenger.

*   `UserDeletedEvent` triggers cleanup of related work entries.


In the `test` environment, messages are dispatched synchronously using the `in-memory://` transport.

> 📌 Valkey is used as the default Redis-compatible backend for the message stream transport (`MESSENGER_TRANSPORT_DSN`). However, this can be easily swapped for any supported backend like RabbitMQ or Doctrine.

📈 Architecture Highlights
--------------------------

*   `App\User` and `App\WorkEntry` are separate bounded contexts
*   `App\Common` is an additional shared bounded context for reusable infrastructure (e.g., ValueObjects, CommandBus, EventBus, Attributes, Exception handling)
*   Command Bus and Event Bus interfaces decouple infrastructure from application logic
*   Symfony Messenger used for async communication
*   Doctrine DBAL types defined for value objects (`UserId`, `Email`, etc.), compatible with SQLite for testing
*   Value Objects used extensively for immutability and type safety
*   Tests mock CommandBus and EventBus interactions
*   Tactical CQRS: Queries and commands are handled via separate handlers, even though a single CommandBus is used. This enables scalability without overengineering.
*   Except for Symfony-specific code within the `Infrastructure/Symfony` layer, the rest of the application remains framework-agnostic. Even Value Objects using Symfony UID do not expose Symfony-specific internals.


🧭 Application Flow Example (Async Event)
-----------------------------------------

```
   DELETE /users/{id}         → DeleteUserCommand → DeleteUserHandler
                               └─► dispatches UserDeletedEvent
                                                    ↓                                   
                                      handled by UserDeletedEventSubscriber
                                      └─► sends DeleteWorkEntryByUserCommand
                                                   → DeleteWorkEntryByUserHandler
```

🧪 Potential Improvements
-------------------------

*   ♻️ Add support for restoring soft-deleted users and related work entries through a `UserRestoredEvent`
*   🔄 Include a `/refresh-token` endpoint to allow refreshing JWT tokens
*   📁 Add `QueryBus` abstraction to cleanly separate query operations


💼 License
----------

This repository is for demonstration purposes only. You are welcome to fork it as reference for architectural patterns, but please do not copy-paste for assessments.
