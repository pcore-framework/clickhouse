<?php

use PCore\ClickHouse\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigurationTest
 * @package PCore\ClickHouse
 * @github https://github.com/pcore-framework/clickhouse
 */
class ConfigurationTest extends TestCase
{

    protected $validParams = [
        'scheme' => 'http',
        'host' => 'localhost',
        'port' => 8123,
        'user' => '',
        'password' => '',

        'server.database' => 'default',
        'server.default_format' => 'JSONEachRow',
        'server.buffer_size' => 4096,
        'server.wait_end_of_query' => 0,
        'server.send_progress_in_http_headers' => 0,
        'server.output_format_enable_streaming' => 1,
        'server.result_overflow_mode' => 'break'
    ];

    /**
     * @dataProvider optionsDataProvider
     * @param array $parameters
     * @param string $key
     * @param string $value
     * @param string|null $exception
     * @return void
     */
    public function testInit(array $parameters, string $key, $value, string $exception = null)
    {
        if (!is_null($exception)) {
            $this->expectException($exception);
        }

        $sut = new Configuration($parameters);
        $this->assertEquals($value, $sut->get($key));
    }

    /**
     * @return void
     */
    public function testGetServerParameters()
    {
        $serverConfigKeys = preg_grep('/^server\./', array_keys($this->validParams));
        $serverConfig = array_intersect_key($this->validParams, array_flip($serverConfigKeys));

        $result = preg_replace('/^server\./', '', $serverConfigKeys);
        $expected = '?' . http_build_query(array_combine($result, $serverConfig));

        $sut = new Configuration($this->validParams);
        $this->assertSame($expected, $sut->getServerConnectionQuery());
    }

    /**
     * @return Generator
     */
    public function optionsDataProvider(): Generator
    {
        $validSchemes = ['http', 'https'];
        $invalidSchemes = ['ftp', '', 123, 'http://', 'https://'];
        $validHosts = [
            'localhost',
            '127.0.0.1',
            '172.16.238.2',
            '172.16.238.2/32',
            'google.local.com',
            '::1',
            '::1/128',
            '1200:0000:AB00:1234:0000:2552:7777:1313',
            'fc00::/7'
        ];
        $invalidHosts = [
            '',
            '!@#$@#$',
            'fe80::20:2e4f::39ac',
            '172.16.238.2/462',
            '172.16.238.2/0',
            '172.16.238.2/0/2',
            '32/172.16.238.2'
        ];
        $validPorts = [8123, '8123', '9000'];
        $invalidPorts = [0, '', '0', -10, PHP_INT_MAX];

        foreach ($validSchemes as $scheme) {
            $params = array_replace($this->validParams, ['scheme' => $scheme]);
            yield [$params, 'scheme', $scheme];
        }

        foreach ($validHosts as $host) {
            $params = array_replace($this->validParams, ['host' => $host]);
            yield [$params, 'host', $host];
        }

        foreach ($validPorts as $port) {
            $params = array_replace($this->validParams, ['port' => $port]);
            yield [$params, 'port', $port];
        }

        foreach ($invalidSchemes as $scheme) {
            $params = array_replace($this->validParams, ['scheme' => $scheme]);
            yield [$params, 'scheme', $scheme, InvalidArgumentException::class];
        }

        foreach ($invalidHosts as $host) {
            $params = array_replace($this->validParams, ['host' => $host]);
            yield [$params, 'host', $host, InvalidArgumentException::class];
        }

        foreach ($invalidPorts as $port) {
            $params = array_replace($this->validParams, ['port' => $port]);
            yield [$params, 'port', $port, InvalidArgumentException::class];
        }
    }

}
