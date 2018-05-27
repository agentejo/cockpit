.PHONY: composer test

COMPOSER_ARGS=update --no-interaction --prefer-source

composer:
	@command -v composer >/dev/null 2>&1; \
	if test $$? -eq 0; then \
		composer $(COMPOSER_ARGS); \
	elif test -r composer.phar; then \
		php composer.phar $(COMPOSER_ARGS); \
	else \
		echo >&2 "Cannot find composer; aborting."; \
		false; \
	fi

test: composer
	vendor/bin/phpunit
