<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function exchange($param){
        $n_id = $param['ex_name'];
        $exchange_id = '\\ccxt\\' . $n_id;
        $exchange = new $exchange_id(array(
            'enableRateLimit' => true,
            'apiKey' => $param['api_key'],
            'secret' => $param['api_secret'],
            'options' => array(
                'defaultType' => 'future',
            ),
        ));
        // $exchange = new $exchange_id(array(
        //     'enableRateLimit' => true,
        //     'apiKey' => 'cfa26610450d9e422319ed255b92b192b64292ea8d9bfafcb7918963d8718019',
        //     'secret' => '4fad0a5112f5b5eab2b75d74a5de10e01634c0d17b42da44097386be99a534e9',
        //     'options' => array(
        //         'defaultType' => 'future',
        //     ),
        // ));
        // $exchange->set_sandbox_mode(True);
        return $exchange;
    }
}
