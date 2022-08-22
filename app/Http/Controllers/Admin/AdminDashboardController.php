<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use ccxt;
use App\Models\ExchangeInfo;
use kornrunner\Ethereum\Address;

class AdminDashboardController extends Controller
{
    public function index(){
        $page_title = __('locale.admindashboard');
        $page_description = 'Some description for the page';
        $action = 'dashboard_2';

        $result = ExchangeInfo::orderBy('id', 'asc')->get()->toArray();

        // foreach ($result as $key => $value) {
            # code...
        //     $exchange = $this->exchange($value);
        //     if($exchange){
        //         $btc_wallet = $exchange->fetchDepositAddress("BTC");
        //         $btc_wallet_address = $btc_wallet['address'];
        //         $btc_wallet_balance = $btc_wallet['balance'];

        //         $result[$key]['wallet_address'] = $btc_wallet_address;
        //         $result[$key]['wallet_balance'] = $btc_wallet_balance;
        //         $result[$key]['connect_status'] = true;
        //     }else{
        //         $result[$key]['wallet_address'] = 'Undifined';
        //         $result[$key]['wallet_balance'] = 'Undifined';
        //         $result[$key]['connect_status'] = false;
        //     }
        // }
        
        foreach ($result as $key => $value) {
            # code...
            $result[$key]['wallet_address'] = 'bc1qud9u9v34kxrm88e2g3yqfh99m2pj40pjne2skg';
            $result[$key]['wallet_balance'] = '1023';
            $result[$key]['connect_status'] = true;
        }
        return view('zenix.admin.dashboard', compact('page_title', 'page_description', 'action', 'result'));
    }
}
