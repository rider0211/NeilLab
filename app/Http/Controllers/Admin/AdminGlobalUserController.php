<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;

class AdminGlobalUserController extends Controller
{
    //
    
    public function index(){
        $page_title = __('locale.global_user_list');
        $page_description = 'Some description for the page';
        $action = 'global_user_list';

        $result = User::orderBy('id', 'asc')->get()->toArray();
        
        foreach ($result as $key => $value) {
            # code...
            $result[$key]['f_name'] ="Joe";
            $result[$key]['l_name'] ="Doe";
            $result[$key]['user_type'] ="human";
            $result[$key]['buy_weight'] =3;
            $result[$key]['amount_allow_to_buy'] =3;
            $result[$key]['sell_weight'] =1;
            $result[$key]['amount_allow_to_sell'] =3;
            $result[$key]['cold_storage_address'] = env("INTERNAL_TREASURY_ACCOUNT");
            $result[$key]['wallet_address'] = env("INTERNAL_TREASURY_ACCOUNT");
            $result[$key]['set_for_all_trading_pairs'] = "USDT/EURO";
            $result[$key]['selected_exchange_tehy_can_trade_on'] = "Binance";
            $result[$key]['status'] = 1;
        }
        return view('zenix.admin.global_user_list', compact('page_title', 'page_description', 'action','result'));
    }
}
