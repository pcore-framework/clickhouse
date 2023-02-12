## ClickHouse

### Установка

```shell
composer require pcore/clickhouse
```

```php
$connection = new \PCore\ClickHouse\Configuration([
    'host' => '127.0.0.1',
    'server.database' => 'test'
]);

$ch = new \PCore\ClickHouse\Client($connection);

$ch->query('select date, user_id, device_id, name from tracker where date between \'2023-01-16\' and \'2023-01-17\' ');

$i = 0;
foreach ($ch->stream() as $one) {
    var_dump($one);
    var_dump($i . ' ' . convert(memory_get_usage(true)));
    $i++;
}

function convert($size)
{
    $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}
```
