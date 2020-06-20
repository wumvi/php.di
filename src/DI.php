<?php
declare(strict_types=1);

namespace Wumvi\Di;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Container;
use \Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use \Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use \Symfony\Component\Dotenv\Dotenv;

class DI
{
    public const DEFAULT_FILE_CONFIG = '../../conf/services.yml';
    private ?Container $di = null;

    public function getDi(
        string $file = self::DEFAULT_FILE_CONFIG,
        bool $isDebug = true,
        string $envFile = ''
    ): Container {
        $envHash = 'NoEnv';
        if (!empty($envFile) && is_file($envFile)) {
            (new Dotenv())->loadEnv($envFile);
            $envHash = md5_file($envFile);
        }
        if ($this->di === null) {
            $tmpCache = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'SymfonyDiCacheContainer-' . $envHash . '.php';
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
