<?php

declare(strict_types=1);

namespace PCore\ClickHouse;

use Generator;
use JsonException;
use PCore\ClickHouse\Contracts\{ConfigurationInterface, ResponseParserInterface};
use PCore\ClickHouse\Exceptions\ConnectionException;

/**
 * Class Client
 * @package PCore\ClickHouse
 * @github https://github.com/pcore-framework/clickhouse
 */
class Client
{

    protected const READ_BYTES = 65535;

    private $socket;

    /**
     * @var ResponseParserInterface|JSONEachRowStreamResponseParser
     */
    private ResponseParserInterface $responseParser;

    public function __construct(
        private ConfigurationInterface $configuration,
        ResponseParserInterface        $responseParser = null
    )
    {
        $this->responseParser = $responseParser ?? new JSONEachRowStreamResponseParser();
        $this->socket = fsockopen(
            $this->configuration->get('host'),
            $this->configuration->get('port'),
            $errorCode,
            $errorMessage
        );
        if ($this->socket === false) {
            throw new ConnectionException($errorMessage, $errorCode);
        }
    }

    /**
     * @return Generator|null
     * @throws JsonException
     */
    public function stream(): ?Generator
    {
        $block = stream_get_line($this->socket, self::READ_BYTES, "\r\n");
        yield from $this->responseParser->add($block)->row();
    }

}
