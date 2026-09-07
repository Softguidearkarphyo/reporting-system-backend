# Reporting System Backend

Laravel 12 API for the Mirai reporting system. Local development runs on Docker with a live source mount, so edits under `app/`, `routes/`, and related folders apply without rebuilding the image.

## Requirements

- Docker Desktop (or Docker Engine + Compose v2)
- Make (optional; commands below also work as plain `docker compose`)

## Quick start

```bash
cd reporting-system_backend

# Create env file if needed
cp -n .env.example .env

# Build and start all services
make up
# or: docker compose up --build -d

# Generate app key (first time only, if APP_KEY is empty)
docker compose exec app php artisan key:generate

# Run migrations (and seeders if needed)
make migrate
# or: make seed
```

API base URL: **http://localhost:8080**

Example login endpoint: `POST http://localhost:8080/api/login`

## Services

| Service     | Container             | Role                          | Host port |
|-------------|-----------------------|-------------------------------|-----------|
| `web`       | `reporting-nginx`     | Nginx                         | **8080**  |
| `app`       | `reporting-app`       | PHP 8.3 FPM                   | 9000 (internal) |
| `mysql`     | `reporting-mysql`     | MySQL 8                       | **33308** |
| `queue`     | `reporting-queue`     | `php artisan queue:work`      | —         |
| `scheduler` | `reporting-scheduler` | `php artisan schedule:work`   | —         |

Project source is bind-mounted to `/var/www` in `app`, `web`, `queue`, and `scheduler`.

## Makefile commands

```bash
make up        # build and start in background
make down      # stop containers
make restart   # down + up --build
make logs      # follow container logs
make migrate   # php artisan migrate
make seed      # migrate:fresh --seed
make clear     # php artisan optimize:clear
make composer  # install PHP deps via composer:2 image
make artisan   # run artisan inside the app container
```

## Environment

Copy from `.env.example`. Important defaults for Docker:

```env
APP_URL=http://localhost:8080
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=reporting
DB_USERNAME=root
DB_PASSWORD=root
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,localhost:5174,localhost:8080,127.0.0.1,127.0.0.1:5173,127.0.0.1:5174,127.0.0.1:8080
```

CORS allows local Vite origins (`localhost` / `127.0.0.1` on common ports). See `config/cors.php`.

## Frontend

Point the Vue app API URL at this backend, then restart Vite so `.env` is reloaded:

```env
VITE_API_BASE_URL=http://localhost:8080/api/
```

## Composer

`composer.json` targets PHP `^8.2`. The Docker image uses PHP 8.3.

After changing `composer.json` / `composer.lock`:

```bash
make composer
# or
docker run --rm -v "$PWD:/app" -w /app composer:2 install --no-interaction --prefer-dist --ignore-platform-reqs
```

`vendor/` lives on the host mount. Do not bake application source into the image for local work.

## Project layout (Docker)

```
Dockerfile                 # PHP 8.3 FPM + Composer (no app COPY)
docker-compose.yml         # app, web, mysql, queue, scheduler
docker/nginx/default.conf  # Nginx → PHP-FPM
docker/php/entrypoint.sh   # wait for vendor / clear caches
docker/php/local.ini       # PHP limits
Makefile                   # common docker/artisan shortcuts
```

## Notes

- Host port **80** may already be used by another nginx (for example WSL). This stack uses **8080** on purpose.
- MySQL from the host: `127.0.0.1:33308` (user/password from `.env`).
- Inside containers, MySQL host is `mysql` on port `3306`.
