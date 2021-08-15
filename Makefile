.PHONY: analyze fix-code test coverage mutation-test validation

analyze: | vendor
	$(COMPOSER) exec -v parallel-lint -- src
	$(COMPOSER) exec -v php-cs-fixer -- fix --dry-run
	$(COMPOSER) exec -v unused_scanner -- .unused.dist.php
	$(COMPOSER) exec -v security-checker -- security:check
	$(COMPOSER) exec -v phpcpd -- --fuzzy src
	$(COMPOSER) exec -v phpmd -- src ansi codesize,controversial,design,naming,unusedcode
	$(COMPOSER) exec -v phpa -- src
	$(COMPOSER) exec -v phpstan -- analyse
	$(COMPOSER) exec -v psalm -- src

fix-code: | vendor
	$(COMPOSER) normalize
	$(COMPOSER) exec -v php-cs-fixer -- fix

test: | vendor
	$(COMPOSER) exec -v phpunit

coverage: | vendor
	@if [ -z "`php -v | grep -i 'xdebug'`" ]; then echo "You need to install Xdebug in order to do this action"; exit 1; fi
	$(COMPOSER) exec -v phpunit -- --coverage-text --color

validation: fix-code analyze test coverage

vendor: composer.json
	$(COMPOSER) install --optimize-autoloader --no-suggest --prefer-dist
	touch vendor

composer.phar:
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php composer-setup.php --quiet
	rm composer-setup.php

# Check Composer installation
ifneq ($(shell command -v composer > /dev/null ; echo $$?), 0)
  ifneq ($(MAKECMDGOALS),composer.phar)
    $(shell $(MAKE) composer.phar)
  endif
  COMPOSER=php composer.phar
else
  COMPOSER=composer
endif