<?php

namespace DDTrace\Tests\Metrics\SigSegV;

use DDTrace\Tests\Common\WebFrameworkTestCase;
use DDTrace\Tests\Frameworks\Util\Request\GetSpec;
use DDTrace\Tests\WebServer;

class SigSegVTest extends WebFrameworkTestCase
{
    // this doesn't use the sandbox API, but it doesn't use legacy API either
    const IS_SANDBOX = true;

    protected static function getEnvs()
    {
        return \array_merge(parent::getEnvs(), ['DD_TRACE_HEALTH_METRICS_ENABLED' => 1]);
    }

    protected static function getAppIndexScript()
    {
        return __DIR__ . '/../../Frameworks/Custom/Version_Not_Autoloaded/sigsegv.php';
    }

    protected function setUp()
    {
        if (!extension_loaded('posix')) {
            $this->markTestSkipped(
                'The posix extension is not available.'
            );
        }
        parent::setUp();
        @unlink(__DIR__ . '/../../Frameworks/Custom/Version_Not_Autoloaded/' . WebServer::ERROR_LOG_NAME);
    }

    public function testGet()
    {
        $log = __DIR__ . '/../../Frameworks/Custom/Version_Not_Autoloaded/' . WebServer::ERROR_LOG_NAME;
        self::assertFileNotExists($log);

        $spec = GetSpec::create('sigsegv', '/sigsegv.php');
        $this->call($spec);

        self::assertFileExists($log);
        $contents = \file_get_contents($log);
        self::assertRegExp("/.*Segmentation fault\n.*sigsegv health metric sent/", $contents);
    }
}
