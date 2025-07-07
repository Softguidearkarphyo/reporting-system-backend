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
