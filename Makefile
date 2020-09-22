updev:
	docker-compose up

devphpbash:
	docker exec -it cbrf_fpm_1 bash

vendor:
	docker-compose run fpm composer install

run: vendor
	docker-compose up

# Testing
test: vendor
	docker-compose run fpm vendor/bin/phpunit tests