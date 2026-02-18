help:                                                                           ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install:                                                              			## install all dependencies for a development environment
	composer install -n

update:                                                              			## update all dependencies for a development environment
	composer update

coding-standard-fix:                                                            ## apply automated coding standard fixes
	./vendor/bin/mago --config config/mago.toml fmt
	./vendor/bin/mago --config config/mago.toml lint --fix --unsafe

coding-standard-check:                                                          ## check coding-standard compliance
	./vendor/bin/mago --config config/mago.toml fmt --check
	./vendor/bin/mago --config config/mago.toml lint

static-analysis:                                                                ## run static analysis checks
	./vendor/bin/psalm -c config/psalm.xml

uncached-static-analysis:                                                      ## run static analysis checks without cache
	./vendor/bin/psalm -c config/psalm.xml --no-cache

type-coverage:                                                                  ## send static analysis type coverage metrics to https://shepherd.dev/
	./vendor/bin/psalm -c config/psalm.xml --shepherd --stats

security-analysis:                                                              ## run static analysis security checks
	./vendor/bin/psalm -c config/psalm.xml --taint-analysis

unit-tests:                                                                     ## run unit test suite
	XDEBUG_MODE=coverage php vendor/bin/phpunit -c config/phpunit.xml.dist

code-coverage: unit-tests                                                       ## generate and upload test coverage metrics to https://coveralls.io/
	./vendor/bin/php-coveralls -x var/clover.xml -o var/coveralls-upload.json -v

check: coding-standard-check static-analysis security-analysis unit-tests ## run quick checks for local development iterations
