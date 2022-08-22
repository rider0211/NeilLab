<?php

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('UTC');
require_once 'vendor/autoload.php';

function create_exchange($exchange_id, $config) {

    $keys = array(
        'ids',
        'markets',
        'markets_by_id',
        'currencies',
        'currencies_by_id',
        'base_currencies',
        'quote_currencies',
        'symbols',
    );


    $exchange_class = '\\ccxtpro\\' . $exchange_id;
    $exchange = new $exchange_class($config);
    $markets_on_disk = "./{$id}.markets.json";

    $exchange->verbose = true; // this is a debug output to demonstrate which networking calls are being issued

    if (file_exists($markets_on_disk)) {

        $cache = json_decode(file_get_contents($markets_on_disk), true);
        foreach ($keys as $key) {
            $exchange->{$key} = $cache[$key];
        }

    } else {

        $markets = yield $exchange->load_markets();
        $cache = array();
        foreach ($keys as $key) {
            $cache[$key] = $exchange->{$key};
        }
        file_put_contents($markets_on_disk, json_encode($cache));
    }

    return $exchange;
}

$exchanges = array(
    array('binance', array(
        'id' => 'binance1',
        'apiKey' => YOUR_API_KEY_HERE,
        'secret' => YOUR_SECRET_HERE,
    )),
    array('binance', array(
        'id' => 'binance2',
        'apiKey' => YOUR_API_KEY_HERE,
        'secret' => YOUR_SECRET_HERE,
    )),
);

function loop($exchange_id, $config) {
    $exchange = yield create_exchange($exchange_id, $config);
    $exchange->verbose = true;
    while (true) {
        $response = yield $exchange->watch_balance();
        print('--------------------------------------------------------------');
        print($exchange->id);
        print($response);
    }
};


$exchange = new \ccxtpro\binance();
$kernel = $exchange::get_kernel();
foreach ($exchanges as $exchange) {
    $kernel->execute(loop($exchange[0], $exchange[1]));
}

$kernel->run();
