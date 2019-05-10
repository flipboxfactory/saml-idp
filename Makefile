
composer-update:
	docker-compose exec web sh -c "composer update"
composer-install-plugin:
	docker-compose exec web sh -c "composer install"
test: test-unit

test-unit:
	docker-compose run --rm web sh -c "php ./vendor/bin/codecept run unit ${TEST_NAME} ${DEBUG} --coverage --coverage-html"

test-unit-debug: DEBUG := -vvv -d
test-unit-debug: test-unit

phpcs:
	docker-compose run --rm web sh -c "./plugin/vendor/bin/phpcs --standard=psr2 --ignore=./plugin/src/web/assets/*/dist/*,./plugin/src/migrations/m* ./plugin/src"
phpcbf:
	docker-compose run --rm web sh -c "./plugin/vendor/bin/phpcbf --standard=psr2 ./plugin/src"

# DOCS
docs-build:
	npm run docs:build
docs-dev:
	npm run docs:dev
