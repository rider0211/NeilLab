<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\ColdWallet;
use App\Models\InternalWallet;

class AdminWalletController extends Controller
{
    public function index(){
        $page_title = __('locale.adminwalletlist');
        $page_description = 'Some description for the page';
        $action = 'walletlist';

        $internal_wallet  = InternalWallet::orderBy('id', 'asc')->get()->toArray();
        foreach ($internal_wallet as $key => $value) {
            # code...
            $internal_wallet[$key]['internal_treasury_account'] = env('INTERNAL_TREASURY_ACCOUNT');
            $cold_storage_address = ColdWallet::select("cold_address")->where("id", $internal_wallet[$key]['cold_storage_status'])->where("wallet_type", $internal_wallet[$key]['wallet_type'])->get()->toArray();
            $internal_wallet[$key]['cold_storage_address'] = $cold_storage_address[0]['cold_address'];
            $cold_storage_balance = $this->getBalance($cold_storage_address[0]['cold_address']);
            $internal_wallet[$key]['cold_storage_balance'] = $cold_storage_balance;
        }
        return view('zenix.admin.walletlist', compact('page_title', 'page_description', 'action', 'internal_wallet'));
    }

    function getBalance($address) {
        return file_get_contents('https://blockchain.info/q/addressbalance/'. $address);
    }
}
