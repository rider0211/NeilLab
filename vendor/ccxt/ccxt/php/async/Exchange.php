<?php


namespace ccxt\async;

use ccxt;

// rounding mode
const TRUNCATE = ccxt\TRUNCATE;
const ROUND = ccxt\ROUND;
const ROUND_UP = ccxt\ROUND_UP;
const ROUND_DOWN = ccxt\ROUND_DOWN;

// digits counting mode
const DECIMAL_PLACES = ccxt\DECIMAL_PLACES;
const SIGNIFICANT_DIGITS = ccxt\SIGNIFICANT_DIGITS;
const TICK_SIZE = ccxt\TICK_SIZE;

// padding mode
const NO_PADDING = ccxt\NO_PADDING;
const PAD_WITH_ZERO = ccxt\PAD_WITH_ZERO;

use \ccxt\Precise;
use \ccxt\AuthenticationError;
use \ccxt\ExchangeError;
use \ccxt\BadSymbol;

use React;
use Recoil;

use Generator;
use Exception;

include 'Throttle.php';

$version = '1.92.32';

class Exchange extends \ccxt\Exchange {

    const VERSION = '1.92.32';

    public static $loop;
    public static $kernel;
    public $browser;
    public $marketsLoading = null;
    public $reloadingMarkets = null;
    public $tokenBucket;
    public $throttle;

    public static function get_loop() {
        return React\EventLoop\Loop::get();
    }

    public static function get_kernel() {
        if (!static::$kernel) {
            static::$kernel = Recoil\React\ReactKernel::create(static::get_loop());
        }
        return static::$kernel;
    }

    public static function execute_and_run($arg) {
        $kernel = static::get_kernel();
        $kernel->execute($arg);
        $kernel->run();
    }

    public function __construct($options = array()) {
        if (!class_exists('React\\EventLoop\\Factory')) {
            throw new ccxt\NotSupported("React is not installed\n\ncomposer require --ignore-platform-reqs react/http:\"^1.4.0\"\n\n");
        }
        if (!class_exists('Recoil\\React\\ReactKernel')) {
            throw new ccxt\NotSupported("Recoil is not installed\n\ncomposer require --ignore-platform-reqs recoil/react:\"1.0.2\"\n\n");
        }
        $config = $this->omit($options, array('loop', 'kernel'));
        parent::__construct($config);
        // we only want one instance of the loop and one instance of the kernel
        if (static::$loop === null) {
            if (array_key_exists('loop', $options)) {
                static::$loop = $options['loop'];
            } else {
                static::$loop = static::get_loop();
            }
        } else if (array_key_exists('loop', $options)) {
            throw new Exception($this->id . ' cannot use two different loops');
        }

        if (static::$kernel === null) {
            if (array_key_exists('kernel', $options)) {
                static::$kernel = $options['kernel'];
            } else {
                static::$kernel = Recoil\React\ReactKernel::create(static::$loop);
            }
        } else if (array_key_exists('kernel', $options)) {
            throw new Exception($this->id . ' cannot use two different loops');
        }

        $connector = new React\Socket\Connector(static::$loop, array(
            'timeout' => $this->timeout,
        ));
        if ($this->browser === null) {
            $this->browser = (new React\Http\Browser(static::$loop, $connector))->withRejectErrorResponse(false);
        }
        $this->throttle = new Throttle($this->tokenBucket, static::$kernel);
    }

    public function fetch($url, $method = 'GET', $headers = null, $body = null) {

        $headers = array_merge($this->headers, $headers ? $headers : array());
        if (!$headers) {
            $headers = array();
        }

        if (strlen($this->proxy)) {
            $headers['Origin'] = $this->origin;
        }

        if ($this->userAgent) {
            if (gettype($this->userAgent) == 'string') {
                $headers['User-Agent'] = $this->userAgent;
            } elseif ((gettype($this->userAgent) == 'array') && array_key_exists('User-Agent', $this->userAgent)) {
                $headers['User-Agent'] = $this->userAgent['User-Agent'];
            }
        }

        // this name for the proxy string is deprecated
        // we should rename it to $this->cors everywhere
        $url = $this->proxy . $url;

        if ($this->verbose) {
            print_r(array('fetch Request:', $this->id, $method, $url, 'RequestHeaders:', $headers, 'RequestBody:', $body));
        }

        $this->lastRestRequestTimestamp = $this->milliseconds();

        try {
            $result = yield $this->browser->request($method, $url, $headers, $body);
        } catch (Exception $e) {
            $message = $e->getMessage();
            if (strpos($message, 'timed out') !== false) { // no way to determine this easily https://github.com/clue/reactphp-buzz/issues/146
                throw new ccxt\RequestTimeout(implode(' ', array($url, $method, 28, $message))); // 28 for compatibility with CURL
            } else if (strpos($message, 'DNS query') !== false) {
                throw new ccxt\NetworkError($message);
            } else {
                throw new ccxt\ExchangeError($message);
            }
        }

        $raw_response_headers = $result->getHeaders();
        $raw_header_keys = array_keys($raw_response_headers);
        $response_headers = array();
        foreach ($raw_header_keys as $header) {
            $response_headers[$header] = $result->getHeaderLine($header);
        }
        $http_status_code = $result->getStatusCode();
        $http_status_text = $result->getReasonPhrase();
        $response_body = strval($result->getBody());

        $response_body = $this->on_rest_response($http_status_code, $http_status_text, $url, $method, $response_headers, $response_body, $headers, $body);

        if ($this->enableLastHttpResponse) {
            $this->last_http_response = $response_body;
        }

        if ($this->enableLastResponseHeaders) {
            $this->last_response_headers = $response_headers;
        }

        if ($this->verbose) {
            print_r(array('fetch Response:', $this->id, $method, $url, $http_status_code, 'ResponseHeaders:', $response_headers, 'ResponseBody:', $response_body));
        }

        $json_response = null;
        $is_json_encoded_response = $this->is_json_encoded_object($response_body);

        if ($is_json_encoded_response) {
            $json_response = $this->parse_json($response_body);
            if ($this->enableLastJsonResponse) {
                $this->last_json_response = $json_response;
            }
        }

        $this->handle_errors($http_status_code, $http_status_text, $url, $method, $response_headers, $response_body, $json_response, $headers, $body);
        $this->handle_http_status_code($http_status_code, $http_status_text, $url, $method, $response_body);

        return isset($json_response) ? $json_response : $response_body;
    }

    public function load_markets_helper($reload = false, $params = array()) {
        // copied from js
        if (!$reload && $this->markets) {
            if (!$this->markets_by_id) {
                return $this->set_markets ($this->markets);
            }
            return $this->markets;
        }
        $currencies = null;
        if (array_key_exists('fetchCurrencies', $this->has) && $this->has['fetchCurrencies'] === true) {
            $currencies = yield $this->fetch_currencies ();
        }
        $markets = yield $this->fetch_markets ($params);
        return $this->set_markets ($markets, $currencies);
    }

    public function loadMarkets($reload = false, $params = array()) {
        return yield $this->load_markets($reload, $params);
    }

    public function load_markets($reload = false, $params = array()) {
        if (($reload && !$this->reloadingMarkets) || !$this->marketsLoading) {
            $this->reloadingMarkets = true;
            $this->marketsLoading = static::$kernel->execute($this->load_markets_helper($reload, $params))->promise()->then(function ($resolved) {
                $this->reloadingMarkets = false;
                return $resolved;
            }, function ($error) {
                $this->reloadingMarkets = false;
                throw $error;
            });
        }
        return $this->marketsLoading;
    }

    public function loadAccounts($reload = false, $params = array()) {
        return yield $this->load_accounts($reload, $params);
    }

    public function sleep($milliseconds) {
        $time = $milliseconds / 1000;
        $loop = $this->get_loop();
        $timer = null;
        return new React\Promise\Promise(function ($resolve) use ($loop, $time, &$timer) {
            $timer = $loop->addTimer($time, function () use ($resolve) {
                $resolve(null);
            });
        });
    }

    // ########################################################################
    // ########################################################################
    // ########################################################################
    // ########################################################################
    // ########                        ########                        ########
    // ########                        ########                        ########
    // ########                        ########                        ########
    // ########                        ########                        ########
    // ########        ########################        ########################
    // ########        ########################        ########################
    // ########        ########################        ########################
    // ########        ########################        ########################
    // ########                        ########                        ########
    // ########                        ########                        ########
    // ########                        ########                        ########
    // ########                        ########                        ########
    // ########################################################################
    // ########################################################################
    // ########################################################################
    // ########################################################################
    // ########        ########        ########                        ########
    // ########        ########        ########                        ########
    // ########        ########        ########                        ########
    // ########        ########        ########                        ########
    // ################        ########################        ################
    // ################        ########################        ################
    // ################        ########################        ################
    // ################        ########################        ################
    // ########        ########        ################        ################
    // ########        ########        ################        ################
    // ########        ########        ################        ################
    // ########        ########        ################        ################
    // ########################################################################
    // ########################################################################
    // ########################################################################
    // ########################################################################

    // METHODS BELOW THIS LINE ARE TRANSPILED FROM JAVASCRIPT TO PYTHON AND PHP

    public function safe_ledger_entry($entry, $currency = null) {
        $currency = $this->safe_currency(null, $currency);
        $direction = $this->safe_string($entry, 'direction');
        $before = $this->safe_string($entry, 'before');
        $after = $this->safe_string($entry, 'after');
        $amount = $this->safe_string($entry, 'amount');
        if ($amount !== null) {
            if ($before === null && $after !== null) {
                $before = Precise::string_sub($after, $amount);
            } elseif ($before !== null && $after === null) {
                $after = Precise::string_add($before, $amount);
            }
        }
        if ($before !== null && $after !== null) {
            if ($direction === null) {
                if (Precise::string_gt($before, $after)) {
                    $direction = 'out';
                }
                if (Precise::string_gt($after, $before)) {
                    $direction = 'in';
                }
            }
        }
        $fee = $this->safe_value($entry, 'fee');
        if ($fee !== null) {
            $fee['cost'] = $this->safe_number($fee, 'cost');
        }
        $timestamp = $this->safe_integer($entry, 'timestamp');
        return array(
            'id' => $this->safe_string($entry, 'id'),
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'direction' => $direction,
            'account' => $this->safe_string($entry, 'account'),
            'referenceId' => $this->safe_string($entry, 'referenceId'),
            'referenceAccount' => $this->safe_string($entry, 'referenceAccount'),
            'type' => $this->safe_string($entry, 'type'),
            'currency' => $currency['code'],
            'amount' => $this->parse_number($amount),
            'before' => $this->parse_number($before),
            'after' => $this->parse_number($after),
            'status' => $this->safe_string($entry, 'status'),
            'fee' => $fee,
            'info' => $entry,
        );
    }

    public function set_markets($markets, $currencies = null) {
        $values = array();
        $marketValues = $this->to_array($markets);
        for ($i = 0; $i < count($marketValues); $i++) {
            $market = $this->deep_extend($this->safe_market(), array(
                'precision' => $this->precision,
                'limits' => $this->limits,
            ), $this->fees['trading'], $marketValues[$i]);
            $values[] = $market;
        }
        $this->markets = $this->index_by($values, 'symbol');
        $this->markets_by_id = $this->index_by($markets, 'id');
        $marketsSortedBySymbol = $this->keysort ($this->markets);
        $marketsSortedById = $this->keysort ($this->markets_by_id);
        $this->symbols = is_array($marketsSortedBySymbol) ? array_keys($marketsSortedBySymbol) : array();
        $this->ids = is_array($marketsSortedById) ? array_keys($marketsSortedById) : array();
        if ($currencies !== null) {
            $this->currencies = $this->deep_extend($this->currencies, $currencies);
        } else {
            $baseCurrencies = array();
            $quoteCurrencies = array();
            for ($i = 0; $i < count($values); $i++) {
                $market = $values[$i];
                $defaultCurrencyPrecision = ($this->precisionMode === DECIMAL_PLACES) ? 8 : $this->parse_number('0.00000001');
                $marketPrecision = $this->safe_value($market, 'precision', array());
                if (is_array($market) && array_key_exists('base', $market)) {
                    $currencyPrecision = $this->safe_value_2($marketPrecision, 'base', 'amount', $defaultCurrencyPrecision);
                    $currency = array(
                        'id' => $this->safe_string_2($market, 'baseId', 'base'),
                        'numericId' => $this->safe_string($market, 'baseNumericId'),
                        'code' => $this->safe_string($market, 'base'),
                        'precision' => $currencyPrecision,
                    );
                    $baseCurrencies[] = $currency;
                }
                if (is_array($market) && array_key_exists('quote', $market)) {
                    $currencyPrecision = $this->safe_value_2($marketPrecision, 'quote', 'amount', $defaultCurrencyPrecision);
                    $currency = array(
                        'id' => $this->safe_string_2($market, 'quoteId', 'quote'),
                        'numericId' => $this->safe_string($market, 'quoteNumericId'),
                        'code' => $this->safe_string($market, 'quote'),
                        'precision' => $currencyPrecision,
                    );
                    $quoteCurrencies[] = $currency;
                }
            }
            $baseCurrencies = $this->sort_by($baseCurrencies, 'code');
            $quoteCurrencies = $this->sort_by($quoteCurrencies, 'code');
            $this->baseCurrencies = $this->index_by($baseCurrencies, 'code');
            $this->quoteCurrencies = $this->index_by($quoteCurrencies, 'code');
            $allCurrencies = $this->array_concat($baseCurrencies, $quoteCurrencies);
            $groupedCurrencies = $this->group_by($allCurrencies, 'code');
            $codes = is_array($groupedCurrencies) ? array_keys($groupedCurrencies) : array();
            $resultingCurrencies = array();
            for ($i = 0; $i < count($codes); $i++) {
                $code = $codes[$i];
                $groupedCurrenciesCode = $this->safe_value($groupedCurrencies, $code, array());
                $highestPrecisionCurrency = $this->safe_value($groupedCurrenciesCode, 0);
                for ($j = 1; $j < count($groupedCurrenciesCode); $j++) {
                    $currentCurrency = $groupedCurrenciesCode[$j];
                    if ($this->precisionMode === TICK_SIZE) {
                        $highestPrecisionCurrency = ($currentCurrency['precision'] < $highestPrecisionCurrency['precision']) ? $currentCurrency : $highestPrecisionCurrency;
                    } else {
                        $highestPrecisionCurrency = ($currentCurrency['precision'] > $highestPrecisionCurrency['precision']) ? $currentCurrency : $highestPrecisionCurrency;
                    }
                }
                $resultingCurrencies[] = $highestPrecisionCurrency;
            }
            $sortedCurrencies = $this->sort_by($resultingCurrencies, 'code');
            $this->currencies = $this->deep_extend($this->currencies, $this->index_by($sortedCurrencies, 'code'));
        }
        $this->currencies_by_id = $this->index_by($this->currencies, 'id');
        $currenciesSortedByCode = $this->keysort ($this->currencies);
        $this->codes = is_array($currenciesSortedByCode) ? array_keys($currenciesSortedByCode) : array();
        return $this->markets;
    }

    public function safe_balance($balance) {
        $balances = $this->omit ($balance, array( 'info', 'timestamp', 'datetime', 'free', 'used', 'total' ));
        $codes = is_array($balances) ? array_keys($balances) : array();
        $balance['free'] = array();
        $balance['used'] = array();
        $balance['total'] = array();
        for ($i = 0; $i < count($codes); $i++) {
            $code = $codes[$i];
            $total = $this->safe_string($balance[$code], 'total');
            $free = $this->safe_string($balance[$code], 'free');
            $used = $this->safe_string($balance[$code], 'used');
            if (($total === null) && ($free !== null) && ($used !== null)) {
                $total = Precise::string_add($free, $used);
            }
            if (($free === null) && ($total !== null) && ($used !== null)) {
                $free = Precise::string_sub($total, $used);
            }
            if (($used === null) && ($total !== null) && ($free !== null)) {
                $used = Precise::string_sub($total, $free);
            }
            $balance[$code]['free'] = $this->parse_number($free);
            $balance[$code]['used'] = $this->parse_number($used);
            $balance[$code]['total'] = $this->parse_number($total);
            $balance['free'][$code] = $balance[$code]['free'];
            $balance['used'][$code] = $balance[$code]['used'];
            $balance['total'][$code] = $balance[$code]['total'];
        }
        return $balance;
    }

    public function safe_order($order, $market = null) {
        // parses numbers as strings
        // it is important pass the $trades as unparsed $rawTrades
        $amount = $this->omit_zero($this->safe_string($order, 'amount'));
        $remaining = $this->safe_string($order, 'remaining');
        $filled = $this->safe_string($order, 'filled');
        $cost = $this->safe_string($order, 'cost');
        $average = $this->omit_zero($this->safe_string($order, 'average'));
        $price = $this->omit_zero($this->safe_string($order, 'price'));
        $lastTradeTimeTimestamp = $this->safe_integer($order, 'lastTradeTimestamp');
        $parseFilled = ($filled === null);
        $parseCost = ($cost === null);
        $parseLastTradeTimeTimestamp = ($lastTradeTimeTimestamp === null);
        $fee = $this->safe_value($order, 'fee');
        $parseFee = ($fee === null);
        $parseFees = $this->safe_value($order, 'fees') === null;
        $shouldParseFees = $parseFee || $parseFees;
        $fees = $this->safe_value($order, 'fees', array());
        $trades = array();
        if ($parseFilled || $parseCost || $shouldParseFees) {
            $rawTrades = $this->safe_value($order, 'trades', $trades);
            $oldNumber = $this->number;
            // we parse $trades as strings here!
            $this->number = 'strval';
            $trades = $this->parse_trades($rawTrades, $market, null, null, array(
                'symbol' => $order['symbol'],
                'side' => $order['side'],
                'type' => $order['type'],
                'order' => $order['id'],
            ));
            $this->number = $oldNumber;
            $tradesLength = 0;
            $isArray = gettype($trades) === 'array' && array_keys($trades) === array_keys(array_keys($trades));
            if ($isArray) {
                $tradesLength = count($trades);
            }
            if ($isArray && ($tradesLength > 0)) {
                // move properties that are defined in $trades up into the $order
                if ($order['symbol'] === null) {
                    $order['symbol'] = $trades[0]['symbol'];
                }
                if ($order['side'] === null) {
                    $order['side'] = $trades[0]['side'];
                }
                if ($order['type'] === null) {
                    $order['type'] = $trades[0]['type'];
                }
                if ($order['id'] === null) {
                    $order['id'] = $trades[0]['order'];
                }
                if ($parseFilled) {
                    $filled = '0';
                }
                if ($parseCost) {
                    $cost = '0';
                }
                for ($i = 0; $i < count($trades); $i++) {
                    $trade = $trades[$i];
                    $tradeAmount = $this->safe_string($trade, 'amount');
                    if ($parseFilled && ($tradeAmount !== null)) {
                        $filled = Precise::string_add($filled, $tradeAmount);
                    }
                    $tradeCost = $this->safe_string($trade, 'cost');
                    if ($parseCost && ($tradeCost !== null)) {
                        $cost = Precise::string_add($cost, $tradeCost);
                    }
                    $tradeTimestamp = $this->safe_value($trade, 'timestamp');
                    if ($parseLastTradeTimeTimestamp && ($tradeTimestamp !== null)) {
                        if ($lastTradeTimeTimestamp === null) {
                            $lastTradeTimeTimestamp = $tradeTimestamp;
                        } else {
                            $lastTradeTimeTimestamp = max ($lastTradeTimeTimestamp, $tradeTimestamp);
                        }
                    }
                    if ($shouldParseFees) {
                        $tradeFees = $this->safe_value($trade, 'fees');
                        if ($tradeFees !== null) {
                            for ($j = 0; $j < count($tradeFees); $j++) {
                                $tradeFee = $tradeFees[$j];
                                $fees[] = array_merge(array(), $tradeFee);
                            }
                        } else {
                            $tradeFee = $this->safe_value($trade, 'fee');
                            if ($tradeFee !== null) {
                                $fees[] = array_merge(array(), $tradeFee);
                            }
                        }
                    }
                }
            }
        }
        if ($shouldParseFees) {
            $reducedFees = $this->reduceFees ? $this->reduce_fees_by_currency($fees) : $fees;
            $reducedLength = count($reducedFees);
            for ($i = 0; $i < $reducedLength; $i++) {
                $reducedFees[$i]['cost'] = $this->safe_number($reducedFees[$i], 'cost');
                if (is_array($reducedFees[$i]) && array_key_exists('rate', $reducedFees[$i])) {
                    $reducedFees[$i]['rate'] = $this->safe_number($reducedFees[$i], 'rate');
                }
            }
            if (!$parseFee && ($reducedLength === 0)) {
                $fee['cost'] = $this->safe_number($fee, 'cost');
                if (is_array($fee) && array_key_exists('rate', $fee)) {
                    $fee['rate'] = $this->safe_number($fee, 'rate');
                }
                $reducedFees[] = $fee;
            }
            $order['fees'] = $reducedFees;
            if ($parseFee && ($reducedLength === 1)) {
                $order['fee'] = $reducedFees[0];
            }
        }
        if ($amount === null) {
            // ensure $amount = $filled . $remaining
            if ($filled !== null && $remaining !== null) {
                $amount = Precise::string_add($filled, $remaining);
            } elseif ($this->safe_string($order, 'status') === 'closed') {
                $amount = $filled;
            }
        }
        if ($filled === null) {
            if ($amount !== null && $remaining !== null) {
                $filled = Precise::string_sub($amount, $remaining);
            }
        }
        if ($remaining === null) {
            if ($amount !== null && $filled !== null) {
                $remaining = Precise::string_sub($amount, $filled);
            }
        }
        // ensure that the $average field is calculated correctly
        if ($average === null) {
            if (($filled !== null) && ($cost !== null) && Precise::string_gt($filled, '0')) {
                $average = Precise::string_div($cost, $filled);
            }
        }
        // also ensure the $cost field is calculated correctly
        $costPriceExists = ($average !== null) || ($price !== null);
        if ($parseCost && ($filled !== null) && $costPriceExists) {
            $multiplyPrice = null;
            if ($average === null) {
                $multiplyPrice = $price;
            } else {
                $multiplyPrice = $average;
            }
            // contract trading
            $contractSize = $this->safe_string($market, 'contractSize');
            if ($contractSize !== null) {
                $inverse = $this->safe_value($market, 'inverse', false);
                if ($inverse) {
                    $multiplyPrice = Precise::string_div('1', $multiplyPrice);
                }
                $multiplyPrice = Precise::string_mul($multiplyPrice, $contractSize);
            }
            $cost = Precise::string_mul($multiplyPrice, $filled);
        }
        // support for $market orders
        $orderType = $this->safe_value($order, 'type');
        $emptyPrice = ($price === null) || Precise::string_equals($price, '0');
        if ($emptyPrice && ($orderType === 'market')) {
            $price = $average;
        }
        // we have $trades with string values at this point so we will mutate them
        for ($i = 0; $i < count($trades); $i++) {
            $entry = $trades[$i];
            $entry['amount'] = $this->safe_number($entry, 'amount');
            $entry['price'] = $this->safe_number($entry, 'price');
            $entry['cost'] = $this->safe_number($entry, 'cost');
            $fee = $this->safe_value($entry, 'fee', array());
            $fee['cost'] = $this->safe_number($fee, 'cost');
            if (is_array($fee) && array_key_exists('rate', $fee)) {
                $fee['rate'] = $this->safe_number($fee, 'rate');
            }
            $entry['fee'] = $fee;
        }
        // timeInForceHandling
        $timeInForce = $this->safe_string($order, 'timeInForce');
        if ($timeInForce === null) {
            if ($this->safe_string($order, 'type') === 'market') {
                $timeInForce = 'IOC';
            }
            // allow postOnly override
            if ($this->safe_value($order, 'postOnly', false)) {
                $timeInForce = 'PO';
            }
        }
        return array_merge($order, array(
            'lastTradeTimestamp' => $lastTradeTimeTimestamp,
            'price' => $this->parse_number($price),
            'amount' => $this->parse_number($amount),
            'cost' => $this->parse_number($cost),
            'average' => $this->parse_number($average),
            'filled' => $this->parse_number($filled),
            'remaining' => $this->parse_number($remaining),
            'timeInForce' => $timeInForce,
            'trades' => $trades,
        ));
    }

    public function parse_orders($orders, $market = null, $since = null, $limit = null, $params = array ()) {
        //
        // the value of $orders is either a dict or a list
        //
        // dict
        //
        //     {
        //         'id1' => array( ... ),
        //         'id2' => array( ... ),
        //         'id3' => array( ... ),
        //         ...
        //     }
        //
        // list
        //
        //     array(
        //         array( 'id' => 'id1', ... ),
        //         array( 'id' => 'id2', ... ),
        //         array( 'id' => 'id3', ... ),
        //         ...
        //     )
        //
        $results = array();
        if (gettype($orders) === 'array' && array_keys($orders) === array_keys(array_keys($orders))) {
            for ($i = 0; $i < count($orders); $i++) {
                $order = array_merge($this->parse_order($orders[$i], $market), $params);
                $results[] = $order;
            }
        } else {
            $ids = is_array($orders) ? array_keys($orders) : array();
            for ($i = 0; $i < count($ids); $i++) {
                $id = $ids[$i];
                $order = array_merge($this->parse_order(array_merge(array( 'id' => $id ), $orders[$id]), $market), $params);
                $results[] = $order;
            }
        }
        $results = $this->sort_by($results, 'timestamp');
        $symbol = ($market !== null) ? $market['symbol'] : null;
        $tail = $since === null;
        return $this->filter_by_symbol_since_limit($results, $symbol, $since, $limit, $tail);
    }

    public function calculate_fee($symbol, $type, $side, $amount, $price, $takerOrMaker = 'taker', $params = array ()) {
        $market = $this->markets[$symbol];
        $feeSide = $this->safe_string($market, 'feeSide', 'quote');
        $key = 'quote';
        $cost = null;
        if ($feeSide === 'quote') {
            // the fee is always in quote currency
            $cost = $amount * $price;
        } elseif ($feeSide === 'base') {
            // the fee is always in base currency
            $cost = $amount;
        } elseif ($feeSide === 'get') {
            // the fee is always in the currency you get
            $cost = $amount;
            if ($side === 'sell') {
                $cost *= $price;
            } else {
                $key = 'base';
            }
        } elseif ($feeSide === 'give') {
            // the fee is always in the currency you give
            $cost = $amount;
            if ($side === 'buy') {
                $cost *= $price;
            } else {
                $key = 'base';
            }
        }
        $rate = $market[$takerOrMaker];
        if ($cost !== null) {
            $cost *= $rate;
        }
        return array(
            'type' => $takerOrMaker,
            'currency' => $market[$key],
            'rate' => $rate,
            'cost' => $cost,
        );
    }

    public function safe_trade($trade, $market = null) {
        $amount = $this->safe_string($trade, 'amount');
        $price = $this->safe_string($trade, 'price');
        $cost = $this->safe_string($trade, 'cost');
        if ($cost === null) {
            // contract trading
            $contractSize = $this->safe_string($market, 'contractSize');
            $multiplyPrice = $price;
            if ($contractSize !== null) {
                $inverse = $this->safe_value($market, 'inverse', false);
                if ($inverse) {
                    $multiplyPrice = Precise::string_div('1', $price);
                }
                $multiplyPrice = Precise::string_mul($multiplyPrice, $contractSize);
            }
            $cost = Precise::string_mul($multiplyPrice, $amount);
        }
        $parseFee = $this->safe_value($trade, 'fee') === null;
        $parseFees = $this->safe_value($trade, 'fees') === null;
        $shouldParseFees = $parseFee || $parseFees;
        $fees = array();
        if ($shouldParseFees) {
            $tradeFees = $this->safe_value($trade, 'fees');
            if ($tradeFees !== null) {
                for ($j = 0; $j < count($tradeFees); $j++) {
                    $tradeFee = $tradeFees[$j];
                    $fees[] = array_merge(array(), $tradeFee);
                }
            } else {
                $tradeFee = $this->safe_value($trade, 'fee');
                if ($tradeFee !== null) {
                    $fees[] = array_merge(array(), $tradeFee);
                }
            }
        }
        $fee = $this->safe_value($trade, 'fee');
        if ($shouldParseFees) {
            $reducedFees = $this->reduceFees ? $this->reduce_fees_by_currency($fees) : $fees;
            $reducedLength = count($reducedFees);
            for ($i = 0; $i < $reducedLength; $i++) {
                $reducedFees[$i]['cost'] = $this->safe_number($reducedFees[$i], 'cost');
                if (is_array($reducedFees[$i]) && array_key_exists('rate', $reducedFees[$i])) {
                    $reducedFees[$i]['rate'] = $this->safe_number($reducedFees[$i], 'rate');
                }
            }
            if (!$parseFee && ($reducedLength === 0)) {
                $fee['cost'] = $this->safe_number($fee, 'cost');
                if (is_array($fee) && array_key_exists('rate', $fee)) {
                    $fee['rate'] = $this->safe_number($fee, 'rate');
                }
                $reducedFees[] = $fee;
            }
            if ($parseFees) {
                $trade['fees'] = $reducedFees;
            }
            if ($parseFee && ($reducedLength === 1)) {
                $trade['fee'] = $reducedFees[0];
            }
            $tradeFee = $this->safe_value($trade, 'fee');
            if ($tradeFee !== null) {
                $tradeFee['cost'] = $this->safe_number($tradeFee, 'cost');
                if (is_array($tradeFee) && array_key_exists('rate', $tradeFee)) {
                    $tradeFee['rate'] = $this->safe_number($tradeFee, 'rate');
                }
                $trade['fee'] = $tradeFee;
            }
        }
        $trade['amount'] = $this->parse_number($amount);
        $trade['price'] = $this->parse_number($price);
        $trade['cost'] = $this->parse_number($cost);
        return $trade;
    }

    public function reduce_fees_by_currency($fees) {
        //
        // this function takes a list of $fee structures having the following format
        //
        //     string = true
        //
        //     array(
        //         array( 'currency' => 'BTC', 'cost' => '0.1' ),
        //         array( 'currency' => 'BTC', 'cost' => '0.2'  ),
        //         array( 'currency' => 'BTC', 'cost' => '0.2', 'rate' => '0.00123' ),
        //         array( 'currency' => 'BTC', 'cost' => '0.4', 'rate' => '0.00123' ),
        //         array( 'currency' => 'BTC', 'cost' => '0.5', 'rate' => '0.00456' ),
        //         array( 'currency' => 'USDT', 'cost' => '12.3456' ),
        //     )
        //
        //     string = false
        //
        //     array(
        //         array( 'currency' => 'BTC', 'cost' => 0.1 ),
        //         array( 'currency' => 'BTC', 'cost' => 0.2 ),
        //         array( 'currency' => 'BTC', 'cost' => 0.2, 'rate' => 0.00123 ),
        //         array( 'currency' => 'BTC', 'cost' => 0.4, 'rate' => 0.00123 ),
        //         array( 'currency' => 'BTC', 'cost' => 0.5, 'rate' => 0.00456 ),
        //         array( 'currency' => 'USDT', 'cost' => 12.3456 ),
        //     )
        //
        // and returns a $reduced $fee list, where $fees are summed per currency and $rate (if any)
        //
        //     string = true
        //
        //     array(
        //         array( 'currency' => 'BTC', 'cost' => '0.3'  ),
        //         array( 'currency' => 'BTC', 'cost' => '0.6', 'rate' => '0.00123' ),
        //         array( 'currency' => 'BTC', 'cost' => '0.5', 'rate' => '0.00456' ),
        //         array( 'currency' => 'USDT', 'cost' => '12.3456' ),
        //     )
        //
        //     string  = false
        //
        //     array(
        //         array( 'currency' => 'BTC', 'cost' => 0.3  ),
        //         array( 'currency' => 'BTC', 'cost' => 0.6, 'rate' => 0.00123 ),
        //         array( 'currency' => 'BTC', 'cost' => 0.5, 'rate' => 0.00456 ),
        //         array( 'currency' => 'USDT', 'cost' => 12.3456 ),
        //     )
        //
        $reduced = array();
        for ($i = 0; $i < count($fees); $i++) {
            $fee = $fees[$i];
            $feeCurrencyCode = $this->safe_string($fee, 'currency');
            if ($feeCurrencyCode !== null) {
                $rate = $this->safe_string($fee, 'rate');
                $cost = $this->safe_value($fee, 'cost');
                if (Precise::string_eq($cost, '0')) {
                    // omit zero $cost $fees
                    continue;
                }
                if (!(is_array($reduced) && array_key_exists($feeCurrencyCode, $reduced))) {
                    $reduced[$feeCurrencyCode] = array();
                }
                $rateKey = ($rate === null) ? '' : $rate;
                if (is_array($reduced[$feeCurrencyCode]) && array_key_exists($rateKey, $reduced[$feeCurrencyCode])) {
                    $reduced[$feeCurrencyCode][$rateKey]['cost'] = Precise::string_add($reduced[$feeCurrencyCode][$rateKey]['cost'], $cost);
                } else {
                    $reduced[$feeCurrencyCode][$rateKey] = array(
                        'currency' => $feeCurrencyCode,
                        'cost' => $cost,
                    );
                    if ($rate !== null) {
                        $reduced[$feeCurrencyCode][$rateKey]['rate'] = $rate;
                    }
                }
            }
        }
        $result = array();
        $feeValues = is_array($reduced) ? array_values($reduced) : array();
        for ($i = 0; $i < count($feeValues); $i++) {
            $reducedFeeValues = is_array($feeValues[$i]) ? array_values($feeValues[$i]) : array();
            $result = $this->array_concat($result, $reducedFeeValues);
        }
        return $result;
    }

    public function safe_ticker($ticker, $market = null) {
        $open = $this->safe_value($ticker, 'open');
        $close = $this->safe_value($ticker, 'close');
        $last = $this->safe_value($ticker, 'last');
        $change = $this->safe_value($ticker, 'change');
        $percentage = $this->safe_value($ticker, 'percentage');
        $average = $this->safe_value($ticker, 'average');
        $vwap = $this->safe_value($ticker, 'vwap');
        $baseVolume = $this->safe_value($ticker, 'baseVolume');
        $quoteVolume = $this->safe_value($ticker, 'quoteVolume');
        if ($vwap === null) {
            $vwap = Precise::string_div($quoteVolume, $baseVolume);
        }
        if (($last !== null) && ($close === null)) {
            $close = $last;
        } elseif (($last === null) && ($close !== null)) {
            $last = $close;
        }
        if (($last !== null) && ($open !== null)) {
            if ($change === null) {
                $change = Precise::string_sub($last, $open);
            }
            if ($average === null) {
                $average = Precise::string_div(Precise::string_add($last, $open), '2');
            }
        }
        if (($percentage === null) && ($change !== null) && ($open !== null) && Precise::string_gt($open, '0')) {
            $percentage = Precise::string_mul(Precise::string_div($change, $open), '100');
        }
        if (($change === null) && ($percentage !== null) && ($open !== null)) {
            $change = Precise::string_div(Precise::string_mul($percentage, $open), '100');
        }
        if (($open === null) && ($last !== null) && ($change !== null)) {
            $open = Precise::string_sub($last, $change);
        }
        // timestamp and symbol operations don't belong in safeTicker
        // they should be done in the derived classes
        return array_merge($ticker, array(
            'bid' => $this->safe_number($ticker, 'bid'),
            'bidVolume' => $this->safe_number($ticker, 'bidVolume'),
            'ask' => $this->safe_number($ticker, 'ask'),
            'askVolume' => $this->safe_number($ticker, 'askVolume'),
            'high' => $this->safe_number($ticker, 'high'),
            'low' => $this->safe_number($ticker, 'low'),
            'open' => $this->parse_number($open),
            'close' => $this->parse_number($close),
            'last' => $this->parse_number($last),
            'change' => $this->parse_number($change),
            'percentage' => $this->parse_number($percentage),
            'average' => $this->parse_number($average),
            'vwap' => $this->parse_number($vwap),
            'baseVolume' => $this->parse_number($baseVolume),
            'quoteVolume' => $this->parse_number($quoteVolume),
            'previousClose' => $this->safe_number($ticker, 'previousClose'),
        ));
    }

    public function fetch_ohlcv($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        if (!$this->has['fetchTrades']) {
            throw new NotSupported($this->id . ' fetchOHLCV() is not supported yet');
        }
        yield $this->load_markets();
        $trades = yield $this->fetchTrades ($symbol, $since, $limit, $params);
        $ohlcvc = $this->build_ohlcvc($trades, $timeframe, $since, $limit);
        $result = array();
        for ($i = 0; $i < count($ohlcvc); $i++) {
            $result[] = [
                $this->safe_integer($ohlcvc[$i], 0),
                $this->safe_number($ohlcvc[$i], 1),
                $this->safe_number($ohlcvc[$i], 2),
                $this->safe_number($ohlcvc[$i], 3),
                $this->safe_number($ohlcvc[$i], 4),
                $this->safe_number($ohlcvc[$i], 5),
            ];
        }
        return $result;
    }

    public function convert_trading_view_to_ohlcv($ohlcvs, $timestamp = 't', $open = 'o', $high = 'h', $low = 'l', $close = 'c', $volume = 'v', $ms = false) {
        $result = array();
        $timestamps = $this->safe_value($ohlcvs, $timestamp, array());
        $opens = $this->safe_value($ohlcvs, $open, array());
        $highs = $this->safe_value($ohlcvs, $high, array());
        $lows = $this->safe_value($ohlcvs, $low, array());
        $closes = $this->safe_value($ohlcvs, $close, array());
        $volumes = $this->safe_value($ohlcvs, $volume, array());
        for ($i = 0; $i < count($timestamps); $i++) {
            $result[] = array(
                $ms ? $this->safe_integer($timestamps, $i) : $this->safe_timestamp($timestamps, $i),
                $this->safe_value($opens, $i),
                $this->safe_value($highs, $i),
                $this->safe_value($lows, $i),
                $this->safe_value($closes, $i),
                $this->safe_value($volumes, $i),
            );
        }
        return $result;
    }

    public function convert_ohlcv_to_trading_view($ohlcvs, $timestamp = 't', $open = 'o', $high = 'h', $low = 'l', $close = 'c', $volume = 'v', $ms = false) {
        $result = array();
        $result[$timestamp] = array();
        $result[$open] = array();
        $result[$high] = array();
        $result[$low] = array();
        $result[$close] = array();
        $result[$volume] = array();
        for ($i = 0; $i < count($ohlcvs); $i++) {
            $ts = $ms ? $ohlcvs[$i][0] : intval($ohlcvs[$i][0] / 1000);
            $result[$timestamp][] = $ts;
            $result[$open][] = $ohlcvs[$i][1];
            $result[$high][] = $ohlcvs[$i][2];
            $result[$low][] = $ohlcvs[$i][3];
            $result[$close][] = $ohlcvs[$i][4];
            $result[$volume][] = $ohlcvs[$i][5];
        }
        return $result;
    }

    public function market_ids($symbols) {
        $result = array();
        for ($i = 0; $i < count($symbols); $i++) {
            $result[] = $this->market_id($symbols[$i]);
        }
        return $result;
    }

    public function market_symbols($symbols) {
        if ($symbols === null) {
            return $symbols;
        }
        $result = array();
        for ($i = 0; $i < count($symbols); $i++) {
            $result[] = $this->symbol ($symbols[$i]);
        }
        return $result;
    }

    public function parse_bids_asks($bidasks, $priceKey = 0, $amountKey = 1) {
        $bidasks = $this->to_array($bidasks);
        $result = array();
        for ($i = 0; $i < count($bidasks); $i++) {
            $result[] = $this->parse_bid_ask($bidasks[$i], $priceKey, $amountKey);
        }
        return $result;
    }

    public function fetch_l2_order_book($symbol, $limit = null, $params = array ()) {
        $orderbook = yield $this->fetch_order_book($symbol, $limit, $params);
        return array_merge($orderbook, array(
            'asks' => $this->sort_by($this->aggregate ($orderbook['asks']), 0),
            'bids' => $this->sort_by($this->aggregate ($orderbook['bids']), 0, true),
        ));
    }

    public function filter_by_symbol($objects, $symbol = null) {
        if ($symbol === null) {
            return $objects;
        }
        $result = array();
        for ($i = 0; $i < count($objects); $i++) {
            $objectSymbol = $this->safe_string($objects[$i], 'symbol');
            if ($objectSymbol === $symbol) {
                $result[] = $objects[$i];
            }
        }
        return $result;
    }

    public function parse_ohlcv($ohlcv, $market = null) {
        if (gettype($ohlcv) === 'array' && array_keys($ohlcv) === array_keys(array_keys($ohlcv))) {
            return array(
                $this->safe_integer($ohlcv, 0), // timestamp
                $this->safe_number($ohlcv, 1), // open
                $this->safe_number($ohlcv, 2), // high
                $this->safe_number($ohlcv, 3), // low
                $this->safe_number($ohlcv, 4), // close
                $this->safe_number($ohlcv, 5), // volume
            );
        }
        return $ohlcv;
    }

    public function get_network($network, $code) {
        $network = strtoupper($network);
        $aliases = array(
            'ETHEREUM' => 'ETH',
            'ETHER' => 'ETH',
            'ERC20' => 'ETH',
            'ETH' => 'ETH',
            'TRC20' => 'TRX',
            'TRON' => 'TRX',
            'TRX' => 'TRX',
            'BEP20' => 'BSC',
            'BSC' => 'BSC',
            'HRC20' => 'HT',
            'HECO' => 'HT',
            'SPL' => 'SOL',
            'SOL' => 'SOL',
            'TERRA' => 'LUNA',
            'LUNA' => 'LUNA',
            'POLYGON' => 'MATIC',
            'MATIC' => 'MATIC',
            'EOS' => 'EOS',
            'WAVES' => 'WAVES',
            'AVALANCHE' => 'AVAX',
            'AVAX' => 'AVAX',
            'QTUM' => 'QTUM',
            'CHZ' => 'CHZ',
            'NEO' => 'NEO',
            'ONT' => 'ONT',
            'RON' => 'RON',
        );
        if ($network === $code) {
            return $network;
        } elseif (is_array($aliases) && array_key_exists($network, $aliases)) {
            return $aliases[$network];
        } else {
            throw new NotSupported($this->id . ' $network ' . $network . ' is not yet supported');
        }
    }

    public function safe_number_2($dictionary, $key1, $key2, $d = null) {
        $value = $this->safe_string_2($dictionary, $key1, $key2);
        return $this->parse_number($value, $d);
    }

    public function parse_order_book($orderbook, $symbol, $timestamp = null, $bidsKey = 'bids', $asksKey = 'asks', $priceKey = 0, $amountKey = 1) {
        $bids = $this->parse_bids_asks($this->safe_value($orderbook, $bidsKey, array()), $priceKey, $amountKey);
        $asks = $this->parse_bids_asks($this->safe_value($orderbook, $asksKey, array()), $priceKey, $amountKey);
        return array(
            'symbol' => $symbol,
            'bids' => $this->sort_by($bids, 0, true),
            'asks' => $this->sort_by($asks, 0),
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'nonce' => null,
        );
    }

    public function parse_ohlcvs($ohlcvs, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        $results = array();
        for ($i = 0; $i < count($ohlcvs); $i++) {
            $results[] = $this->parse_ohlcv($ohlcvs[$i], $market);
        }
        $sorted = $this->sort_by($results, 0);
        $tail = ($since === null);
        return $this->filter_by_since_limit($sorted, $since, $limit, 0, $tail);
    }

    public function parse_leverage_tiers($response, $symbols = null, $marketIdKey = null) {
        // $marketIdKey should only be null when $response is a dictionary
        $symbols = $this->market_symbols($symbols);
        $tiers = array();
        for ($i = 0; $i < count($response); $i++) {
            $item = $response[$i];
            $id = $this->safe_string($item, $marketIdKey);
            $market = $this->safe_market($id);
            $symbol = $market['symbol'];
            $contract = $this->safe_value($market, 'contract', false);
            if ($contract && (($symbols === null) || $this->in_array($symbol, $symbols))) {
                $tiers[$symbol] = $this->parse_market_leverage_tiers($item, $market);
            }
        }
        return $tiers;
    }

    public function load_trading_limits($symbols = null, $reload = false, $params = array ()) {
        if ($this->has['fetchTradingLimits']) {
            if ($reload || !(is_array($this->options) && array_key_exists('limitsLoaded', $this->options))) {
                $response = yield $this->fetch_trading_limits($symbols);
                for ($i = 0; $i < count($symbols); $i++) {
                    $symbol = $symbols[$i];
                    $this->markets[$symbol] = $this->deep_extend($this->markets[$symbol], $response[$symbol]);
                }
                $this->options['limitsLoaded'] = $this->milliseconds ();
            }
        }
        return $this->markets;
    }

    public function parse_positions($positions, $symbols = null, $params = array ()) {
        $symbols = $this->market_symbols($symbols);
        $positions = $this->to_array($positions);
        $result = array();
        for ($i = 0; $i < count($positions); $i++) {
            $position = array_merge($this->parse_position($positions[$i], null), $params);
            $result[] = $position;
        }
        return $this->filter_by_array($result, 'symbol', $symbols, false);
    }

    public function parse_accounts($accounts, $params = array ()) {
        $accounts = $this->to_array($accounts);
        $result = array();
        for ($i = 0; $i < count($accounts); $i++) {
            $account = array_merge($this->parse_account($accounts[$i]), $params);
            $result[] = $account;
        }
        return $result;
    }

    public function parse_trades($trades, $market = null, $since = null, $limit = null, $params = array ()) {
        $trades = $this->to_array($trades);
        $result = array();
        for ($i = 0; $i < count($trades); $i++) {
            $trade = array_merge($this->parse_trade($trades[$i], $market), $params);
            $result[] = $trade;
        }
        $result = $this->sort_by_2($result, 'timestamp', 'id');
        $symbol = ($market !== null) ? $market['symbol'] : null;
        $tail = ($since === null);
        return $this->filter_by_symbol_since_limit($result, $symbol, $since, $limit, $tail);
    }

    public function parse_transactions($transactions, $currency = null, $since = null, $limit = null, $params = array ()) {
        $transactions = $this->to_array($transactions);
        $result = array();
        for ($i = 0; $i < count($transactions); $i++) {
            $transaction = array_merge($this->parse_transaction($transactions[$i], $currency), $params);
            $result[] = $transaction;
        }
        $result = $this->sort_by($result, 'timestamp');
        $code = ($currency !== null) ? $currency['code'] : null;
        $tail = ($since === null);
        return $this->filter_by_currency_since_limit($result, $code, $since, $limit, $tail);
    }

    public function parse_transfers($transfers, $currency = null, $since = null, $limit = null, $params = array ()) {
        $transfers = $this->to_array($transfers);
        $result = array();
        for ($i = 0; $i < count($transfers); $i++) {
            $transfer = array_merge($this->parse_transfer($transfers[$i], $currency), $params);
            $result[] = $transfer;
        }
        $result = $this->sort_by($result, 'timestamp');
        $code = ($currency !== null) ? $currency['code'] : null;
        $tail = ($since === null);
        return $this->filter_by_currency_since_limit($result, $code, $since, $limit, $tail);
    }

    public function parse_ledger($data, $currency = null, $since = null, $limit = null, $params = array ()) {
        $result = array();
        $arrayData = $this->to_array($data);
        for ($i = 0; $i < count($arrayData); $i++) {
            $itemOrItems = $this->parse_ledger_entry($arrayData[$i], $currency);
            if (gettype($itemOrItems) === 'array' && array_keys($itemOrItems) === array_keys(array_keys($itemOrItems))) {
                for ($j = 0; $j < count($itemOrItems); $j++) {
                    $result[] = array_merge($itemOrItems[$j], $params);
                }
            } else {
                $result[] = array_merge($itemOrItems, $params);
            }
        }
        $result = $this->sort_by($result, 'timestamp');
        $code = ($currency !== null) ? $currency['code'] : null;
        $tail = ($since === null);
        return $this->filter_by_currency_since_limit($result, $code, $since, $limit, $tail);
    }

    public function nonce() {
        return $this->seconds ();
    }

    public function set_headers($headers) {
        return $headers;
    }

    public function market_id($symbol) {
        $market = $this->market ($symbol);
        if ($market !== null) {
            return $market['id'];
        }
        return $symbol;
    }

    public function symbol($symbol) {
        $market = $this->market ($symbol);
        return $this->safe_string($market, 'symbol', $symbol);
    }

    public function resolve_path($path, $params) {
        return array(
            $this->implode_params($path, $params),
            $this->omit ($params, $this->extract_params($path)),
        );
    }

    public function filter_by_array($objects, $key, $values = null, $indexed = true) {
        $objects = $this->to_array($objects);
        // return all of them if no $values were passed
        if ($values === null || !$values) {
            return $indexed ? $this->index_by($objects, $key) : $objects;
        }
        $results = array();
        for ($i = 0; $i < count($objects); $i++) {
            if ($this->in_array($objects[$i][$key], $values)) {
                $results[] = $objects[$i];
            }
        }
        return $indexed ? $this->index_by($results, $key) : $results;
    }

    public function fetch2($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null, $config = array (), $context = array ()) {
        if ($this->enableRateLimit) {
            $cost = $this->calculate_rate_limiter_cost($api, $method, $path, $params, $config, $context);
            yield $this->throttle ($cost);
        }
        $this->lastRestRequestTimestamp = $this->milliseconds ();
        $request = $this->sign ($path, $api, $method, $params, $headers, $body);
        return yield $this->fetch ($request['url'], $request['method'], $request['headers'], $request['body']);
    }

    public function request($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null, $config = array (), $context = array ()) {
        return yield $this->fetch2 ($path, $api, $method, $params, $headers, $body, $config, $context);
    }

    public function load_accounts($reload = false, $params = array ()) {
        if ($reload) {
            $this->accounts = yield $this->fetch_accounts($params);
        } else {
            if ($this->accounts) {
                return $this->accounts;
            } else {
                $this->accounts = yield $this->fetch_accounts($params);
            }
        }
        $this->accountsById = $this->index_by($this->accounts, 'id');
        return $this->accounts;
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchTrades() is not supported yet');
    }

    public function fetch_ohlcvc($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        if (!$this->has['fetchTrades']) {
            throw new NotSupported($this->id . ' fetchOHLCV() is not supported yet');
        }
        yield $this->load_markets();
        $trades = yield $this->fetchTrades ($symbol, $since, $limit, $params);
        return $this->build_ohlcvc($trades, $timeframe, $since, $limit);
    }

    public function parse_trading_view_ohlcv($ohlcvs, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        $result = $this->convert_trading_view_to_ohlcv($ohlcvs);
        return $this->parse_ohlcvs($result, $market, $timeframe, $since, $limit);
    }

    public function edit_limit_buy_order($id, $symbol, $amount, $price = null, $params = array ()) {
        return yield $this->edit_limit_order($id, $symbol, 'buy', $amount, $price, $params);
    }

    public function edit_limit_sell_order($id, $symbol, $amount, $price = null, $params = array ()) {
        return yield $this->edit_limit_order($id, $symbol, 'sell', $amount, $price, $params);
    }

    public function edit_limit_order($id, $symbol, $side, $amount, $price = null, $params = array ()) {
        return yield $this->edit_order($id, $symbol, 'limit', $side, $amount, $price, $params);
    }

    public function edit_order($id, $symbol, $type, $side, $amount, $price = null, $params = array ()) {
        yield $this->cancelOrder ($id, $symbol);
        return yield $this->create_order($symbol, $type, $side, $amount, $price, $params);
    }

    public function fetch_permissions($params = array ()) {
        throw new NotSupported($this->id . ' fetchPermissions() is not supported yet');
    }

    public function fetch_bids_asks($symbols = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchBidsAsks() is not supported yet');
    }

    public function parse_bid_ask($bidask, $priceKey = 0, $amountKey = 1) {
        $price = $this->safe_number($bidask, $priceKey);
        $amount = $this->safe_number($bidask, $amountKey);
        return array( $price, $amount );
    }

    public function safe_currency($currencyId, $currency = null) {
        if (($currencyId === null) && ($currency !== null)) {
            return $currency;
        }
        if (($this->currencies_by_id !== null) && (is_array($this->currencies_by_id) && array_key_exists($currencyId, $this->currencies_by_id))) {
            return $this->currencies_by_id[$currencyId];
        }
        $code = $currencyId;
        if ($currencyId !== null) {
            $code = $this->common_currency_code(strtoupper($currencyId));
        }
        return array(
            'id' => $currencyId,
            'code' => $code,
        );
    }

    public function safe_market($marketId = null, $market = null, $delimiter = null) {
        $result = array(
            'id' => $marketId,
            'symbol' => $marketId,
            'base' => null,
            'quote' => null,
            'baseId' => null,
            'quoteId' => null,
            'active' => null,
            'type' => null,
            'linear' => null,
            'inverse' => null,
            'spot' => false,
            'swap' => false,
            'future' => false,
            'option' => false,
            'margin' => false,
            'contract' => false,
            'contractSize' => null,
            'expiry' => null,
            'expiryDatetime' => null,
            'optionType' => null,
            'strike' => null,
            'settle' => null,
            'settleId' => null,
            'precision' => array(
                'amount' => null,
                'price' => null,
            ),
            'limits' => array(
                'amount' => array(
                    'min' => null,
                    'max' => null,
                ),
                'price' => array(
                    'min' => null,
                    'max' => null,
                ),
                'cost' => array(
                    'min' => null,
                    'max' => null,
                ),
            ),
            'info' => null,
        );
        if ($marketId !== null) {
            if (($this->markets_by_id !== null) && (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id))) {
                $market = $this->markets_by_id[$marketId];
            } elseif ($delimiter !== null) {
                $parts = explode($delimiter, $marketId);
                $partsLength = count($parts);
                if ($partsLength === 2) {
                    $result['baseId'] = $this->safe_string($parts, 0);
                    $result['quoteId'] = $this->safe_string($parts, 1);
                    $result['base'] = $this->safe_currency_code($result['baseId']);
                    $result['quote'] = $this->safe_currency_code($result['quoteId']);
                    $result['symbol'] = $result['base'] . '/' . $result['quote'];
                    return $result;
                } else {
                    return $result;
                }
            }
        }
        if ($market !== null) {
            return $market;
        }
        return $result;
    }

    public function check_required_credentials($error = true) {
        $keys = is_array($this->requiredCredentials) ? array_keys($this->requiredCredentials) : array();
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            if ($this->requiredCredentials[$key] && !$this->$key) {
                if ($error) {
                    throw new AuthenticationError($this->id . ' requires "' . $key . '" credential');
                } else {
                    return $error;
                }
            }
        }
        return true;
    }

    public function oath() {
        if ($this->twofa !== null) {
            return $this->totp ($this->twofa);
        } else {
            throw new ExchangeError($this->id . ' exchange.twofa has not been set for 2FA Two-Factor Authentication');
        }
    }

    public function fetch_balance($params = array ()) {
        throw new NotSupported($this->id . ' fetchBalance() is not supported yet');
    }

    public function fetch_partial_balance($part, $params = array ()) {
        $balance = yield $this->fetch_balance($params);
        return $balance[$part];
    }

    public function fetch_free_balance($params = array ()) {
        return yield $this->fetch_partial_balance('free', $params);
    }

    public function fetch_used_balance($params = array ()) {
        return yield $this->fetch_partial_balance('used', $params);
    }

    public function fetch_total_balance($params = array ()) {
        return yield $this->fetch_partial_balance('total', $params);
    }

    public function fetch_status($params = array ()) {
        if ($this->has['fetchTime']) {
            $time = yield $this->fetchTime ($params);
            $this->status = array_merge($this->status, array(
                'updated' => $time,
            ));
        }
        return $this->status;
    }

    public function fetch_funding_fee($code, $params = array ()) {
        $warnOnFetchFundingFee = $this->safe_value($this->options, 'warnOnFetchFundingFee', true);
        if ($warnOnFetchFundingFee) {
            throw new NotSupported($this->id . ' fetchFundingFee() method is deprecated, it will be removed in July 2022, please, use fetchTransactionFee() or set exchange.options["warnOnFetchFundingFee"] = false to suppress this warning');
        }
        return $this->fetch_transaction_fee($code, $params);
    }

    public function fetch_funding_fees($codes = null, $params = array ()) {
        $warnOnFetchFundingFees = $this->safe_value($this->options, 'warnOnFetchFundingFees', true);
        if ($warnOnFetchFundingFees) {
            throw new NotSupported($this->id . ' fetchFundingFees() method is deprecated, it will be removed in July 2022. Please, use fetchTransactionFees() or set exchange.options["warnOnFetchFundingFees"] = false to suppress this warning');
        }
        return $this->fetch_transaction_fees($codes, $params);
    }

    public function fetch_transaction_fee($code, $params = array ()) {
        if (!$this->has['fetchTransactionFees']) {
            throw new NotSupported($this->id . ' fetchTransactionFee() is not supported yet');
        }
        return $this->fetch_transaction_fees(array( $code ), $params);
    }

    public function fetch_transaction_fees($codes = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchTransactionFees() is not supported yet');
    }

    public function get_supported_mapping($key, $mapping = array ()) {
        if (is_array($mapping) && array_key_exists($key, $mapping)) {
            return $mapping[$key];
        } else {
            throw new NotSupported($this->id . ' ' . $key . ' does not have a value in mapping');
        }
    }

    public function fetch_borrow_rate($code, $params = array ()) {
        yield $this->load_markets();
        if (!$this->has['fetchBorrowRates']) {
            throw new NotSupported($this->id . ' fetchBorrowRate() is not supported yet');
        }
        $borrowRates = yield $this->fetch_borrow_rates($params);
        $rate = $this->safe_value($borrowRates, $code);
        if ($rate === null) {
            throw new ExchangeError($this->id . ' fetchBorrowRate() could not find the borrow $rate for currency $code ' . $code);
        }
        return $rate;
    }

    public function handle_market_type_and_params($methodName, $market = null, $params = array ()) {
        $defaultType = $this->safe_string_2($this->options, 'defaultType', 'type', 'spot');
        $methodOptions = $this->safe_value($this->options, $methodName);
        $methodType = $defaultType;
        if ($methodOptions !== null) {
            if (gettype($methodOptions) === 'string') {
                $methodType = $methodOptions;
            } else {
                $methodType = $this->safe_string_2($methodOptions, 'defaultType', 'type', $methodType);
            }
        }
        $marketType = ($market === null) ? $methodType : $market['type'];
        $type = $this->safe_string_2($params, 'defaultType', 'type', $marketType);
        $params = $this->omit ($params, array( 'defaultType', 'type' ));
        return array( $type, $params );
    }

    public function handle_sub_type_and_params($methodName, $market = null, $params = array ()) {
        $subType = null;
        // if set in $params, it takes precedence
        $subTypeInParams = $this->safe_string_2($params, 'subType', 'subType');
        // avoid omitting if it's not present
        if ($subTypeInParams !== null) {
            $subType = $subTypeInParams;
            $params = $this->omit ($params, array( 'defaultSubType', 'subType' ));
        } else {
            // at first, check from $market object
            if ($market !== null) {
                if ($market['linear']) {
                    $subType = 'linear';
                } elseif ($market['inverse']) {
                    $subType = 'inverse';
                }
            }
            // if it was not defined in $market object
            if ($subType === null) {
                $exchangeWideValue = $this->safe_string_2($this->options, 'defaultSubType', 'subType', 'linear');
                $methodOptions = $this->safe_value($this->options, $methodName, array());
                $subType = $this->safe_string_2($methodOptions, 'defaultSubType', 'subType', $exchangeWideValue);
            }
        }
        return array( $subType, $params );
    }

    public function throw_exactly_matched_exception($exact, $string, $message) {
        if (is_array($exact) && array_key_exists($string, $exact)) {
            throw new $exact[$string]($message);
        }
    }

    public function throw_broadly_matched_exception($broad, $string, $message) {
        $broadKey = $this->find_broadly_matched_key($broad, $string);
        if ($broadKey !== null) {
            throw new $broad[$broadKey]($message);
        }
    }

    public function find_broadly_matched_key($broad, $string) {
        // a helper for matching error strings exactly vs broadly
        $keys = is_array($broad) ? array_keys($broad) : array();
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            if (mb_strpos($string, $key) !== false) {
                return $key;
            }
        }
        return null;
    }

    public function handle_errors($statusCode, $statusText, $url, $method, $responseHeaders, $responseBody, $response, $requestHeaders, $requestBody) {
        // it is a stub $method that must be overrided in the derived exchange classes
        // throw new NotSupported($this->id . ' handleErrors() not implemented yet');
    }

    public function calculate_rate_limiter_cost($api, $method, $path, $params, $config = array (), $context = array ()) {
        return $this->safe_value($config, 'cost', 1);
    }

    public function fetch_ticker($symbol, $params = array ()) {
        if ($this->has['fetchTickers']) {
            $tickers = yield $this->fetch_tickers(array( $symbol ), $params);
            $ticker = $this->safe_value($tickers, $symbol);
            if ($ticker === null) {
                throw new NullResponse($this->id . ' fetchTickers() could not find a $ticker for ' . $symbol);
            } else {
                return $ticker;
            }
        } else {
            throw new NotSupported($this->id . ' fetchTicker() is not supported yet');
        }
    }

    public function fetch_tickers($symbols = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchTickers() is not supported yet');
    }

    public function fetch_order($id, $symbol = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchOrder() is not supported yet');
    }

    public function fetch_order_status($id, $symbol = null, $params = array ()) {
        $order = yield $this->fetch_order($id, $symbol, $params);
        return $order['status'];
    }

    public function fetch_unified_order($order, $params = array ()) {
        return yield $this->fetch_order($this->safe_value($order, 'id'), $this->safe_value($order, 'symbol'), $params);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        throw new NotSupported($this->id . ' createOrder() is not supported yet');
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        throw new NotSupported($this->id . ' cancelOrder() is not supported yet');
    }

    public function cancel_unified_order($order, $params = array ()) {
        return $this->cancelOrder ($this->safe_value($order, 'id'), $this->safe_value($order, 'symbol'), $params);
    }

    public function fetch_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchOrders() is not supported yet');
    }

    public function fetch_open_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchOpenOrders() is not supported yet');
    }

    public function fetch_closed_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchClosedOrders() is not supported yet');
    }

    public function fetch_my_trades($symbol = null, $since = null, $limit = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchMyTrades() is not supported yet');
    }

    public function fetch_transactions($symbol = null, $since = null, $limit = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchTransactions() is not supported yet');
    }

    public function fetch_deposits($symbol = null, $since = null, $limit = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchDeposits() is not supported yet');
    }

    public function fetch_withdrawals($symbol = null, $since = null, $limit = null, $params = array ()) {
        throw new NotSupported($this->id . ' fetchWithdrawals() is not supported yet');
    }

    public function fetch_deposit_address($code, $params = array ()) {
        if ($this->has['fetchDepositAddresses']) {
            $depositAddresses = yield $this->fetchDepositAddresses (array( $code ), $params);
            $depositAddress = $this->safe_value($depositAddresses, $code);
            if ($depositAddress === null) {
                throw new InvalidAddress($this->id . ' fetchDepositAddress() could not find a deposit address for ' . $code . ', make sure you have created a corresponding deposit address in your wallet on the exchange website');
            } else {
                return $depositAddress;
            }
        } else {
            throw new NotSupported($this->id . ' fetchDepositAddress() is not supported yet');
        }
    }

    public function account() {
        return array(
            'free' => null,
            'used' => null,
            'total' => null,
        );
    }

    public function common_currency_code($currency) {
        if (!$this->substituteCommonCurrencyCodes) {
            return $currency;
        }
        return $this->safe_string($this->commonCurrencies, $currency, $currency);
    }

    public function currency($code) {
        if ($this->currencies === null) {
            throw new ExchangeError($this->id . ' currencies not loaded');
        }
        if (gettype($code) === 'string') {
            if (is_array($this->currencies) && array_key_exists($code, $this->currencies)) {
                return $this->currencies[$code];
            } elseif (is_array($this->currencies_by_id) && array_key_exists($code, $this->currencies_by_id)) {
                return $this->currencies_by_id[$code];
            }
        }
        throw new ExchangeError($this->id . ' does not have currency $code ' . $code);
    }

    public function market($symbol) {
        if ($this->markets === null) {
            throw new ExchangeError($this->id . ' markets not loaded');
        }
        if ($this->markets_by_id === null) {
            throw new ExchangeError($this->id . ' markets not loaded');
        }
        if (gettype($symbol) === 'string') {
            if (is_array($this->markets) && array_key_exists($symbol, $this->markets)) {
                return $this->markets[$symbol];
            } elseif (is_array($this->markets_by_id) && array_key_exists($symbol, $this->markets_by_id)) {
                return $this->markets_by_id[$symbol];
            }
        }
        throw new BadSymbol($this->id . ' does not have market $symbol ' . $symbol);
    }

    public function handle_withdraw_tag_and_params($tag, $params) {
        if (gettype($tag) === 'array') {
            $params = array_merge($tag, $params);
            $tag = null;
        }
        if ($tag === null) {
            $tag = $this->safe_string($params, 'tag');
            if ($tag !== null) {
                $params = $this->omit ($params, 'tag');
            }
        }
        return array( $tag, $params );
    }

    public function create_limit_order($symbol, $side, $amount, $price, $params = array ()) {
        return yield $this->create_order($symbol, 'limit', $side, $amount, $price, $params);
    }

    public function create_market_order($symbol, $side, $amount, $price = null, $params = array ()) {
        return yield $this->create_order($symbol, 'market', $side, $amount, $price, $params);
    }

    public function create_limit_buy_order($symbol, $amount, $price, $params = array ()) {
        return yield $this->create_order($symbol, 'limit', 'buy', $amount, $price, $params);
    }

    public function create_limit_sell_order($symbol, $amount, $price, $params = array ()) {
        return yield $this->create_order($symbol, 'limit', 'sell', $amount, $price, $params);
    }

    public function create_market_buy_order($symbol, $amount, $params = array ()) {
        return yield $this->create_order($symbol, 'market', 'buy', $amount, null, $params);
    }

    public function create_market_sell_order($symbol, $amount, $params = array ()) {
        return yield $this->create_order($symbol, 'market', 'sell', $amount, null, $params);
    }

    public function cost_to_precision($symbol, $cost) {
        $market = $this->market ($symbol);
        return $this->decimal_to_precision($cost, TRUNCATE, $market['precision']['price'], $this->precisionMode, $this->paddingMode);
    }

    public function price_to_precision($symbol, $price) {
        $market = $this->market ($symbol);
        return $this->decimal_to_precision($price, ROUND, $market['precision']['price'], $this->precisionMode, $this->paddingMode);
    }

    public function amount_to_precision($symbol, $amount) {
        $market = $this->market ($symbol);
        return $this->decimal_to_precision($amount, TRUNCATE, $market['precision']['amount'], $this->precisionMode, $this->paddingMode);
    }

    public function fee_to_precision($symbol, $fee) {
        $market = $this->market ($symbol);
        return $this->decimal_to_precision($fee, ROUND, $market['precision']['price'], $this->precisionMode, $this->paddingMode);
    }

    public function currency_to_precision($code, $fee, $networkCode = null) {
        $currency = $this->currencies[$code];
        $precision = $this->safe_value($currency, 'precision');
        if ($networkCode !== null) {
            $networks = $this->safe_value($currency, 'networks', array());
            $networkItem = $this->safe_value($networks, $networkCode, array());
            $precision = $this->safe_value($networkItem, 'precision', $precision);
        }
        if ($precision === null) {
            return $fee;
        } else {
            return $this->decimal_to_precision($fee, ROUND, $precision, $this->precisionMode, $this->paddingMode);
        }
    }

    public function safe_number($object, $key, $d = null) {
        $value = $this->safe_string($object, $key);
        return $this->parse_number($value, $d);
    }

    public function safe_number_n($object, $arr, $d = null) {
        $value = $this->safe_string_n($object, $arr);
        return $this->parse_number($value, $d);
    }

    public function parse_precision($precision) {
        if ($precision === null) {
            return null;
        }
        return '1e' . Precise::string_neg($precision);
    }

    public function load_time_difference($params = array ()) {
        $serverTime = yield $this->fetchTime ($params);
        $after = $this->milliseconds ();
        $this->options['timeDifference'] = $after - $serverTime;
        return $this->options['timeDifference'];
    }

    public function implode_hostname($url) {
        return $this->implode_params($url, array( 'hostname' => $this->hostname ));
    }

    public function fetch_market_leverage_tiers($symbol, $params = array ()) {
        if ($this->has['fetchLeverageTiers']) {
            $market = yield $this->market ($symbol);
            if (!$market['contract']) {
                throw new BadSymbol($this->id . ' fetchMarketLeverageTiers() supports contract markets only');
            }
            $tiers = yield $this->fetch_leverage_tiers(array( $symbol ));
            return $this->safe_value($tiers, $symbol);
        } else {
            throw new NotSupported($this->id . ' fetchMarketLeverageTiers() is not supported yet');
        }
    }

    public function create_post_only_order($symbol, $type, $side, $amount, $price, $params = array ()) {
        if (!$this->has['createPostOnlyOrder']) {
            throw new NotSupported($this->id . 'createPostOnlyOrder() is not supported yet');
        }
        $query = array_merge($params, array( 'postOnly' => true ));
        return yield $this->create_order($symbol, $type, $side, $amount, $price, $query);
    }

    public function create_reduce_only_order($symbol, $type, $side, $amount, $price, $params = array ()) {
        if (!$this->has['createReduceOnlyOrder']) {
            throw new NotSupported($this->id . 'createReduceOnlyOrder() is not supported yet');
        }
        $query = array_merge($params, array( 'reduceOnly' => true ));
        return yield $this->create_order($symbol, $type, $side, $amount, $price, $query);
    }

    public function create_stop_order($symbol, $type, $side, $amount, $price = null, $stopPrice = null, $params = array ()) {
        if (!$this->has['createStopOrder']) {
            throw new NotSupported($this->id . ' createStopOrder() is not supported yet');
        }
        if ($stopPrice === null) {
            throw new ArgumentsRequired($this->id . ' create_stop_order() requires a $stopPrice argument');
        }
        $query = array_merge($params, array( 'stopPrice' => $stopPrice ));
        return yield $this->create_order($symbol, $type, $side, $amount, $price, $query);
    }

    public function create_stop_limit_order($symbol, $side, $amount, $price, $stopPrice, $params = array ()) {
        if (!$this->has['createStopLimitOrder']) {
            throw new NotSupported($this->id . ' createStopLimitOrder() is not supported yet');
        }
        $query = array_merge($params, array( 'stopPrice' => $stopPrice ));
        return yield $this->create_order($symbol, 'limit', $side, $amount, $price, $query);
    }

    public function create_stop_market_order($symbol, $side, $amount, $stopPrice, $params = array ()) {
        if (!$this->has['createStopMarketOrder']) {
            throw new NotSupported($this->id . ' createStopMarketOrder() is not supported yet');
        }
        $query = array_merge($params, array( 'stopPrice' => $stopPrice ));
        return yield $this->create_order($symbol, 'market', $side, $amount, null, $query);
    }

    public function safe_currency_code($currencyId, $currency = null) {
        $currency = $this->safe_currency($currencyId, $currency);
        return $currency['code'];
    }

    public function filter_by_symbol_since_limit($array, $symbol = null, $since = null, $limit = null, $tail = false) {
        return $this->filter_by_value_since_limit($array, 'symbol', $symbol, $since, $limit, 'timestamp', $tail);
    }

    public function filter_by_currency_since_limit($array, $code = null, $since = null, $limit = null, $tail = false) {
        return $this->filter_by_value_since_limit($array, 'currency', $code, $since, $limit, 'timestamp', $tail);
    }

    public function parse_tickers($tickers, $symbols = null, $params = array ()) {
        //
        // the value of $tickers is either a dict or a list
        //
        // dict
        //
        //     {
        //         'marketId1' => array( ... ),
        //         'marketId2' => array( ... ),
        //         'marketId3' => array( ... ),
        //         ...
        //     }
        //
        // list
        //
        //     array(
        //         array( 'market' => 'marketId1', ... ),
        //         array( 'market' => 'marketId2', ... ),
        //         array( 'market' => 'marketId3', ... ),
        //         ...
        //     )
        //
        $results = array();
        if (gettype($tickers) === 'array' && array_keys($tickers) === array_keys(array_keys($tickers))) {
            for ($i = 0; $i < count($tickers); $i++) {
                $ticker = array_merge($this->parse_ticker($tickers[$i]), $params);
                $results[] = $ticker;
            }
        } else {
            $marketIds = is_array($tickers) ? array_keys($tickers) : array();
            for ($i = 0; $i < count($marketIds); $i++) {
                $marketId = $marketIds[$i];
                $market = $this->safe_market($marketId);
                $ticker = array_merge($this->parse_ticker($tickers[$marketId], $market), $params);
                $results[] = $ticker;
            }
        }
        $symbols = $this->market_symbols($symbols);
        return $this->filter_by_array($results, 'symbol', $symbols);
    }

    public function parse_deposit_addresses($addresses, $codes = null, $indexed = true, $params = array ()) {
        $result = array();
        for ($i = 0; $i < count($addresses); $i++) {
            $address = array_merge($this->parse_deposit_address($addresses[$i]), $params);
            $result[] = $address;
        }
        if ($codes !== null) {
            $result = $this->filter_by_array($result, 'currency', $codes, false);
        }
        $result = $indexed ? $this->index_by($result, 'currency') : $result;
        return $result;
    }

    public function parse_borrow_interests($response, $market = null) {
        $interests = array();
        for ($i = 0; $i < count($response); $i++) {
            $row = $response[$i];
            $interests[] = $this->parse_borrow_interest($row, $market);
        }
        return $interests;
    }

    public function parse_funding_rate_histories($response, $market = null, $since = null, $limit = null) {
        $rates = array();
        for ($i = 0; $i < count($response); $i++) {
            $entry = $response[$i];
            $rates[] = $this->parse_funding_rate_history($entry, $market);
        }
        $sorted = $this->sort_by($rates, 'timestamp');
        $symbol = ($market === null) ? null : $market['symbol'];
        return $this->filter_by_symbol_since_limit($sorted, $symbol, $since, $limit);
    }

    public function safe_symbol($marketId, $market = null, $delimiter = null) {
        $market = $this->safe_market($marketId, $market, $delimiter);
        return $market['symbol'];
    }

    public function parse_funding_rate($contract, $market = null) {
        throw new NotSupported($this->id . ' parseFundingRate() is not supported yet');
    }

    public function parse_funding_rates($response, $market = null) {
        $result = array();
        for ($i = 0; $i < count($response); $i++) {
            $parsed = $this->parse_funding_rate($response[$i], $market);
            $result[$parsed['symbol']] = $parsed;
        }
        return $result;
    }

    public function is_post_only($isMarketOrder, $exchangeSpecificParam, $params = array ()) {
        /**
         * @ignore
         * @param {string} type Order type
         * @param {boolean} $exchangeSpecificParam exchange specific $postOnly
         * @param {array} $params exchange specific $params
         * @return {boolean} true if a post only order, false otherwise
         */
        $timeInForce = $this->safe_string_upper($params, 'timeInForce');
        $postOnly = $this->safe_value_2($params, 'postOnly', 'post_only', false);
        // we assume $timeInForce is uppercase from safeStringUpper ($params, 'timeInForce')
        $ioc = $timeInForce === 'IOC';
        $fok = $timeInForce === 'FOK';
        $timeInForcePostOnly = $timeInForce === 'PO';
        $postOnly = $postOnly || $timeInForcePostOnly || $exchangeSpecificParam;
        if ($postOnly) {
            if ($ioc || $fok) {
                throw new InvalidOrder($this->id . ' $postOnly orders cannot have $timeInForce equal to ' . $timeInForce);
            } elseif ($isMarketOrder) {
                throw new InvalidOrder($this->id . ' market orders cannot be postOnly');
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function fetch_trading_fees($params = array ()) {
        throw new NotSupported($this->id . ' fetchTradingFees() is not supported yet');
    }

    public function fetch_trading_fee($symbol, $params = array ()) {
        if (!$this->has['fetchTradingFees']) {
            throw new NotSupported($this->id . ' fetchTradingFee() is not supported yet');
        }
        return yield $this->fetch_trading_fees($params);
    }

    public function parse_open_interest($interest, $market = null) {
        throw new NotSupported($this->id . ' parseOpenInterest () is not supported yet');
    }

    public function parse_open_interests($response, $market = null, $since = null, $limit = null) {
        $interests = array();
        for ($i = 0; $i < count($response); $i++) {
            $entry = $response[$i];
            $interest = $this->parse_open_interest($entry, $market);
            $interests[] = $interest;
        }
        $sorted = $this->sort_by($interests, 'timestamp');
        $symbol = $this->safe_string($market, 'symbol');
        return $this->filter_by_symbol_since_limit($sorted, $symbol, $since, $limit);
    }

    public function fetch_funding_rate($symbol, $params = array ()) {
        if ($this->has['fetchFundingRates']) {
            yield $this->load_markets();
            $market = $this->market ($symbol);
            if (!$market['contract']) {
                throw new BadSymbol($this->id . ' fetchFundingRate() supports contract markets only');
            }
            $rates = yield $this->fetchFundingRates (array( $symbol ), $params);
            $rate = $this->safe_value($rates, $symbol);
            if ($rate === null) {
                throw new NullResponse($this->id . ' fetchFundingRate () returned no data for ' . $symbol);
            } else {
                return $rate;
            }
        } else {
            throw new NotSupported($this->id . ' fetchFundingRate () is not supported yet');
        }
    }

    public function fetch_mark_ohlcv($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        /**
         * fetches historical mark price candlestick data containing the open, high, low, and close price of a market
         * @param {string} $symbol unified $symbol of the market to fetch OHLCV data for
         * @param {string} $timeframe the length of time each candle represents
         * @param {int|null} $since timestamp in ms of the earliest candle to fetch
         * @param {int|null} $limit the maximum amount of candles to fetch
         * @param {array} $params extra parameters specific to the exchange api endpoint
         * @return {[[int|float]]} A list of candles ordered as timestamp, open, high, low, close, null
         */
        if ($this->has['fetchMarkOHLCV']) {
            $request = array(
                'price' => 'mark',
            );
            return yield $this->fetch_ohlcv($symbol, $timeframe, $since, $limit, array_merge($request, $params));
        } else {
            throw new NotSupported($this->id . ' fetchMarkOHLCV () is not supported yet');
        }
    }

    public function fetch_index_ohlcv($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        /**
         * fetches historical index price candlestick data containing the open, high, low, and close price of a market
         * @param {string} $symbol unified $symbol of the market to fetch OHLCV data for
         * @param {string} $timeframe the length of time each candle represents
         * @param {int|null} $since timestamp in ms of the earliest candle to fetch
         * @param {int|null} $limit the maximum amount of candles to fetch
         * @param {array} $params extra parameters specific to the exchange api endpoint
         * @return {[[int|float]]} A list of candles ordered as timestamp, open, high, low, close, null
         */
        if ($this->has['fetchIndexOHLCV']) {
            $request = array(
                'price' => 'index',
            );
            return yield $this->fetch_ohlcv($symbol, $timeframe, $since, $limit, array_merge($request, $params));
        } else {
            throw new NotSupported($this->id . ' fetchIndexOHLCV () is not supported yet');
        }
    }

    public function fetch_premium_index_ohlcv($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        /**
         * fetches historical premium index price candlestick data containing the open, high, low, and close price of a market
         * @param {string} $symbol unified $symbol of the market to fetch OHLCV data for
         * @param {string} $timeframe the length of time each candle represents
         * @param {int|null} $since timestamp in ms of the earliest candle to fetch
         * @param {int|null} $limit the maximum amount of candles to fetch
         * @param {array} $params extra parameters specific to the exchange api endpoint
         * @return {[[int|float]]} A list of candles ordered as timestamp, open, high, low, close, null
         */
        if ($this->has['fetchPremiumIndexOHLCV']) {
            $request = array(
                'price' => 'premiumIndex',
            );
            return yield $this->fetch_ohlcv($symbol, $timeframe, $since, $limit, array_merge($request, $params));
        } else {
            throw new NotSupported($this->id . ' fetchPremiumIndexOHLCV () is not supported yet');
        }
    }

    public function handle_time_in_force($params = array ()) {
        /**
         * @ignore
         * * Must add $timeInForce to $this->options to use this method
         * @return {string} returns the exchange specific value for $timeInForce
         */
        $timeInForce = $this->safe_string_upper($params, 'timeInForce'); // supported values GTC, IOC, PO
        if ($timeInForce !== null) {
            $exchangeValue = $this->safe_string($this->options['timeInForce'], $timeInForce);
            if ($exchangeValue === null) {
                throw new ExchangeError($this->id . ' does not support $timeInForce "' . $timeInForce . '"');
            }
            return $exchangeValue;
        }
        return null;
    }

    public function parse_account($account) {
        /**
         * @ignore
         * * Must add $accountsByType to $this->options to use this method
         * @param {string} $account key for $account name in $this->options['accountsByType']
         * @return the exchange specific $account name or the isolated margin id for transfers
         */
        $accountsByType = $this->safe_value($this->options, 'accountsByType', array());
        $symbols = $this->symbols;
        if (is_array($accountsByType) && array_key_exists($account, $accountsByType)) {
            return $accountsByType[$account];
        } elseif ($this->in_array($account, $symbols)) {
            $market = $this->market ($account);
            return $market['id'];
        } else {
            return $account;
        }
    }

    public function handle_margin_mode_and_params($methodName, $params = array ()) {
        /**
         * @ignore
         * @param {array} $params extra parameters specific to the exchange api endpoint
         * @return array([string|null, object]) the $marginMode in lowercase as specified by $params["marginMode"], $params["defaultMarginMode"] $this->options["marginMode"] or $this->options["defaultMarginMode"]
         */
        $defaultMarginMode = $this->safe_string_2($this->options, 'marginMode', 'defaultMarginMode');
        $methodOptions = $this->safe_value($this->options, $methodName, array());
        $methodMarginMode = $this->safe_string_2($methodOptions, 'marginMode', 'defaultMarginMode', $defaultMarginMode);
        $marginMode = $this->safe_string_lower_2($params, 'marginMode', 'defaultMarginMode', $methodMarginMode);
        if ($marginMode !== null) {
            $params = $this->omit ($params, array( 'marginMode', 'defaultMarginMode' ));
        }
        return array( $marginMode, $params );
    }
}
