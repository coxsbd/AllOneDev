# AllOne AI

A complete starter AI workspace using **PHP 8.2 + Laravel 11**, a **Python FastAPI** AI service, **MySQL**, and a responsive **HTML5/CSS3/JavaScript** interface. Includes chat, writing-assistant, and summarization modes. Conversation messages are saved in MySQL.

## Requirements

- PHP 8.2+, Composer, and the PDO MySQL extension
- Python 3.10+ and pip
- MySQL 8+
- An OpenAI-compatible provider API key (the provider may charge for use)

## Run with GitHub Codespaces

1. Open the repository and create a Codespace.
2. In the terminal, install Laravel dependencies and configure its environment:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

3. Create a MySQL database named `allone_ai`, then set the MySQL host, username, and password in `.env`. For a managed database, use its connection details.
4. Configure and run the Python AI service in a second terminal:

   ```bash
   cd ai_service
   python -m venv .venv
   source .venv/bin/activate
   pip install -r requirements.txt
   cp .env.example .env
   # Edit ai_service/.env and set OPENAI_API_KEY; never commit the real key.
   set -a && source .env && set +a
   uvicorn main:app --host 0.0.0.0 --port 8001
   ```

   On Windows PowerShell, activate with `.venv\\Scripts\\Activate.ps1` and set the variables with `$env:OPENAI_API_KEY="your-key"` and `$env:OPENAI_MODEL="gpt-4o-mini"` instead of `source`.
5. In another terminal at the project root, run migrations and Laravel:

   ```bash
   php artisan migrate
   php artisan serve --host 0.0.0.0 --port 8000
   ```

6. Open forwarded port **8000** in Codespaces. The Python service should run on port **8001**. Set `AI_SERVICE_URL` in the Laravel environment to the Python service URL reachable from the PHP process.

## Configure secrets safely

For Codespaces, store `OPENAI_API_KEY` as a Codespaces secret, then restart the Codespace or the Python service. Never put a real key in Git, `.env.example`, frontend JavaScript, or chat messages. The Laravel frontend calls the Python service; the provider key is only used server-side by Python.

## Features and routes

- `/` — responsive AI workspace with chat, writing, and summarization modes
- `POST /chat` — validated Laravel endpoint; saves user/assistant messages
- `GET /health` — Laravel health check
- Python `GET /health` and `POST /chat` — provider integration
- MySQL `conversations` and `messages` tables created by `php artisan migrate`
- Laravel CSRF protection and request size validation

This starter does not include user accounts; add authentication and authorization before using it for multiple users or sensitive conversations. The current conversation history is not yet isolated by user. Provider and database services require their own credentials and may incur costs.
