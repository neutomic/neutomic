help:                                                                           ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install:                                                              			## install all dependencies for a development environment
	composer install -n

update:                                                              			## update all dependencies for a development environment
	composer update

coding-standard-fix:                                                            ## apply automated coding standard fixes
	./vendor/bin/php-cs-fixer fix --config=config/.php_cs.dist.php -vvv
	./vendor/bin/phpcbf --basepath=. --standard=config/.phpcs.xml

coding-standard-check:                                                          ## check coding-standard compliance
	./vendor/bin/php-cs-fixer fix --config=config/.php_cs.dist.php --dry-run -vvv
	./vendor/bin/phpcs --basepath=. --standard=config/.phpcs.xml

MAGO_FORMAT ?= rich

lint:                                                                           ## run linting checks
	./vendor/bin/mago lint --reporting-format $(MAGO_FORMAT)

lint-fix:                                                                       ## apply automated linting fixes
	./vendor/bin/mago lint --fix

analyze:                                                                        ## run static analysis
	./vendor/bin/mago analyze --reporting-format $(MAGO_FORMAT)

analyze-fix:                                                                    ## apply automated analysis fixes
	./vendor/bin/mago analyze --fix

static-analysis: lint analyze                                                   ## run static analysis checks

unit-tests:                                                                     ## run unit test suite
	XDEBUG_MODE=coverage php vendor/bin/phpunit -c config/phpunit.xml.dist

code-coverage: unit-tests                                                       ## generate and upload test coverage metrics to https://coveralls.io/
	./vendor/bin/php-coveralls -x var/clover.xml -o var/coveralls-upload.json -v

check: coding-standard-check static-analysis unit-tests ## run quick checks for local development iterations
