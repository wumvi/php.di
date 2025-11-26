<?php
declare(strict_types=1);

namespace Wumvi\DI;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Dotenv\Dotenv;

class Builder
{
    public const string PREFIX_FPM = 'fpm';
    public const string PREFIX_CLI = 'cli';

    private ?Container $di = null;
    private static ?Builder $diSelf = null;

    public static function getCacheFilename(string $envHash, string $cacheFilenamePrefix): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR
            . 'SymfonyDiCacheContainer-' . $envHash . '-' . $cacheFilenamePrefix . '.php';
    }

    public static function getEnvHash(string $envFile): string
    {
        $envHash = 'NoEnvFile';
        if (!empty($envFile) && is_file($envFile)) {
            new Dotenv()->loadEnv($envFile);
            $envHash = md5_file($envFile) ?: $envHash;
        }

        return $envHash;
    }

    public function getDi(
        string $file,
        string $cacheFilenamePrefix,
        string $envFile = '',
        bool   $resolveEnvPlaceholders = true,
        bool   $isDebug = true,
    ): Container {
        if ($this->di === null) {
            $tmpCache = self::getCacheFilename(self::getEnvHash($envFile), $cacheFilenamePrefix);
            $containerConfigCache = new ConfigCache($tmpCache, $isDebug);
            if (!$containerConfigCache->isFresh()) {
                $containerBuilder = new ContainerBuilder();

                $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
                $loader->load($file);
                $containerBuilder->compile($resolveEnvPlaceholders);

                $dumper = new PhpDumper($containerBuilder);
                $options = [
                    'container.dumper.inline_factories' => true,
                    'container.dumper.inline_class_loader' => true,
                    'debug' => $isDebug,
                    'class' => 'SymfonyDiCacheContainerClass',
                ];
                /** @var string $dump */
                $dump = $dumper->dump($options);
                $containerConfigCache->write($dump, $containerBuilder->getResources());
            }

            include $tmpCache;
            $this->di = new \SymfonyDiCacheContainerClass();
        }

        return $this->di;
    }

    public static function makeDi(
        string $cacheFilenamePrefix = self::PREFIX_FPM,
        string $root = '/',
        bool   $resolveEnvPlaceholders = true,
        string $configFile = 'app/config/services.yml',
        string $envFile = 'app/config/.env'
    ): Container {
        if (self::$diSelf === null) {
            self::$diSelf = new self();
        }

        $root = $root ?: $_SERVER['DOCUMENT_ROOT'];

        return self::$diSelf->getDi(
            $root . $configFile,
            $cacheFilenamePrefix,
            $root . $envFile,
            $resolveEnvPlaceholders,
            self::isDev()
        );
    }

    public static function isDev(): bool
    {
        return ($_SERVER['APP_ENV'] ?? 'prod') === 'dev';
    }
}
