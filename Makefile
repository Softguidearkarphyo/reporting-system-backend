# Makefile for Laravel Sail setup

setup:
	sudo apt update && sudo apt install -y composer
	sudo apt install -y php8.3-dom php8.3-xml
	cp -n .env.example .env
	composer install
	./vendor/bin/sail up -d
	docker-compose exec reporting-system php artisan key:generate
	docker-compose exec reporting-system php artisan migrate:fresh
	docker-compose exec reporting-system php artisan optimize:clear

init:
	docker compose -f docker-compose.nginx.yml up --build -d
	docker compose -f docker-compose.nginx.yml exec app cp .env.example .env
	docker compose -f docker-compose.nginx.yml exec app php artisan key:generate
	@echo "Waiting for MySQL to be ready..."
	@until docker compose -f docker-compose.nginx.yml exec app php artisan migrate:fresh --seed >/dev/null 2>&1; do \
		echo "MySQL not ready yet, retrying ..."; \
		sleep 3; \
	done
	@echo "MySQL ready, migrations and seeders run successfully!"

down:
	docker compose -f docker-compose.nginx.yml down --volumes --remove-orphans

seed:
	docker compose -f docker-compose.nginx.yml exec app php artisan migrate:fresh --seed

start:
	./vendor/bin/sail up -d

stop:
	./vendor/bin/sail down

restart:
	./vendor/bin/sail down
	./vendor/bin/sail up -d

migrate:
	./vendor/bin/sail artisan migrate

refresh:
	./vendor/bin/sail artisan migrate:fresh --seed

clear:
	./vendor/bin/sail artisan optimize:clear

key:
	./vendor/bin/sail artisan key:generate

artisan:
	./vendor/bin/sail artisan

composer:
	./vendor/bin/sail composer

logs:
	./vendor/bin/sail logs -f
