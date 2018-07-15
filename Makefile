PHPUNIT_BIN = ./bin/phpunit
PHPCS_BIN = ./bin/phpcs
.PHONY: test

test:
    $(PHPCS_BIN) --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
    $(PHPCS_BIN) --standard=Symfony src
    $(PHPCS_BIN) --standard=Symfony tests
    $(PHPUNIT_BIN)
