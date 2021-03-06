<?php

declare(strict_types=1);

namespace CasaHugo\Secrets\Tests;

use CasaHugo\Secrets\DotenvVault;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

final class DotenvVaultTest extends TestCase
{
    private $envFile;

    protected function setUp(): void
    {
        $this->envFile = sys_get_temp_dir().'/sf_secrets.env.test';
        @unlink($this->envFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->envFile);
    }

    public function testGenerateKeys()
    {
        $vault = new DotenvVault($this->envFile);

        $this->assertFalse($vault->generateKeys());
        $this->assertSame('The dotenv vault doesn\'t encrypt secrets thus doesn\'t need keys.', $vault->getLastMessage());
    }

    public function testEncryptAndDecrypt()
    {
        $vault = new DotenvVault($this->envFile);

        $plain = "plain\ntext";

        $vault->seal('foo', $plain);

        unset($_SERVER['foo'], $_ENV['foo']);
        (new Dotenv())->load($this->envFile);

        $decrypted = $vault->reveal('foo');
        $this->assertSame($plain, $decrypted);

        $this->assertSame(['foo' => null], array_intersect_key($vault->list(), ['foo' => 123]));
        $this->assertSame(['foo' => $plain], array_intersect_key($vault->list(true), ['foo' => 123]));

        $this->assertTrue($vault->remove('foo'));
        $this->assertFalse($vault->remove('foo'));

        unset($_SERVER['foo'], $_ENV['foo']);
        (new Dotenv())->load($this->envFile);

        $this->assertArrayNotHasKey('foo', $vault->list());
    }
}
