[![Build Status](https://travis-ci.org/ministryofjustice/opg-lpa-api-server.svg)](https://travis-ci.org/ministryofjustice/opg-lpa-api-server)

---
# OPG LPA API v 1.0.1

Moved `LICENSE` to `LICENSE` file.

## Application Server (aka the API)

The Application Server is written in PHP 5.4 using [Zend Framework 2](http://framework.zend.com/).

It exposes a RESTful API with endpoints for retrieving, storing and submitting Applications and Registrations.

Within the larger LPA front office system the Application Server is the point of authority for validation and workflow processing.

## How to setup a development environment

### Requirements:
PHP 5.4+ with PDO support for MySQL, SQLite and MongoDB

Create the following files:

```
config/autoload/local.php
```

Templates are available:

```
config/autoload/local.php.jinja
```

### Run the following from the application's root directory:

```
php composer.phar install --dev
```

Give your webserver appropriate permissions to write into the following directories:

```
data
data/cache
```

### To run all tests

```
vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml
```

#### Unit tests only

```
vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml module/Opg/tests/Unit/
```

#### Integration tests only

```
vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml module/Opg/tests/Integration/
```

## TODO

- [x] Removed `vendor` directory *(production dependencies re-added as requested)*
- [x] Moved `LICENSE` to its own file
- [ ] Moved best practices config files (i.e. `phpcs.xml`) to `test` directory
- [ ] Moved `bin` directory from root of project to default location (vendor)
- [x] Add Unit Tests to Travis CI
- [ ] Add Integration/Smoke Tests to Travis CI
- [ ] Coveralls support for CodeCoverage from TravisCI
