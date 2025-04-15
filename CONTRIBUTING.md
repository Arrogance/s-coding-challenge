# Contributing

Thank you for your interest in this project!

Although this repository was originally developed as part of a technical challenge, its structure and architecture were designed with long-term maintainability and extensibility in mind. If you'd like to explore, extend, or learn from the codebase, here are a few guidelines:

## 🧱 Structure

The application follows a strict separation of concerns:

- **Domain Layer**: Business logic lives here, decoupled from infrastructure.
- **Application Layer**: Use cases and commands interact with the domain via services.
- **Infrastructure Layer**: Integrates with Symfony, Messenger, Doctrine, etc.
- **Bounded Contexts**: Organized by `User`, `WorkEntry`, and shared logic in `Common`.

## 🧪 Tests

Run tests using:

```bash
make phpunit
```

Tests live in the `tests/` directory and follow the same modular structure as the application.

## 🐘 Coding Style

The project follows the Symfony and PSR coding standards. You can format the code with:

```bash
make style
```

## 🐳 Docker & Local Setup

All environments are containerized. Make sure you have Docker + GNU Make installed.

```bash
make build up install
```

You're now ready to explore!

## 📬 Suggestions

Feel free to fork this repository for your own use or adapt its patterns into your own Symfony-based applications. If you find it useful, consider leaving a ⭐️ on GitHub!

---

This project is intended for demonstration purposes and not actively maintained, but it reflects real-world practices used in professional environments.