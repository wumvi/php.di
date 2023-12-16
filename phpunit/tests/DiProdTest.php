<?php

use PHPUnit\Framework\TestCase;
use Wumvi\DI\Builder;

/**
 * @runTestsInSeparateProcesses
 */
class DiProdTest extends TestCase
{
    private const int FILE_INDEX = 2;
    private const string ENV_FILE = __DIR__ . '/../assets/env' . self::FILE_INDEX . '.txt';
    private const string CONFIG_FILE = __DIR__ . '/../assets/config' . self::FILE_INDEX . '.yml';

    public static function setUpBeforeClass(): void
    {
        $file = Builder::getCacheFilename(self::ENV_FILE);
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
        $diBuilder = new Builder();
        $di = $diBuilder->getDi(self::CONFIG_FILE, self::ENV_FILE, true, false);
        $this->assertEquals('test-param-value2', $di->getParameter('test_param'), 'get prod cache di param');
        $this->assertEquals('1', $di->getParameter('env_value'), 'get prod cache di env');
    }
}