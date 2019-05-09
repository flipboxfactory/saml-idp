composer-update:
	docker-compose exec web sh -c "cd plugin && composer update"
composer-install-plugin:
	docker-compose exec web sh -c "cd plugin && composer install"
test: test-unit

test-unit:
	docker-compose exec web sh -c "cd plugin && php ./vendor/bin/codecept run unit idp/MyProviderRecordTest -v -d --coverage --coverage-xml"
phpcs:
	docker-compose run --rm web sh -c "./plugin/vendor/bin/phpcs --standard=psr2 --ignore=./plugin/src/web/assets/*/dist/*,./plugin/src/migrations/m* ./plugin/src"
phpcbf:
	docker-compose run --rm web sh -c "./plugin/vendor/bin/phpcbf --standard=psr2 ./plugin/src"

# DOCS
docs-build:
	npm run docs:build
docs-dev:
	npm run docs:dev
