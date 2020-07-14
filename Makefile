TEST_NAME :=
DEBUG :=
test:
	docker-compose run test

clean:
	rm -rf vendor/ composer.lock cpresources web

composer-install:
	docker run --rm -it -v "${PWD}:/var/www/html/" flipbox/php:72-apache sh -c "composer install"

clean-install: clean composer-install
phpcs: composer-install
	docker run --rm -it -v "${PWD}:/var/www/html" \
	    flipbox/php:72-apache sh -c "./vendor/bin/phpcs --standard=psr2 --ignore=./src/web/assets/*/dist/*,./src/migrations/m* ./src"
phpcbf: composer-install
	docker run --rm -it -v "${PWD}:/var/www/html" \
	    flipbox/php:72-apache sh -c "./vendor/bin/phpcbf --standard=psr2 ./src"

# DOCS
docs-build:
	npm run docs:build
docs-dev:
	npm run docs:dev
