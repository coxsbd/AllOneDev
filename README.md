# AllOne AI

A complete starter AI workspace using **PHP 8.2 + Laravel 11**, a **Python FastAPI** AI service, **MySQL**, and a responsive **HTML5/CSS3/JavaScript** interface. It includes chat, writing-assistant, and summarization modes. Conversation history is stored in MySQL and scoped to the visitor's Laravel session.

## Requirements

- PHP 8.2+, Composer, and the PDO MySQL extension
- Python 3.10+ and pip
- MySQL 8+ (or a compatible managed MySQL service)
- An OpenAI API key; provider usage may incur charges

## Run with GitHub Codespaces

1. Open this private repository while signed in with access, select **Code → Codespaces → Create codespace on main**.
2. In the project root terminal, install Laravel and initialize its environment:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

3. Create a MySQL database named `allone_ai`. Set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` to credentials for a MySQL server reachable from the Codespace.
4. Add `OPENAI_API_KEY` as a Codespaces secret in repository settings, then restart the Codespace. The secret must be present in the environment where the Python service is started. Never commit a real key.
5. In a second terminal, install and start the Python service:

   ```bash
   cd ai_service
   python -m venv .venv
   source .venv/bin/activate
   pip install -r requirements.txt
   export OPENAI_API_KEY="$OPENAI_API_KEY"
   uvicorn main:app --host 0.0.0.0 --port 8001
   ```

   If you prefer a local environment file, copy `ai_service/.env.example` to `ai_service/.env`, add the key there, and load it with `set -a && source .env && set +a` before running Uvicorn. Do not commit that file.
6. In a third terminal at the project root, run the migration and Laravel:

   ```bash
   php artisan migrate
   php artisan serve --host 0.0.0.0 --port 8000
   ```

7. Open forwarded port **8000** in Codespaces. Laravel calls the Python service at `AI_SERVICE_URL` (default `http://127.0.0.1:8001`).

## Features

- AI chat with recent follow-up context
- Writing assistant for drafting and editing
- Summarization with instructions to preserve source facts
- Responsive mobile and desktop interface
- MySQL persistence for conversations and messages
- Conversations are isolated by Laravel browser session
- Server-side API key handling, request validation, and CSRF protection

## Configuration and security

- `OPENAI_API_KEY` — required by the Python service; configure as a Codespaces secret or in an untracked `ai_service/.env`.
- `OPENAI_MODEL` — optional; defaults to `gpt-4o-mini`.
- `AI_SERVICE_URL` — optional Laravel-to-Python service URL; defaults to `http://127.0.0.1:8001`.
- Database connection settings are read from the root `.env` file.

Never put a GitHub token or AI provider key in source code, frontend JavaScript, `.env.example`, or chat messages. Revoke any token accidentally exposed. This is a starter app, not a fully audited production deployment: before hosting sensitive or multi-user workloads, add user accounts, authorization, rate limiting, retention controls, monitoring, HTTPS, and security testing. Do not store sensitive personal information in it until those safeguards are in place.
