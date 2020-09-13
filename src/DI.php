<?php
declare(strict_types=1);

namespace Wumvi\DI;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Container;
use \Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use \Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;
use \Symfony\Component\Dotenv\Dotenv;

class DI
{
    private ?Container $di = null;

    public static function getCacheFilename(string $envHash): string
    {
        return $tmpCache = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'SymfonyDiCacheContainer-' . $envHash . '.php';
    }

    public static function getEnvHash(string $envFile): string
    {
        $envHash = 'NoEnvFile';
        if (!empty($envFile) && is_file($envFile)) {
            (new Dotenv())->loadEnv($envFile);
            $envHash = md5_file($envFile);
        }

        return $envHash;
    }

    public function getDi(
        string $file,
        string $envFile = '',
        bool $isDebug = true
    ): Container {
        if ($this->di === null) {
            $tmpCache = self::getCacheFilename(self::getEnvHash($envFile));
            $containerConfigCache = new ConfigCache($tmpCache, $isDebug);
            if (!$containerConfigCache->isFresh()) {
                $containerBuilder = new ContainerBuilder();

                $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
                $loader->load($file);
                $containerBuilder->compile(true);

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
}
