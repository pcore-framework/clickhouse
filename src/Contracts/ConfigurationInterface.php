<?php

declare(strict_types=1);

namespace PCore\ClickHouse\Contracts;

/**
 * Class ConfigurationInterface
 * @package PCore\ClickHouse\Contracts
 * @github https://github.com/pcore-framework/clickhouse
 */
interface ConfigurationInterface
{

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function get(string $key, string $default = null): mixed;

    /**
     * @return string
     */
    public function getServerConnectionQuery(): string;

}
