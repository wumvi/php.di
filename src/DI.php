<?php
declare(strict_types=1);

namespace Wumvi\Di;

use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class DI
{
    public const DEFAULT_FILE_CONFIG = '../../conf/services.yml';
    private ?ContainerBuilder $di = null;

    public function getDi(string $file = self::DEFAULT_FILE_CONFIG): ContainerBuilder
    {
        if ($this->di === null) {
            $this->di = new ContainerBuilder();
            $loader = new YamlFileLoader($this->di, new FileLocator(__DIR__));
            $loader->load($file);
        }

        return $this->di;
    }
}
