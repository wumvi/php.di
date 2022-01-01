<?php

use PHPUnit\Framework\TestCase;
use Wumvi\DI\DiBuilder;

/**
 * @runTestsInSeparateProcesses
 */
class DiProdTest extends TestCase
{
    private const FILE_INDEX = 2;
    private const ENV_FILE = __DIR__ . '/../assets/env' . self::FILE_INDEX . '.txt';
    private const CONFIG_FILE = __DIR__ . '/../assets/config' . self::FILE_INDEX . '.yml';

    public static function setUpBeforeClass(): void
    {
        $file = DiBuilder::getCacheFile(self::ENV_FILE);
        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * @return void
     * @throws Exception
     * @runTestsInSeparateProcesses
     */
    public function testCacheDi(): void
    {
        $diBuilder = new DiBuilder();
        $di = $diBuilder->getDi(self::CONFIG_FILE, self::ENV_FILE, true, false);
        $this->assertEquals('test-param-value2', $di->getParameter('test_param'), 'get prod cache di param');
        $this->assertEquals('1', $di->getParameter('env_value'), 'get prod cache di env');

        $diBuilder->clear();
    }
}