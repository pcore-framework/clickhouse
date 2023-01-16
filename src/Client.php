<?php

declare(strict_types=1);

namespace PCore\ClickHouse;

use PCore\ClickHouse\Contracts\ConfigurationInterface;

/**
 * Class Client
 * @package PCore\ClickHouse
 * @github https://github.com/pcore-framework/clickhouse
 */
class Client
{

    public function __construct(
        private ConfigurationInterface $configuration
    )
    {

    }

}
