list:
    @just --list

install:
    composer install

fmt:
    ./vendor/bin/mago --config config/mago.toml fmt

fmt-check:
    ./vendor/bin/mago --config config/mago.toml fmt --check

lint:
    ./vendor/bin/mago --config config/mago.toml lint --sort

fix:
    ./vendor/bin/mago --config config/mago.toml analyze --fix --unsafe
    ./vendor/bin/mago --config config/mago.toml lint --fix --unsafe
    ./vendor/bin/mago --config config/mago.toml fmt

analyze:
    ./vendor/bin/mago --config config/mago.toml analyze --sort

typos:
    typos -c config/typos.toml

test:
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/phpunit -c config/phpunit.xml.dist

coverage: test
    ./vendor/bin/php-coveralls -x var/clover.xml -o var/coveralls-upload.json -v

verify:
    typos -c config/typos.toml
    ./vendor/bin/mago --config config/mago.toml fmt --check
    ./vendor/bin/mago --config config/mago.toml lint
    ./vendor/bin/mago --config config/mago.toml analyze
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/phpunit -c config/phpunit.xml.dist
