<?php

use PHPUnit\Framework\TestCase;
use Wumvi\DI\DiBuilder;

/**
 * @runTestsInSeparateProcesses
 */
class DiDevTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $file = DiBuilder::getCacheFile('');
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testDevDi(): void
    {
        $file = DiBuilder::getCacheFile('');
        if (is_file($file)) {
            unlink($file);
        }

        $configFile = __DIR__ . '/../assets/config1.yml';
        $diBuilder = new DiBuilder();
        $di = $diBuilder->getDi($configFile, '', true, true);
        $this->assertEquals('test-param-value1', $di->getParameter('test_param'), 'get dev di param');
        $diBuilder->clear();
    }
}
