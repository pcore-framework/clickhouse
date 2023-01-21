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
     * @var ClickhouseQueryMessage
     */
    private ClickhouseQueryMessage $queryMessage;

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

    public function __destruct()
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }

    /**
     * @param string $sqlQuery
     * @return $this
     */
    public function query(string $sqlQuery): self
    {
        $this->queryMessage = new ClickhouseQueryMessage($sqlQuery, $this->configuration);
        return $this;
    }

    /**
     * @return Generator|null
     * @throws JsonException
     */
    public function stream(): ?Generator
    {
        fwrite($this->socket, (string)$this->queryMessage, $this->queryMessage->length());
        $processingBodyStarted = false;
        while (($line = stream_get_line($this->socket, self::READ_BYTES, "\r\n")) !== false) {
            if (empty($line)) {
                $processingBodyStarted = true;
            } elseif (!$processingBodyStarted && str_contains($line, 'X-ClickHouse-Exception')) {
                throw new ClickhouseServerException($line);
            } elseif ($processingBodyStarted) {
                $block = stream_get_line($this->socket, self::READ_BYTES, "\r\n");
                yield from $this->responseParser->add($block)->row();
            }
        }
    }

}
