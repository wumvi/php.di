<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use Wumvi\DI\Builder;

#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
class DiDevTest extends TestCase
{
    public const string ENV = '';

    public static function setUpBeforeClass(): void
    {
        $file = Builder::getCacheFilename(Builder::getEnvHash(self::ENV), Builder::PREFIX_CLI);
        if (is_file($file)) {
            unlink($file);
        }
    }
//
//    public function testDevDi(): void
//    {
//        $file = Builder::getCacheFilename(Builder::getEnvHash(self::ENV), Builder::PREFIX_CLI);
//        if (is_file($file)) {
//            unlink($file);
//        }
//
//        $configFile = __DIR__ . '/../assets/config1.yml';
//        $builder = new Builder();
//        // $builder->clear();
//    }

    public function testDevMakeDi(): void
    {
        $file = Builder::getCacheFilename(Builder::getEnvHash(self::ENV), Builder::PREFIX_CLI);
        if (is_file($file)) {
            unlink($file);
        }

        $di = Builder::makeDi(Builder::PREFIX_CLI, __DIR__, true, '/../assets/config1.yml', self::ENV);
        $this->assertEquals('test-param-value1', $di->getParameter('test_param'), 'get dev di param');
    }
}
