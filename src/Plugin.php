<?php declare(strict_types = 1);

/**
 * This file is part of the Nextras Composer Autoload Simplifier library.
 * @license    MIT
 * @link       https://github.com/nextras/composer-autoload-simplifier
 */

namespace Nextras\ComposerAutoloadSimplifier;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;


class Plugin implements PluginInterface, EventSubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return ['post-autoload-dump' => 'optimize'];
	}


	public function activate(Composer $composer, IOInterface $io)
	{

	}


	public function optimize()
	{
		$vendorDir = dirname(__DIR__, 3);
		$classMap = require "$vendorDir/composer/autoload_classmap.php";
		$files = is_file("$vendorDir/composer/autoload_files.php") ? require "$vendorDir/composer/autoload_files.php" : [];
		$lines = [];

		if ($classMap || $files) {
			$lines[] = '<?php declare(strict_types = 1);';
			$lines[] = '';
			$lines[] = '(function () {';
		}

		if ($classMap) {
			$lines[] = '	$requireClassScoped = function (string $path) {';
			$lines[] = '		require $path;';
			$lines[] = '	};';
		}

		if ($classMap && $files) {
			$lines[] = '';
		}

		if ($files) {
			$lines[] = '	$requireFileScoped = function (string $id, string $path) {';
			$lines[] = '		if (empty($GLOBALS[\'__composer_autoload_files\'][$id])) {';
			$lines[] = '			require $path;';
			$lines[] = '			$GLOBALS[\'__composer_autoload_files\'][$id] = TRUE;';
			$lines[] = '		}';
			$lines[] = '	};';
		}

		if ($classMap) {
			$lines[] = '';
			$lines[] = '	spl_autoload_register(function (string $className) use ($requireClassScoped) {';
			$lines[] = '		static $classMap = [';

			foreach ($classMap as $className => $classPath) {
				$key = var_export($className, TRUE);
				$value = '__DIR__ . ' . var_export(substr($classPath, strlen($vendorDir)), TRUE);
				$lines[] = "\t\t\t$key => $value,";
			}

			$lines[] = '		];';
			$lines[] = '';
			$lines[] = '		if (isset($classMap[$className])) {';
			$lines[] = '			$requireClassScoped($classMap[$className]);';
			$lines[] = '		}';
			$lines[] = '	});';
		}

		if ($files) {
			$lines[] = '';
			foreach ($files as $fileHash => $filePath) {
				$arg1 = var_export($fileHash, TRUE);
				$arg2 = '__DIR__ . ' . var_export(substr($filePath, strlen($vendorDir)), TRUE);
				$lines[] = "\t\$requireFileScoped($arg1, $arg2);";
			}
		}

		if ($classMap || $files) {
			$lines[] = '})();';
			$lines[] = '';
		}

		file_put_contents("$vendorDir/autoload.php", implode("\n", $lines));
	}
}
