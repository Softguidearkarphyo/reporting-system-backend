up:
	docker compose up --build -d

down:
	docker compose down

restart:
	docker compose down
	docker compose up --build -d

logs:
	docker compose logs -f

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan migrate:fresh --seed

clear:
	docker compose exec app php artisan optimize:clear

composer:
	docker run --rm -v "$(CURDIR):/app" -w /app composer:2 install --no-interaction --prefer-dist --ignore-platform-reqs

artisan:
	docker compose exec app php artisan
