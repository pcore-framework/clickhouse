<?php

use PCore\ClickHouse\ClickhouseQueryMessage;
use PCore\ClickHouse\Contracts\ConfigurationInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ClickhouseQueryMessageTest
 * @package PCore\ClickHouse
 * @github https://github.com/pcore-framework/clickhouse
 */
class ClickhouseQueryMessageTest extends TestCase
{

    /**
     * @return void
     */
    public function testInit()
    {
        $query = 'SELECT * FROM users';
        $serverParams = '?database=test';
        $host = '192.168.1.1';
        $port = 8123;

        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();
        $configurationMock
            ->expects($this->once())
            ->method('getServerConnectionQuery')
            ->willReturn($serverParams);

        $configurationMock
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($host, $port);

        $sut = new ClickhouseQueryMessage($query, $configurationMock);
        $this->assertGreaterThan(0, $sut->length());

        $result = (string)$sut;

        $this->assertNotFalse(strpos($result, 'POST /' . $serverParams . ' HTTP/1.1'));
        $this->assertNotFalse(strpos($result, $host . ':' . $port));
        $this->assertNotFalse(strpos($result, 'Content-Type: application/x-www-form-urlencoded'));
        $this->assertNotFalse(strpos($result, $query));
    }

}
