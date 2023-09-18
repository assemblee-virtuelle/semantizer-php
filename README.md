# Semantizer PHP

A library to enhance your object model with semantic data.

# Development

```
docker run --rm -it --volume $PWD:/app --user 1000:1000 composer install
```

## Tests

Install PHPUnit following the [official documention](https://docs.phpunit.de/en/10.2/):
```
wget -O phpunit.phar https://phar.phpunit.de/phpunit-10.phar
```
```
chmod +x phpunit.phar
```

Run the tests with:
```
docker run -it --rm -v "$PWD":/usr/src/myapp -w /usr/src/myapp php:8.2-cli ./phpunit.phar test/
```