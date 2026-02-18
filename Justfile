list:
    @just --list

install:
    composer install

fmt:
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml fmt

fmt-check:
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml fmt --check

lint:
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml lint --sort

fix:
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml analyze --fix --unsafe
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml lint --fix --unsafe
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml fmt

analyze:
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml analyze --sort

typos:
    typos -c framework/config/typos.toml

test:
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/phpunit -c framework/config/phpunit.xml.dist

coverage: test
    ./vendor/bin/php-coveralls -x var/clover.xml -o var/coveralls-upload.json -v

verify:
    typos -c framework/config/typos.toml
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml fmt --check
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml lint
    ./vendor/bin/mago --workspace framework/ --config framework/config/mago.toml analyze
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/phpunit -c framework/config/phpunit.xml.dist
