
# Frelay

Frelay is a tool that allows you to define webhook endpoints and then forward the requests received by those endpoints to other URL targets.

Features:
  - Includes a built-in web-based control panel
  - Ability to fan out (forward) requests to multiple URL targets
  - Filtering rules
  - Customize the headers and body of forwarded requests using templates

## Installation

### Using Docker

```sh
docker run -d --name frelay -p 8000:80 ghcr.io/joy2fun/frelay:main

# generate app key. required for newly created container
docker exec -it frelay php artisan key:generate

# run database migration and seeding
docker exec -it frelay php artisan migrate --seed

# create an administrator panel login account
docker exec -it frelay php artisan make:filament-user
# or without prompt
docker exec -it frelay php artisan make:filament-user --name={NAME} --email={EMAIL} --password={PASSWORD}
```

Now you can head to `http://localhost:8000/admin` and login.

### Available Enviroment Variables

```sh
# administrator panel route path
FRELAY_PATH=admin

# timezone
APP_TIMEZONE=UTC

# telescope route path
TELESCOPE_PATH=telescope
```

## Filterting rules

If any filtering rule has been defined for an endpoint target, Frelay will skip forwarding the request to that target when the rule expression evaluates to `false`, `null` or `0` etc.

Frelay's filtering rules are powered by [Symfony Expression Language](https://symfony.com/doc/current/reference/formats/expression_language.html). The following examples can help you quickly understand the basic usage and application of these filtering rules.

Forward the request to the target only if the `log_level` parameter in the request is equal to "error" :
```js
req.input('log_level') == "error"
```
**You can access any `GET` or `POST` parameter using `input` method on `req` [object](https://laravel.com/docs/11.x/requests#input).**

```js
req.input('log_level') in ["error", "warning"]
```
Checks if `log_level` parameter is either "error" or "warning".

```js
now.format('N') in [1, 2, 3, 4, 5]
```
Checks if the current day is a weekday. `now` is a [Carbon](https://carbon.nesbot.com/docs/) object, which is a PHP library that provides robust date and time manipulation capabilities, including the ability to convert dates and times to many different [formats](https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters).

## Override request headers and/or body using templates

Rename the `message` parameter to `error` before forwarding the request to the target.
```json
{
  "error": "{{ req.input('message') }}"
}
```
Ensure the headers and body are valid JSON strings; Or the original headers or body will be used when forwarding.

## Database configuration

By default, SQLite is used for persistent data storage. The database file is stored in the `VOLUME` `/var/www/html/database/frelay`. You may use a named volume :
```sh
docker run -d --name frelay \
    -v frelay_data:/var/www/html/database/frelay \
    -p 8000:80 ghcr.io/joy2fun/frelay:main
```

If you prefer to use another [database](https://laravel.com/docs/11.x/database#configuration), such as `PostgreSQL` or `MySQL`, adjust the environment variables accordingly:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

## Debugging

Frelay is shipped with integration for [Laravel Telescope](https://laravel.com/docs/11.x/telescope)
, which allows you to easily inspect all incoming requests and forwarded requests (HTTP Client requests in Laravel).
