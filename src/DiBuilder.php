<?php
declare(strict_types=1);

namespace Wumvi\DI;

use Exception;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Container;
use \Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use \Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use \Symfony\Component\Dotenv\Dotenv;

class DiBuilder
{
    private ?Container $di = null;

    public static function makeCacheFilename(string $envHash, ?string $tmpDir = null): string
    {
        $tmpDir = $tmpDir ?: sys_get_temp_dir();

        return $tmpDir . DIRECTORY_SEPARATOR . 'SymfonyDiCacheContainer-' . $envHash . '.php';
    }

    public static function getEnvHash(string $envFile): string
    {
        if (!empty($envFile) && is_file($envFile)) {
            (new Dotenv())->loadEnv($envFile);
            return md5_file($envFile);
        }

        return 'NoEnvFile';
    }

    public static function getCacheFile(string $envFile): string
    {
        return self::makeCacheFilename(self::getEnvHash($envFile));
    }

    /**
     * @param string $file
     * @param string $envFile
     * @param bool $resolveEnvPlaceholders
     * @param bool $isDebug
     *
     * @return Container
     *
     * @throws Exception
     */
    public function getDi(
        string $file,
        string $envFile = '',
        bool $resolveEnvPlaceholders = true,
        bool $isDebug = true
    ): Container {
        if ($this->di === null) {
            $tmpCache = self::getCacheFile($envFile);
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

    public function clear(): void
    {
        $this->di = null;
    }
}
