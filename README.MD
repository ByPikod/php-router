# PHP Test

![License](https://img.shields.io/github/license/ByPikod/php-router.svg?style=for-the-badge)
![Maintaned](https://img.shields.io/badge/Maintained%3F-yes-green.svg?style=for-the-badge)
![Commits](https://img.shields.io/github/commits-since/ByPikod/php-router/latest.svg?style=for-the-badge)
![Forks](https://img.shields.io/github/forks/ByPikod/php-router.svg?style=for-the-badge)
![Stars](https://img.shields.io/github/stars/ByPikod/php-router.svg?style=for-the-badge)
![Watchers](https://img.shields.io/github/watchers/ByPikod/php-router.svg?style=for-the-badge)

Simple PHP test library for unit testing.

> [!Warning]
> Minimum PHP version for this package is PHP7.

## Installation

Add this package to your project with composer:

```bash
composer require bypikod/php-router
```

> [!Note]
> This project is designed to be used with Apache. You can find a sample ".htaccess" file [here](example.htaccess).

## Getting Started

```php
use PHPRouter\Router;

$router = new Router();
$router->route('/', function () {
    echo "Hello World";
});

$router->run();
```

## Contribute

Contributers are welcome! Just make sure you understand the follow the rules below:

- Contributions must follow the [PSR-2](https://www.php-fig.org/psr/psr-2/) coding standard.
- Make sure you're writing documentation that covers your code changes.
- Follow the [Code of Conduct](CODE_OF_CONDUCT.md).

And you should know your submissions will be under [MIT License](LICENSE).

## License

This project is licensed under the terms of the MIT License.

You are free to use this project in compliance with the MIT License. If you decide to use, modify, or redistribute this software, you must include a copy of the original license and copyright notice in all copies or substantial portions of the software.

For more information about the MIT License, visit: [MIT License](https://www.mit.edu/~amini/LICENSE.md).
