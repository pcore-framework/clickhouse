<?php

declare(strict_types=1);

namespace PCore\ClickHouse\Contracts;

use Generator;

/**
 * Class ResponseParserInterface
 * @package PCore\ClickHouse\Contracts
 * @github https://github.com/pcore-framework/clickhouse
 */
interface ResponseParserInterface
{

    /**
     * @param string $block
     * @return $this
     */
    public function add(string $block) : self;

    /**
     * @return Generator
     */
    public function row(): Generator;

}
