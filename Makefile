up:
	docker-compose up -d --build

down:
	docker-compose down

restart:
	docker-compose down && docker-compose up -d --build

composer:
	docker-compose exec app composer install

artisan:
	docker-compose exec app php artisan $(cmd)

migrate:
	docker-compose exec app php artisan migrate --seed

bash:
	docker-compose exec app bash

init:
	docker-compose up -d --build
	@echo "Waiting for 'app' container to be running..."
	@while [ "$$(docker inspect -f '{{.State.Running}}' reporting-system)" != "true" ]; do \
		echo "Waiting for app container..."; \
		sleep 2; \
	done
	# Ensure .env exists
	@if [ ! -f ./.env ]; then cp ./.env.example ./.env; fi
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate:fresh --seed
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:clear
	docker-compose exec app php artisan view:cache
