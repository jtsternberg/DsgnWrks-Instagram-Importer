# Contributing to DsgnWrks Instagram Importer

Before sending a Pull Request, please ensure the tests pass locally.

## Prerequisites:
* [PHP](https://secure.php.net/)
* [MySQL](https://www.mysql.com/) (or [MariaDB](https://mariadb.org/))

## Test setup
First run `./tests/bin/install-wp-tests.sh`. It will download WordPress, setup test configs, and destroy and recreate your database.

Next install [phpunit](https://phpunit.de/) - the easiest method is via [Composer](https://getcomposer.org/): `composer install`.

## Running the tests

Run `phpunit`. If installed via composer, it will be located at `./vendor/bin/phpunit`.
