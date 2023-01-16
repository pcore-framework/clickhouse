<?php

declare(strict_types=1);

namespace PCore\ClickHouse;

use InvalidArgumentException;
use PCore\ClickHouse\Contracts\ConfigurationInterface;

/**
 * Class Configuration
 * @package PCore\ClickHouse
 * @github https://github.com/pcore-framework/clickhouse
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @var array
     */
    private array $parameters = [
        'scheme' => 'http',
        'host' => 'localhost',
        'port' => 8123,
        'user' => '',
        'password' => '',
        'server.database' => 'default',
        'server.default_format' => 'JSONEachRow',
//        'enable_http_compression' => 1,
//        'max_result_rows' => 10000,
//        'max_result_bytes' => 10000000,
        'server.buffer_size' => 4096,
        'server.wait_end_of_query' => 0,
        'server.send_progress_in_http_headers' => 0,
        'server.output_format_enable_streaming' => 1,
        'server.result_overflow_mode' => 'break'
    ];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if (isset($parameters['scheme']) && !$this->isValidScheme((string)$parameters['scheme'])) {
            throw new InvalidArgumentException('Unsupported scheme: ' . $parameters['scheme']);
        }
        if (isset($parameters['host']) && !$this->isValidHost((string)$parameters['host'])) {
            throw new InvalidArgumentException('Invalid host: ' . $parameters['host']);
        }
        if (isset($parameters['port']) && !$this->isValidPort((int)$parameters['port'])) {
            throw new InvalidArgumentException('Invalid port: ' . $parameters['port']);
        }
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed|string|null
     */
    public function get(string $key, string $default = null): mixed
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    /**
     * @return string
     */
    public function getServerConnectionQuery(): string
    {
        $result = '';
        $keys = preg_grep('/^server\./', array_keys($this->parameters));
        if ($keys) {
            $values = array_intersect_key($this->parameters, array_flip($keys));
            $resultKeys = preg_replace('/^server\./', '', $keys);
            $result = '?' . http_build_query(array_combine($resultKeys, $values));
        }
        return $result;
    }

    /**
     * @param string $scheme
     * @return bool
     */
    private function isValidScheme(string $scheme): bool
    {
        return in_array($scheme, ['http', 'https']);
    }

    /**
     * @param string $host
     * @return bool
     */
    private function isValidHost(string $host): bool
    {
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return true;
        }
        [$ip, $netmask] = explode('/', $host, 2);
        $validIPv4Netmask = $netmask !== null ? $netmask >= 1 && $netmask <= 32 : true;
        $validIPv6Netmask = $netmask !== null ? $netmask >= 0 && $netmask <= 128 : true;
        $validIPv4 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $validIPv4Netmask;
        $validIPv6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && $validIPv6Netmask;
        $validDomain = filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
        return $validIPv4 || $validIPv6 || $validDomain;
    }

    /**
     * @param int $port
     * @return bool
     */
    private function isValidPort(int $port): bool
    {
        return $port > 0 && $port < 65536;
    }

}
