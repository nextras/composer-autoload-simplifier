Nextras Composer Autoload Simplifier
====================================

[![Downloads this Month](https://img.shields.io/packagist/dm/nextras/composer-autoload-simplifier.svg)](https://packagist.org/packages/nextras/composer-autoload-simplifier)
[![Stable version](http://img.shields.io/packagist/v/nextras/composer-autoload-simplifier.svg)](https://packagist.org/packages/nextras/composer-autoload-simplifier)

Nextras Composer Autoload Simplifier consist of a single script which can replace `vendor/autoload.php` with a simplified and therefore slightly faster version.


### Installation

Use composer:

```console
$ composer require nextras/composer-autoload-simplifier
```

Update your project's `composer.json`:

```json
{
    "require": {
        "nextras/composer-autoload-simplifier": "^0.1"
    },
    "scripts": {
    	"post-autoload-dump": "composer-simplify-autoloader"
    }
}
```


### Example of Simplified Autoloader

```php
<?php declare(strict_types = 1)

(function () {
	$requireClassScoped = function (string $path) {
		require $path;
	};

	$requireFileScoped = function (string $id, string $path) {
		if (empty($GLOBALS['__composer_autoload_files'][$id])) {
			require $path;
			$GLOBALS['__composer_autoload_files'][$id] = TRUE;
		}
	};

	spl_autoload_register(function (string $className) use ($requireClassScoped) {
		static $classMap = [
			'Tracy\\Bar' => __DIR__ . '/tracy/tracy/src/Tracy/Bar.php',
			'Tracy\\BlueScreen' => __DIR__ . '/tracy/tracy/src/Tracy/BlueScreen.php',
            ...
		];

		if (isset($classMap[$className])) {
			$requireClassScoped($classMap[$className]);
		}
	});

	$requireFileScoped('7745382c92b7799bf1294b1f43023ba2', __DIR__ . '/tracy/tracy/src/shortcuts.php');
})();
```


### License

MIT. See full [license](license.md).
