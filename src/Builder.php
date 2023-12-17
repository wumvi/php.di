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
    public const string SERVICE_FILE = 'app/config/services.yml';
    public const string ENV_FILE = 'app/.env';
    public const string NO_ENV = 'NoEnvFile';

    private ?Container $di = null;
    private static ?Builder $diSelf = null;

    public static function getCacheFilename(string $envHash = ''): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'SymfonyDiCacheContainer-' . $envHash . '.php';
    }

    public static function getEnvHash(string $envFile): string
    {
        $envHash = self::NO_ENV;
        if (!empty($envFile) && is_file($envFile)) {
            (new Dotenv())->loadEnv($envFile);
            $envHash = md5_file($envFile) ?: self::NO_ENV;
        }

        return $envHash;
    }

    public function getDi(
        string $file,
        string $envFile = '',
        bool $resolveEnvPlaceholders = true,
        bool $isDebug = true
    ): Container {
        if ($this->di === null) {
            $tmpCache = self::getCacheFilename(self::getEnvHash($envFile));
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
                $containerConfigCache->write(
                    $dumper->dump($options),
                    $containerBuilder->getResources()
                );
            }

            include $tmpCache;
            $this->di = new \SymfonyDiCacheContainerClass();
        }

        return $this->di;
    }

    public static function makeDi(
        string $root = '/',
        bool $resolveEnvPlaceholders = true,
        string $configFile = self::SERVICE_FILE,
        string $envFile = self::ENV_FILE
    ): Container {
        if (self::$diSelf === null) {
            self::$diSelf = new self();
        }

        $root = $root ?: $_SERVER['DOCUMENT_ROOT'];

        return self::$diSelf->getDi(
            $root . $configFile,
            $root . $envFile,
            $resolveEnvPlaceholders,
            self::isDev()
        );
    }

    public static function isDev(): bool
    {
        return ($_SERVER['APP_ENV'] ?? '') === 'dev';
    }
}
